<?php

namespace App\Notifications;

use App\Models\Orders;
use App\Services\PricingService;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent when an order is created (portal or public website).
 *
 *  - Client            → in-app + broadcast + EMAIL (order confirmation, customer prices only)
 *  - Admin users       → in-app + broadcast + EMAIL ("New order" alert, customer price + supplier cost)
 *  - Supplier users    → in-app + broadcast only (unchanged)
 *  - Alert address     → EMAIL only (on-demand, config('mail.order_alert_to'), same as admin email)
 *
 * Pricing per item (both emails):
 *  - supplier assigned → customer price via PricingService (single source of truth)
 *  - no supplier       → no price, shown as "Price to be confirmed"
 */
class OrderCreatedNotification extends Notification
{
    public function __construct(
        public Orders $order,
        public string $clientName
    ) {}

    public function via(object $notifiable): array
    {
        // Owner alert address (Notification::route('mail', ...)) → email only.
        if ($notifiable instanceof AnonymousNotifiable) {
            return ['mail'];
        }

        if ($this->isClient($notifiable) || $this->isAdmin($notifiable)) {
            return ['database', 'broadcast', 'mail'];
        }

        // Suppliers: in-app only (they never see customer pricing by email).
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        $isClient = $this->isClient($notifiable);

        return [
            'event'     => 'order.created',
            'title'     => $isClient ? 'Order Placed' : 'New Order Created',
            'message'   => $isClient
                ? "Your order #{$this->order->id} has been placed successfully"
                : "Order #{$this->order->id} was created by {$this->clientName}",
            'order_id'  => $this->order->id,
            'po_number' => $this->order->po_number,
            'client_id' => $this->order->client_id,
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return (new BroadcastMessage($this->toArray($notifiable)))->onConnection('sync');
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->order->loadMissing(['items.product', 'items.supplier', 'items.deliveries']);

        $orderRef = $this->order->po_number ?: "#{$this->order->id}";
        $base     = rtrim(config('app.frontend_url', config('app.url')), '/');
        $summary  = $this->summary();

        if ($this->isClient($notifiable)) {
            return (new MailMessage)
                ->subject("Order received — {$orderRef}")
                ->view('emails.orders.confirmation', [
                    'order'      => $this->order,
                    'orderRef'   => $orderRef,
                    'clientName' => $notifiable->contact_name ?? $notifiable->name ?? 'there',
                    'portalUrl'  => "{$base}/client/orders/{$this->order->id}",
                    'summary'    => $summary,
                ]);
        }

        // Admin user or owner alert address.
        $client = \App\Models\User::find($this->order->client_id);
        $flag   = $summary['unpriced_count'] > 0 ? ' — ACTION: no supplier for ' . $summary['unpriced_count'] . ' item(s)' : '';

        $details = [
            'Order'    => $orderRef,
            'Client'   => $this->clientName,
            'Email'    => $client->email ?? '-',
            'Phone'    => $client->contact_number ?? '-',
        ];
        foreach ($summary['lines'] as $i => $l) {
            $label = 'Item ' . ($i + 1);
            $text  = "{$l['name']} - {$l['qty']} {$l['unit']}";
            if ($l['priced']) {
                $text .= ' | customer $' . number_format((float) $l['line_total'], 2)
                       . ' | supplier ' . ($l['supplier_name'] ?? '-') . ' $' . number_format((float) $l['supplier_total'], 2);
            } else {
                $text .= ' | NO SUPPLIER ASSIGNED';
            }
            $details[$label] = $text;
        }
        $details['Items total (ex GST)']   = '$' . number_format($summary['items_total'], 2);
        $details['Delivery (ex GST)']      = '$' . number_format($summary['delivery_total'], 2);
        $details['Supplier cost total']    = '$' . number_format($summary['supplier_total'], 2);

        $subject = "New order {$orderRef} from {$this->clientName}{$flag}";

        return (new MailMessage)
            ->subject($subject)
            ->view('emails.notification', [
                'subjectLine'    => $subject,
                'preheader'      => "New order {$orderRef} from {$this->clientName}",
                'badge'          => 'New order',
                'title'          => "New order {$orderRef}",
                'recipientName'  => $notifiable instanceof AnonymousNotifiable ? 'Admin' : ($notifiable->contact_name ?? $notifiable->name ?? 'Admin'),
                'bodyText'       => "{$this->clientName} placed a new order." . ($summary['unpriced_count'] > 0 ? " {$summary['unpriced_count']} item(s) still need a supplier." : ''),
                'details'        => $details,
                'actionText'     => 'Open order',
                'actionUrl'      => "{$base}/admin/orders/{$this->order->id}",
                'logoUrl'        => config('app.email_logo_url'),
                'brandColor'     => config('app.email_brand_color'),
                'accentColor'    => config('app.email_accent_color'),
                'supportAddress' => config('app.email_support_address'),
                'supportPhone'   => config('app.email_support_phone'),
            ]);
    }

    /* --------------------------------------------------------------------- */

    private function isClient(object $notifiable): bool
    {
        return isset($notifiable->id) && (int) $notifiable->id === (int) $this->order->client_id;
    }

    private function isAdmin(object $notifiable): bool
    {
        return ($notifiable->role ?? null) === 'admin';
    }

    /**
     * One pricing summary shared by both templates, so the client email and the
     * admin email can never disagree. Items without a supplier carry no price.
     */
    private function summary(): array
    {
        $lines         = [];
        $itemsTotal    = 0.0;
        $supplierTotal = 0.0;
        $deliveryTotal = 0.0;
        $unpriced      = 0;

        foreach ($this->order->items as $item) {
            $hasSupplier = ! empty($item->supplier_id);
            $b           = $hasSupplier ? PricingService::itemBreakdown($item) : null;
            $unit        = $item->product->unit_of_measure ?? '';

            if ($hasSupplier) {
                $itemsTotal    += $b['customer_item_total'];
                $supplierTotal += $b['supplier_net'];
                $deliveryTotal += $b['customer_delivery_cost'];
            } else {
                $unpriced++;
            }

            $lines[] = [
                'name'           => $item->product->product_name ?? 'Item',
                'qty'            => rtrim(rtrim(number_format((float) $item->quantity, 2), '0'), '.'),
                'unit'           => preg_replace('/cubic meters?\s*\(m3\)/i', 'm³', $unit),
                'blend'          => $item->custom_blend_mix,
                'priced'         => $hasSupplier,
                'unit_price'     => $b['customer_unit_price'] ?? null,
                'line_total'     => $b['customer_item_total'] ?? null,
                'supplier_name'  => $hasSupplier ? ($item->supplier->name ?? 'Supplier #' . $item->supplier_id) : null,
                'supplier_unit'  => $b['supplier_unit_cost'] ?? null,
                'supplier_total' => $b['supplier_net'] ?? null,
                'deliveries'     => $item->deliveries->map(fn ($d) => [
                    'date'  => $d->delivery_date ? \Carbon\Carbon::parse($d->delivery_date)->format('D, d M Y') : 'TBC',
                    'time'  => $d->delivery_time ? \Carbon\Carbon::parse($d->delivery_time)->format('g:i A') : '',
                    'qty'   => rtrim(rtrim(number_format((float) $d->quantity, 2), '0'), '.'),
                    'truck' => $d->truck_type ? ucwords(str_replace('_', ' ', $d->truck_type)) : null,
                ])->values()->all(),
            ];
        }

        return [
            'lines'          => $lines,
            'items_total'    => round($itemsTotal, 2),
            'delivery_total' => round($deliveryTotal, 2),
            'supplier_total' => round($supplierTotal, 2),
            'unpriced_count' => $unpriced,
            'all_unpriced'   => $unpriced === count($lines),
        ];
    }
}

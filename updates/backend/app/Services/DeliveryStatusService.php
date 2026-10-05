<?php

namespace App\Services;

use App\Models\OrderItemDelivery;
use App\Models\ActionLog;
use Illuminate\Support\Facades\Auth;

/**
 * Delivery status is FULLY INDEPENDENT and has NO transition rules.
 *
 * Any valid status can move to any other valid status. There is no guarded
 * path and no "illegal transition" — delivery status is driven purely by the
 * admin (Items-tab dropdown) and the client (confirm receipt). Whether a
 * delivery is billed is derived from its `invoice_id`, never from its status.
 *
 * The old 'invoiced' and 'paid' statuses have been removed from the lifecycle.
 */
class DeliveryStatusService
{
    /** All valid delivery statuses (order is display order only — not a lifecycle). */
    public const STATUSES = [
        'scheduled',
        'ordered_with_supplier',
        'out_for_delivery',
        'delivered',
        'client_confirmed',
        'delivery_issue',
        'cancelled',
    ];

    /** Human-readable labels for UI. */
    public const LABELS = [
        'scheduled'             => 'Scheduled',
        'ordered_with_supplier' => 'Ordered with Supplier',
        'out_for_delivery'      => 'Out for Delivery',
        'delivered'             => 'Delivered',
        'client_confirmed'      => 'Confirmed by Customer',
        'delivery_issue'        => 'Delivery Issue',
        'cancelled'             => 'Cancelled',
    ];

    /** Statuses an admin may set from the dropdown (all of them). */
    public const ADMIN_SETTABLE = self::STATUSES;

    public static function all(): array
    {
        return self::STATUSES;
    }

    public static function isValid(string $status): bool
    {
        return in_array($status, self::STATUSES, true);
    }

    public static function label(string $status): string
    {
        return self::LABELS[$status] ?? ucfirst(str_replace('_', ' ', $status));
    }

    /**
     * Set a delivery to ANY valid status. No transition guard — never throws an
     * "illegal transition". Only throws if the status itself is unknown.
     *
     * This is the single entry point for every status change (admin dropdown,
     * client confirm, anything else).
     */
    public static function set(OrderItemDelivery $delivery, string $to, ?string $reason = null, ?int $userId = null): void
    {
        if (!self::isValid($to)) {
            throw new \InvalidArgumentException("Unknown delivery status: {$to}");
        }

        $from = (string) $delivery->status;
        if ($from === $to) {
            return;
        }

        $delivery->status = $to;
        $delivery->save();

        ActionLog::create([
            'action'   => 'Delivery Status Changed',
            'details'  => "Delivery #{$delivery->id} (order #{$delivery->order_id}): {$from} → {$to}" . ($reason ? " ({$reason})" : ''),
            'order_id' => $delivery->order_id,
            'user_id'  => $userId ?? Auth::id(),
        ]);

        // Notify client, supplier (when relevant) and admins. Never throws.
        try {
            $order    = \App\Models\Orders::find($delivery->order_id);
            $ref      = $order ? ($order->po_number ?: "#{$order->id}") : "#{$delivery->order_id}";
            $label    = self::label($to);
            $date     = $delivery->delivery_date ? \Carbon\Carbon::parse($delivery->delivery_date)->format('D, d M Y') : 'TBC';
            $details  = ['Order' => $ref, 'Delivery date' => $date, 'Status' => $label];
            if ($reason) {
                $details['Note'] = $reason;
            }
            $data = ['order_id' => $delivery->order_id, 'delivery_id' => $delivery->id, 'delivery_status' => $to];

            if ($order) {
                EventNotifier::toUserId(
                    (int) $order->client_id, 'delivery.status', "Delivery update - {$label}",
                    "Your delivery for order {$ref} ({$date}) is now: {$label}.",
                    $details, "client/orders/{$order->id}", 'View order', $data, 'Delivery update'
                );
            }

            $supplierId = $delivery->orderItem->supplier_id ?? null;
            if ($supplierId && in_array($to, ['ordered_with_supplier', 'cancelled', 'delivery_issue'], true)) {
                EventNotifier::toUserId(
                    (int) $supplierId, 'delivery.status', "Delivery update - {$label}",
                    "A delivery for order {$ref} ({$date}) is now: {$label}.",
                    $details, null, 'View order', $data, 'Delivery update'
                );
            }

            EventNotifier::toAdmins(
                'delivery.status', "Delivery {$label} - order {$ref}",
                "Delivery #{$delivery->id} for order {$ref} ({$date}) changed from " . self::label($from ?: 'scheduled') . " to {$label}.",
                $details, "admin/orders/{$delivery->order_id}", 'Open order', $data
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Delivery status notification failed', ['delivery_id' => $delivery->id, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Back-compat alias. Older callers used apply() (the old guarded path).
     * It is now identical to set() — no transitions, never throws on a valid
     * status. Kept so existing call sites (e.g. OrderController::confirmDelivery)
     * keep working without edits.
     */
    public static function apply(OrderItemDelivery $delivery, string $to, ?string $reason = null, ?int $userId = null): void
    {
        self::set($delivery, $to, $reason, $userId);
    }
}
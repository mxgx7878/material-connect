<?php

namespace App\Notifications;

use App\Models\Orders;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderCreatedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private Orders $order,
        private string $clientName
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $isSupplier = $notifiable->role === 'supplier';
        $isClient = $notifiable->role === 'client';
        $displayName = $notifiable->contact_name ?: $notifiable->name;
        $orderReference = $this->order->po_number ?: '#' . $this->order->id;

        $items = $isSupplier
            ? $this->order->items->where('supplier_id', $notifiable->id)
            : $this->order->items;

        $itemSummary = $items->map(function ($item) {
            $productName = optional($item->product)->product_name ?: 'Product #' . $item->product_id;
            return "{$productName} - Qty: {$item->quantity}";
        })->implode(', ');

        if ($isSupplier) {
            $subject = "New order assigned - {$orderReference}";
            $intro = "A new order placed by {$this->clientName} contains items assigned to you.";
        } elseif ($isClient) {
            $subject = "Order received - {$orderReference}";
            $intro = 'Your order has been received successfully.';
        } else {
            $subject = "New order placed - {$orderReference}";
            $intro = "A new order has been placed by {$this->clientName}.";
        }
        
        $deliveryDateTime = collect([
            $this->order->delivery_date,
            $this->order->delivery_time,
        ])->filter()->implode(' at ');

        return (new MailMessage)
        ->subject($subject)
        ->view('emails.notification', [
            'subjectLine' => $subject,
            'preheader' => "Order {$orderReference}: {$intro}",
            'badge' => $isSupplier ? 'New order assigned' : 'Order received',
            'title' => $isSupplier ? 'A new order needs your attention' : 'Thank you for your order',
            'recipientName' => $displayName,
            'bodyText' => $intro,
            'details' => [
                'Order reference' => $orderReference,
                'Items' => $itemSummary,
                'Delivery' => $deliveryDateTime ?: 'To be confirmed',
                'Address' => $this->order->delivery_address,
            ],
            'actionText' => 'View order details',
            'actionUrl' => rtrim(config('app.frontend_url'), '/'),
            'logoUrl' => config('app.email_logo_url'),
            'brandColor' => config('app.email_brand_color'),
            'accentColor' => config('app.email_accent_color'),
            'supportAddress' => config('app.email_support_address'),
            'supportPhone' => config('app.email_support_phone'),
        ]);
    }
}
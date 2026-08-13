<?php

namespace App\Notifications;

use App\Models\Orders;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderCreatedNotification extends Notification
{
    public function __construct(
        public Orders $order,
        public string $clientName
    ) {}

    public function via(object $notifiable): array
    {
        $isClient = $notifiable->id === $this->order->client_id;

        // Client also gets the email confirmation; admins stay in-app only.
        return $isClient
            ? ['database', 'broadcast', 'mail']
            : ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        $isClient = $notifiable->id === $this->order->client_id;

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
        // Ensure the template's relations are loaded.
        $this->order->loadMissing(['items.product']);

        $orderRef  = $this->order->po_number ?: "#{$this->order->id}";
        $portalUrl = rtrim(config('app.frontend_url', config('app.url')), '/')
            . "/client/orders/{$this->order->id}";

        return (new MailMessage)
            ->subject("Order received — {$orderRef}")
            ->view('emails.orders.confirmation', [
                'order'      => $this->order,
                'orderRef'   => $orderRef,
                'clientName' => $notifiable->contact_name ?? $notifiable->name ?? 'there',
                'portalUrl'  => $portalUrl,
            ]);
    }
}
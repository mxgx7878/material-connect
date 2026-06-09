<?php

namespace App\Notifications;

use App\Models\Orders;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class OrderCreatedNotification extends Notification
{
    public function __construct(
        public Orders $order,
        public string $clientName
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
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
        // onConnection('sync') => pushed to Pusher inside the same request.
        // No queue worker needed on the live server.
        return (new BroadcastMessage($this->toArray($notifiable)))->onConnection('sync');
    }
}
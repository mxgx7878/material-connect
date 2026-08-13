<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceGeneratedNotification extends Notification
{
    public function __construct(
        public Invoice $invoice,
        public string  $portalUrl
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast', 'mail'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'event'          => 'invoice.sent',
            'title'          => 'Invoice Ready',
            'message'        => "Invoice {$this->invoice->invoice_number} is ready to view and pay",
            'invoice_id'     => $this->invoice->id,
            'invoice_number' => $this->invoice->invoice_number,
            'order_id'       => $this->invoice->order_id,
            'total_amount'   => (float) $this->invoice->total_amount,
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return (new BroadcastMessage($this->toArray($notifiable)))->onConnection('sync');
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Invoice {$this->invoice->invoice_number} for your order")
            ->view('emails.invoices.generated', [
                'invoice'   => $this->invoice,
                'portalUrl' => $this->portalUrl,
            ]);
    }
}
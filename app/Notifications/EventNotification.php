<?php

namespace App\Notifications;

use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Generic event notification: in-app (database + broadcast) AND email.
 *
 * Built by App\Services\EventNotifier. The caller decides the wording and the
 * detail rows, so customer-facing sends never carry supplier cost, and
 * admin sends can.
 *
 * Anonymous recipients (the owner alert address) get email only.
 */
class EventNotification extends Notification
{
    public function __construct(
        public string  $event,
        public string  $title,
        public string  $message,
        public array   $details = [],
        public ?string $actionUrl = null,
        public ?string $actionText = null,
        public array   $data = [],
        public string  $badge = 'Update'
    ) {}

    public function via(object $notifiable): array
    {
        if ($notifiable instanceof AnonymousNotifiable) {
            return ['mail'];
        }

        return ['database', 'broadcast', 'mail'];
    }

    public function toArray(object $notifiable): array
    {
        return array_merge([
            'event'   => $this->event,
            'title'   => $this->title,
            'message' => $this->message,
        ], $this->data);
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return (new BroadcastMessage($this->toArray($notifiable)))->onConnection('sync');
    }

    public function toMail(object $notifiable): MailMessage
    {
        $name = $notifiable instanceof AnonymousNotifiable
            ? 'Admin'
            : ($notifiable->contact_name ?? $notifiable->name ?? 'there');

        return (new MailMessage)
            ->subject($this->title)
            ->view('emails.notification', [
                'subjectLine'    => $this->title,
                'preheader'      => $this->message,
                'badge'          => $this->badge,
                'title'          => $this->title,
                'recipientName'  => $name,
                'bodyText'       => $this->message,
                'details'        => $this->details,
                'actionText'     => $this->actionText,
                'actionUrl'      => $this->actionUrl,
                'logoUrl'        => config('app.email_logo_url'),
                'brandColor'     => config('app.email_brand_color'),
                'accentColor'    => config('app.email_accent_color'),
                'supportAddress' => config('app.email_support_address'),
                'supportPhone'   => config('app.email_support_phone'),
            ]);
    }
}

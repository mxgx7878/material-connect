<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RegistrationSuccessfulNotification extends Notification
{
    use Queueable;

    public function __construct(private string $role)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $roleLabel = ucfirst($this->role);
        $recipientName = $notifiable->contact_name ?: $notifiable->name;
        $subject = "Welcome to " . config('app.name') . " - {$roleLabel} Registration";
        $bodyText = $this->role === 'supplier'
            ? 'Your supplier account is ready. You can now manage your offers and review orders assigned to your business.'
            : 'Your client account is ready. You can now source construction materials, manage your projects and place orders through the Material Connect portal.';
    
        return (new MailMessage)
            ->subject($subject)
            ->view('emails.notification', [
                'subjectLine' => $subject,
                'preheader' => "Your {$this->role} account is ready.",
                'badge' => 'Account ready',
                'title' => 'Welcome to ' . config('app.name'),
                'recipientName' => $recipientName,
                'bodyText' => $bodyText,
                'details' => [
                    'Account type' => $roleLabel,
                    'Email address' => $notifiable->email,
                    'Status' => 'Active',
                ],
                'actionText' => 'Sign in to your account',
                'actionUrl' => rtrim(config('app.frontend_url'), '/') . '/login',
                'logoUrl' => config('app.email_logo_url'),
                'brandColor' => config('app.email_brand_color'),
                'accentColor' => config('app.email_accent_color'),
                'supportAddress' => config('app.email_support_address'),
                'supportPhone' => config('app.email_support_phone'),
            ]);
    }
}
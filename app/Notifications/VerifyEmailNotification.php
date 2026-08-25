<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class VerifyEmailNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $verifyUrl = $this->verificationUrl($notifiable);
        $subject = 'Verify your email - ' . config('app.name');
        $recipientName = $notifiable->contact_name ?: $notifiable->name;

        return (new MailMessage)
            ->subject($subject)
            ->view('emails.notification', [
                'subjectLine'  => $subject,
                'preheader'    => 'Confirm your email to activate your account.',
                'badge'        => 'Action required',
                'title'        => 'Verify your email address',
                'recipientName'=> $recipientName,
                'bodyText'     => 'Thanks for registering with ' . config('app.name')
                                  . '. Please confirm your email address to activate your account '
                                  . 'and start placing orders. This link expires in 60 minutes.',
                'details'      => [
                    'Email address' => $notifiable->email,
                    'Status'        => 'Pending verification',
                ],
                'actionText'   => 'Verify email address',
                'actionUrl'    => $verifyUrl,
                'logoUrl'      => config('app.email_logo_url'),
                'brandColor'   => config('app.email_brand_color'),
                'accentColor'  => config('app.email_accent_color'),
                'supportAddress' => config('app.email_support_address'),
                'supportPhone' => config('app.email_support_phone'),
            ]);
    }

    protected function verificationUrl(object $notifiable): string
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id'   => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }
}
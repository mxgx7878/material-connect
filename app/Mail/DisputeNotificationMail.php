<?php
// FILE PATH: app/Mail/DisputeNotificationMail.php

namespace App\Mail;

use App\Models\Dispute;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DisputeNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Dispute $dispute,
        public string  $subjectLine,
        public string  $heading,
        public string  $bodyMessage,
        public string  $recipientRole = 'client'
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->subjectLine);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.disputes.notification');
    }
}
<?php
// FILE PATH: app/Services/DisputeNotificationService.php

namespace App\Services;

use App\Mail\DisputeNotificationMail;
use App\Models\Dispute;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class DisputeNotificationService
{
    public function disputeRaised(Dispute $dispute): void
    {
        $this->mailClient(
            $dispute,
            "Dispute {$dispute->dispute_number} received",
            'We received your dispute',
            "Your dispute regarding invoice {$dispute->invoice?->invoice_number} has been received and is being processed."
        );

        if ($dispute->supplier_id) {
            $this->mailSupplier(
                $dispute,
                "Action required: Dispute {$dispute->dispute_number}",
                'Dispute response required',
                "A dispute has been raised against an invoice involving your supply. You have "
                    . Dispute::SUPPLIER_RESPONSE_HOURS . " hours to respond."
            );
        } else {
            $this->mailAdmins(
                $dispute,
                "New dispute {$dispute->dispute_number} — needs admin",
                'New dispute (no supplier assigned)',
                'A dispute was raised but no supplier could be auto-assigned. Please review manually.'
            );
        }
    }

    public function supplierResponded(Dispute $dispute): void
    {
        $this->mailAdmins(
            $dispute,
            "Supplier responded on {$dispute->dispute_number}",
            'Supplier proposed a resolution',
            "Supplier proposed '{$dispute->supplier_proposed_outcome}'. Review and approve or override."
        );

        $this->mailClient(
            $dispute,
            "Update on dispute {$dispute->dispute_number}",
            'Supplier has responded',
            'The supplier has responded to your dispute. Admin is reviewing the proposal.'
        );
    }

    public function disputeEscalated(Dispute $dispute): void
    {
        $this->mailAdmins(
            $dispute,
            "Escalated: {$dispute->dispute_number}",
            'Dispute auto-escalated',
            'Supplier did not respond within the 48-hour window. This dispute now requires admin review.'
        );
    }

    public function disputeResolved(Dispute $dispute): void
    {
        $outcome = ucfirst(str_replace('_', ' ', $dispute->resolution_outcome));

        $this->mailClient(
            $dispute,
            "Dispute {$dispute->dispute_number} resolved",
            'Your dispute has been resolved',
            "Resolution: {$outcome}." . ($dispute->resolution_amount ? " Amount: \${$dispute->resolution_amount}." : '')
        );

        if ($dispute->supplier_id) {
            $this->mailSupplier(
                $dispute,
                "Dispute {$dispute->dispute_number} resolved",
                'Dispute resolved',
                "Admin resolved this dispute as {$outcome}."
            );
        }
    }

    public function disputeRejected(Dispute $dispute): void
    {
        $this->mailClient(
            $dispute,
            "Dispute {$dispute->dispute_number} rejected",
            'Your dispute was rejected',
            'After review, admin has rejected this dispute. See full details in the portal.'
        );

        if ($dispute->supplier_id) {
            $this->mailSupplier(
                $dispute,
                "Dispute {$dispute->dispute_number} rejected",
                'Dispute rejected',
                'Admin rejected this dispute. No action required.'
            );
        }
    }

    public function disputeWithdrawn(Dispute $dispute): void
    {
        if ($dispute->supplier_id) {
            $this->mailSupplier(
                $dispute,
                "Dispute {$dispute->dispute_number} withdrawn",
                'Dispute withdrawn',
                'The client has withdrawn this dispute. No action required.'
            );
        }
    }

    // ── Helpers ─────────────────────────────────────────────────────

    protected function mailClient(Dispute $d, string $subject, string $heading, string $message): void
    {
        $email = $d->client?->email;
        if (!$email) return;
        $this->safeSend($email, new DisputeNotificationMail($d, $subject, $heading, $message, 'client'));
    }

    protected function mailSupplier(Dispute $d, string $subject, string $heading, string $message): void
    {
        $email = $d->supplier?->email;
        if (!$email) return;
        $this->safeSend($email, new DisputeNotificationMail($d, $subject, $heading, $message, 'supplier'));
    }

    protected function mailAdmins(Dispute $d, string $subject, string $heading, string $message): void
    {
        $adminEmails = User::where('role', 'admin')->pluck('email')->filter()->all();
        if (empty($adminEmails)) {
            Log::warning('No admin users found to notify', ['dispute' => $d->dispute_number]);
            return;
        }
        $this->safeSend($adminEmails, new DisputeNotificationMail($d, $subject, $heading, $message, 'admin'));
    }

    protected function safeSend($to, DisputeNotificationMail $mail): void
    {
        try {
            Mail::to($to)->send($mail);
        } catch (\Throwable $e) {
            Log::error('Dispute notification mail failed', [
                'to'    => $to,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
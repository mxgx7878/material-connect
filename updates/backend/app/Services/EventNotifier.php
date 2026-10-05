<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\EventNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

/**
 * Single entry point for "tell people about this event".
 *
 *  - toAdmins()  : every admin user (in-app + email) AND the owner address
 *                  config('mail.admin_address') = admin@materialconnect.com.au
 *  - toUser()    : one client / supplier (in-app + email)
 *  - toUserId()  : same, by id
 *
 * Never throws: a mail/broadcast failure must never roll back or fail the
 * business action that triggered it.
 */
class EventNotifier
{
    public static function toAdmins(
        string $event,
        string $title,
        string $message,
        array $details = [],
        ?string $path = null,
        string $actionText = 'View in portal',
        array $data = [],
        string $badge = 'Admin alert'
    ): void {
        try {
            $note   = new EventNotification($event, $title, $message, $details, self::url($path), $path ? $actionText : null, $data, $badge);
            $admins = User::where('role', 'admin')->where('isDeleted', 0)->get();

            foreach ($admins as $admin) {
                try {
                    $admin->notify($note);
                } catch (\Throwable $e) {
                    Log::error('Admin event notification failed', ['event' => $event, 'to' => $admin->email, 'error' => $e->getMessage()]);
                }
            }

            // Owner address — skip if it already belongs to an admin user above.
            $already = $admins->pluck('email')->filter()->map(fn ($e) => Str::lower($e))->all();
            $extra   = collect(explode(',', (string) config('mail.admin_address', '')))
                ->map(fn ($e) => Str::lower(trim($e)))
                ->filter(fn ($e) => $e !== '' && filter_var($e, FILTER_VALIDATE_EMAIL) && ! in_array($e, $already, true))
                ->unique();

            foreach ($extra as $email) {
                try {
                    Notification::route('mail', $email)->notify($note);
                } catch (\Throwable $e) {
                    Log::error('Admin address event email failed', ['event' => $event, 'to' => $email, 'error' => $e->getMessage()]);
                }
            }
        } catch (\Throwable $e) {
            Log::error('EventNotifier::toAdmins failed', ['event' => $event, 'error' => $e->getMessage()]);
        }
    }

    public static function toUser(
        ?User $user,
        string $event,
        string $title,
        string $message,
        array $details = [],
        ?string $path = null,
        string $actionText = 'View in portal',
        array $data = [],
        string $badge = 'Update'
    ): void {
        if (! $user) {
            return;
        }

        try {
            $user->notify(new EventNotification($event, $title, $message, $details, self::url($path), $path ? $actionText : null, $data, $badge));
        } catch (\Throwable $e) {
            Log::error('User event notification failed', ['event' => $event, 'user_id' => $user->id, 'error' => $e->getMessage()]);
        }
    }

    public static function toUserId(?int $userId, string $event, string $title, string $message, array $details = [], ?string $path = null, string $actionText = 'View in portal', array $data = [], string $badge = 'Update'): void
    {
        if (! $userId) {
            return;
        }
        self::toUser(User::find($userId), $event, $title, $message, $details, $path, $actionText, $data, $badge);
    }

    /* ------------------------------------------------------------------ */
    /*  Invoice events                                                     */
    /* ------------------------------------------------------------------ */

    /** $kind: created | sent | paid | completed */
    public static function invoiceEvent(\App\Models\Invoice $invoice, string $kind, ?string $note = null): void
    {
        try {
            $invoice->loadMissing('order');
            $order  = $invoice->order;
            $ref    = $order ? ($order->po_number ?: "#{$order->id}") : "#{$invoice->order_id}";
            $num    = $invoice->invoice_number;
            $amount = '$' . number_format((float) $invoice->total_amount, 2);
            $data   = ['invoice_id' => $invoice->id, 'invoice_number' => $num, 'order_id' => $invoice->order_id, 'total_amount' => (float) $invoice->total_amount];
            $details = ['Invoice' => $num, 'Order' => $ref, 'Total (inc GST)' => $amount, 'Status' => $invoice->status];
            if ($note) {
                $details['Note'] = $note;
            }

            $clientId = (int) ($invoice->client_id ?: ($order->client_id ?? 0));

            [$clientTitle, $clientMsg, $adminTitle, $adminMsg] = match ($kind) {
                'created'   => [null, null, "Invoice {$num} created", "Invoice {$num} ({$amount}) was created for order {$ref}."],
                'sent'      => ["Invoice {$num} is ready", "Invoice {$num} ({$amount}) for order {$ref} is ready to view and pay.", "Invoice {$num} sent to client", "Invoice {$num} ({$amount}) for order {$ref} was sent to the client."],
                'paid'      => ["Payment received - invoice {$num}", "We have recorded payment of {$amount} for invoice {$num}. Thank you.", "Invoice {$num} paid", "Invoice {$num} ({$amount}) for order {$ref} has been paid."],
                'completed' => ["Invoice {$num} finalised", "Invoice {$num} for order {$ref} has been finalised.", "Invoice {$num} completed", "Invoice {$num} for order {$ref} was completed and pushed to Xero."],
                default     => [null, null, "Invoice {$num} updated", "Invoice {$num} was updated."],
            };

            // 'sent' client email is already sent by InvoiceGeneratedNotification.
            if ($clientTitle && $kind !== 'sent') {
                self::toUserId($clientId, "invoice.{$kind}", $clientTitle, $clientMsg, $details, "client/orders/{$invoice->order_id}", 'View invoice', $data, 'Invoice');
            }

            self::toAdmins("invoice.{$kind}", $adminTitle, $adminMsg, $details, "admin/orders/{$invoice->order_id}", 'Open order', $data);
        } catch (\Throwable $e) {
            Log::error('Invoice event notification failed', ['invoice_id' => $invoice->id ?? null, 'kind' => $kind, 'error' => $e->getMessage()]);
        }
    }

    private static function url(?string $path): ?string
    {
        if (! $path) {
            return null;
        }
        $base = rtrim((string) config('app.frontend_url', config('app.url')), '/');

        return $base . '/' . ltrim($path, '/');
    }
}

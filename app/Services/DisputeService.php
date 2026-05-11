<?php
// FILE PATH: app/Services/DisputeService.php

namespace App\Services;

use App\Models\CreditNote;
use App\Models\Dispute;
use App\Models\DisputeAttachment;
use App\Models\DisputeItem;
use App\Models\Invoice;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class DisputeService
{
    public const DISPUTE_WINDOW_DAYS = 7;

    public function __construct(private XeroService $xeroService) {}

    // ─────────────────────────────────────────────────────────────────
    // RAISE A DISPUTE (client)
    // ─────────────────────────────────────────────────────────────────

    /**
     * @param  Invoice $invoice
     * @param  array   $data         ['type', 'reason', 'requested_outcome'?, 'items'?[]]
     * @param  User    $client
     * @param  array   $attachments  Array of S3 metadata:
     *                               [['url' => ..., 'name' => ..., 'mime_type' => ..., 'size' => ...], ...]
     */
    public function raiseDispute(Invoice $invoice, array $data, User $client, array $attachments = []): Dispute
    {
        $this->assertEligibleForDispute($invoice, $client);

        $type = $data['type'] ?? null;
        if (!in_array($type, Dispute::TYPES, true)) {
            throw new InvalidArgumentException("Invalid dispute type: {$type}");
        }

        if ($type !== 'whole_invoice' && empty($data['items'])) {
            throw new InvalidArgumentException("Dispute type '{$type}' requires at least one item.");
        }

        return DB::transaction(function () use ($invoice, $data, $client, $attachments, $type) {
            $dispute = Dispute::create([
                'dispute_number'    => Dispute::generateDisputeNumber(),
                'invoice_id'        => $invoice->id,
                'client_id'         => $client->id,
                'type'              => $type,
                'status'            => 'open',
                'reason'            => $data['reason'],
                'requested_outcome' => $data['requested_outcome'] ?? null,
            ]);

            if ($type !== 'whole_invoice' && !empty($data['items'])) {
                foreach ($data['items'] as $item) {
                    DisputeItem::create([
                        'dispute_id'                  => $dispute->id,
                        'invoice_item_id'             => $item['invoice_item_id'] ?? null,
                        'invoice_item_surcharge_id'   => $item['invoice_item_surcharge_id'] ?? null,
                        'invoice_item_testing_fee_id' => $item['invoice_item_testing_fee_id'] ?? null,
                        'disputed_quantity'           => $item['disputed_quantity'] ?? null,
                        'disputed_amount'             => $item['disputed_amount'] ?? null,
                        'notes'                       => $item['notes'] ?? null,
                    ]);
                }
            }

            foreach ($attachments as $att) {
                $this->storeAttachment($dispute, $att, $client);
            }

            $this->log(
                $invoice->order_id,
                $client->id,
                'Dispute Raised',
                "Dispute {$dispute->dispute_number} raised on invoice {$invoice->invoice_number} (type: {$type})"
            );

            return $dispute->load(['items', 'attachments']);
        });
    }

    protected function assertEligibleForDispute(Invoice $invoice, User $client): void
    {
        $invoiceClientId = $invoice->client_id ?? $invoice->order?->client_id;
        if ((int) $invoiceClientId !== (int) $client->id) {
            throw new InvalidArgumentException('You can only dispute your own invoices.');
        }

        if (in_array($invoice->status, ['Void', 'Cancelled'], true)) {
            throw new InvalidArgumentException("Cannot dispute a {$invoice->status} invoice.");
        }

        if (!$invoice->issued_date) {
            throw new InvalidArgumentException('Invoice has no issued date — cannot validate dispute window.');
        }
        $deadline = Carbon::parse($invoice->issued_date)->addDays(self::DISPUTE_WINDOW_DAYS)->endOfDay();
        if (now()->greaterThan($deadline)) {
            throw new InvalidArgumentException(
                "Dispute window closed. Disputes must be raised within " . self::DISPUTE_WINDOW_DAYS . " days of invoice issue."
            );
        }

        $hasOpen = Dispute::where('invoice_id', $invoice->id)
            ->whereIn('status', Dispute::OPEN_STATUSES)
            ->exists();
        if ($hasOpen) {
            throw new InvalidArgumentException('There is already an open dispute on this invoice.');
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // WITHDRAW (client)
    // ─────────────────────────────────────────────────────────────────

    public function withdrawDispute(Dispute $dispute, User $client): Dispute
    {
        if ((int) $dispute->client_id !== (int) $client->id) {
            throw new InvalidArgumentException('You can only withdraw your own disputes.');
        }

        if (!$dispute->isOpen()) {
            throw new InvalidArgumentException("Cannot withdraw a {$dispute->status} dispute.");
        }

        $dispute->update([
            'status'      => 'withdrawn',
            'resolved_at' => now(),
        ]);

        $this->log(
            $dispute->invoice->order_id,
            $client->id,
            'Dispute Withdrawn',
            "Dispute {$dispute->dispute_number} withdrawn by client"
        );

        return $dispute->fresh();
    }

    // ─────────────────────────────────────────────────────────────────
    // RESOLVE (admin)
    // ─────────────────────────────────────────────────────────────────

    public function resolveDispute(Dispute $dispute, array $data, User $admin): array
    {
        if (!$dispute->isOpen()) {
            throw new InvalidArgumentException("Cannot resolve a {$dispute->status} dispute.");
        }

        $outcome = $data['outcome'] ?? null;
        if (!in_array($outcome, ['full_refund', 'partial_refund', 'adjustment'], true)) {
            throw new InvalidArgumentException("Invalid resolution outcome: {$outcome}");
        }

        $invoice = $dispute->invoice;
        $creditLines = $this->buildCreditNoteLines($dispute, $outcome, $data);
        $totalAmount = array_sum(array_column($creditLines, 'amount'));

        if ($totalAmount <= 0) {
            throw new InvalidArgumentException('Credit note total must be greater than zero.');
        }

        $creditNote = DB::transaction(function () use ($dispute, $invoice, $admin, $outcome, $data, $totalAmount) {
            $cn = CreditNote::create([
                'credit_note_number' => CreditNote::generateCreditNoteNumber(),
                'dispute_id'         => $dispute->id,
                'invoice_id'         => $invoice->id,
                'total_amount'       => $totalAmount,
                'status'             => 'authorised',
                'issued_date'        => now()->toDateString(),
                'notes'              => $data['notes'] ?? null,
            ]);

            $dispute->update([
                'status'             => 'resolved',
                'resolution_outcome' => $outcome,
                'resolution_notes'   => $data['notes'] ?? null,
                'resolved_by'        => $admin->id,
                'resolved_at'        => now(),
            ]);

            $this->log(
                $invoice->order_id,
                $admin->id,
                'Dispute Resolved',
                "Dispute {$dispute->dispute_number} resolved as {$outcome}. Credit Note {$cn->credit_note_number} for \${$totalAmount}."
            );

            return $cn;
        });

        $xeroWarning = $this->pushCreditNoteToXero($creditNote, $creditLines, $admin);

        return [
            'dispute'      => $dispute->fresh(['items', 'creditNote']),
            'credit_note'  => $creditNote->fresh(),
            'xero_warning' => $xeroWarning,
        ];
    }

    public function rejectDispute(Dispute $dispute, string $reason, User $admin): Dispute
    {
        if (!$dispute->isOpen()) {
            throw new InvalidArgumentException("Cannot reject a {$dispute->status} dispute.");
        }

        $dispute->update([
            'status'             => 'rejected',
            'resolution_outcome' => 'rejected',
            'resolution_notes'   => $reason,
            'resolved_by'        => $admin->id,
            'resolved_at'        => now(),
        ]);

        $this->log(
            $dispute->invoice->order_id,
            $admin->id,
            'Dispute Rejected',
            "Dispute {$dispute->dispute_number} rejected: {$reason}"
        );

        return $dispute->fresh();
    }

    public function markUnderReview(Dispute $dispute): Dispute
    {
        if ($dispute->status !== 'open') {
            throw new InvalidArgumentException("Only open disputes can be marked under review.");
        }

        $dispute->update(['status' => 'under_review']);
        return $dispute->fresh();
    }

    // ─────────────────────────────────────────────────────────────────
    // ATTACHMENTS — S3-based
    // ─────────────────────────────────────────────────────────────────

    /**
     * Store an attachment record using metadata from a completed S3 upload.
     *
     * Frontend flow:
     *   1. POST /api/s3/presigned-url → receive { presigned_url, public_url, key }
     *   2. PUT file directly to presigned_url (S3 takes the bytes)
     *   3. POST attachment metadata back to this service via the dispute payload
     *
     * The backend never touches the file bytes — we just save the URL & metadata.
     *
     * @param  Dispute  $dispute
     * @param  array    $att  ['url', 'name', 'mime_type'?, 'size'?]
     * @param  User     $uploader
     */
    public function storeAttachment(Dispute $dispute, array $att, User $uploader): DisputeAttachment
    {
        if (empty($att['url']) || empty($att['name'])) {
            throw new InvalidArgumentException("Attachment requires 'url' and 'name'.");
        }

        return DisputeAttachment::create([
            'dispute_id'  => $dispute->id,
            'file_path'   => $att['url'],                    // S3 public URL
            'file_name'   => $att['name'],
            'mime_type'   => $att['mime_type'] ?? null,
            'size'        => isset($att['size']) ? (int) $att['size'] : null,
            'uploaded_by' => $uploader->id,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    // INTERNAL: build credit note lines based on outcome
    // ─────────────────────────────────────────────────────────────────

    protected function buildCreditNoteLines(Dispute $dispute, string $outcome, array $data): array
    {
        $invoice = $dispute->invoice->loadMissing(['items.surcharges', 'items.testingFees']);
        $lines = [];

        if ($outcome === 'full_refund') {
            // (a) Material/line items
            foreach ($invoice->items as $item) {
                $lines[] = [
                    'description' => $item->product_name,
                    'quantity'    => (float) $item->quantity,
                    'amount'      => (float) $item->line_total,
                ];
            }
            // (b) Surcharges
            foreach ($invoice->items as $item) {
                foreach ($item->surcharges ?? [] as $sur) {
                    $amount = (float) ($sur->calculated_amount ?? 0);
                    if ($amount <= 0) continue;
                    $lines[] = [
                        'description' => "Surcharge: {$sur->name} - {$item->product_name}",
                        'quantity'    => 1,
                        'amount'      => $amount,
                    ];
                }
            }
            // (c) Testing fees (only included = true ones, mirror push logic)
            foreach ($invoice->items as $item) {
                foreach ($item->testingFees ?? [] as $fee) {
                    if (!$fee->included) continue;
                    $amount = (float) ($fee->amount_snapshot ?? 0);
                    if ($amount <= 0) continue;

                    $label = $fee->billing_code
                        ? "Testing: {$fee->name} ({$fee->billing_code}) - {$item->product_name}"
                        : "Testing: {$fee->name} - {$item->product_name}";

                    $lines[] = [
                        'description' => $label,
                        'quantity'    => 1,
                        'amount'      => $amount,
                    ];
                }
            }
            return array_values($lines);
        }

        // partial_refund / adjustment — use dispute items
        $dispute->loadMissing([
            'items.invoiceItem',
            'items.invoiceItemSurcharge',
            'items.invoiceItemTestingFee',
        ]);

        foreach ($dispute->items as $di) {
            $amount = (float) ($di->disputed_amount ?? 0);
            if ($amount <= 0) continue;

            $description = match (true) {
                $di->invoice_item_id !== null
                    => 'Adjustment: ' . ($di->invoiceItem?->product_name ?? "Item #{$di->invoice_item_id}"),
                $di->invoice_item_surcharge_id !== null
                    => 'Adjustment: surcharge' .
                       ($di->invoiceItemSurcharge?->name ? ' ' . $di->invoiceItemSurcharge->name : " #{$di->invoice_item_surcharge_id}"),
                $di->invoice_item_testing_fee_id !== null
                    => 'Adjustment: testing fee' .
                       ($di->invoiceItemTestingFee?->name ? ' ' . $di->invoiceItemTestingFee->name : " #{$di->invoice_item_testing_fee_id}"),
                default => 'Dispute adjustment',
            };

            $lines[] = [
                'description' => $description,
                'quantity'    => (float) ($di->disputed_quantity ?? 1),
                'amount'      => $amount,
            ];
        }

        if (!empty($data['lines']) && is_array($data['lines'])) {
            $lines = $data['lines'];
        }

        return $lines;
    }

    protected function pushCreditNoteToXero(CreditNote $creditNote, array $lines, User $admin): ?string
    {
        try {
            if (!$this->xeroService->isConnected()) {
                return 'Xero is not connected. Credit note saved locally only.';
            }

            $result = $this->xeroService->pushCreditNote($creditNote, $lines);

            $creditNote->update([
                'xero_credit_note_id' => $result['xero_credit_note_id'],
            ]);

            $this->log(
                $creditNote->invoice->order_id,
                $admin->id,
                'Xero Credit Note Synced',
                "Credit Note {$creditNote->credit_note_number} pushed to Xero. Xero ID: {$result['xero_credit_note_id']}"
            );

            return null;
        } catch (\Exception $e) {
            Log::error('Xero credit note push failed', [
                'credit_note_id'     => $creditNote->id,
                'credit_note_number' => $creditNote->credit_note_number,
                'error'              => $e->getMessage(),
            ]);
            return 'Credit note created locally, but Xero sync failed: ' . $e->getMessage();
        }
    }

    protected function log(?int $orderId, int $userId, string $action, string $details): void
    {
        if (class_exists(\App\Models\ActionLog::class)) {
            \App\Models\ActionLog::create([
                'order_id' => $orderId,
                'user_id'  => $userId,
                'action'   => $action,
                'details'  => $details,
            ]);
        }
    }
}
<?php
// FILE PATH: app/Services/DisputeService.php

namespace App\Services;

use App\Models\Dispute;
use App\Models\DisputeAttachment;
use App\Models\DisputeFeedback;
use App\Models\DisputeItem;
use App\Models\DisputeResolutionLine;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class DisputeService
{
    public const DISPUTE_WINDOW_DAYS = 7;

    public function __construct(private DisputeNotificationService $notify) {}

    // ═══════════════════════════════════════════════════════════════════
    // RAISE  (client)
    // ═══════════════════════════════════════════════════════════════════

    public function raiseDispute(Invoice $invoice, array $data, User $client, array $attachments = []): Dispute
    {
        $this->assertEligibleForDispute($invoice, $client);

        $category = $data['category'] ?? null;
        if (!in_array($category, Dispute::CATEGORIES, true)) {
            throw new InvalidArgumentException(
                "Invalid dispute category: {$category}. Allowed: " . implode(', ', Dispute::CATEGORIES)
            );
        }

        $type = $data['type'] ?? 'whole_invoice';
        if (!in_array($type, Dispute::TYPES, true)) {
            throw new InvalidArgumentException("Invalid dispute type: {$type}");
        }

        if ($type !== 'whole_invoice' && empty($data['items'])) {
            throw new InvalidArgumentException("Dispute type '{$type}' requires at least one item.");
        }

        $supplierId = $this->resolveSupplierId($invoice, $data['items'] ?? []);

        return DB::transaction(function () use ($invoice, $data, $client, $attachments, $type, $category, $supplierId) {
            $dispute = Dispute::create([
                'dispute_number'             => Dispute::generateDisputeNumber(),
                'invoice_id'                 => $invoice->id,
                'client_id'                  => $client->id,
                'supplier_id'                => $supplierId,
                'type'                       => $type,
                'category'                   => $category,
                // If we have a supplier, start the 48h clock; otherwise go straight to admin review
                'status' => ($supplierId && config('disputes.supplier_workflow_enabled'))
                ? 'awaiting_supplier_response'
                : 'under_review',
                'reason'                     => $data['reason'],
                'requested_outcome'          => $data['requested_outcome'] ?? null,
                'supplier_response_deadline' => ($supplierId && config('disputes.supplier_workflow_enabled'))
                ? now()->addHours(Dispute::SUPPLIER_RESPONSE_HOURS)
                : null,
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
                "Dispute {$dispute->dispute_number} raised on invoice {$invoice->invoice_number} (category: {$category}, type: {$type})"
            );

            $dispute->load(['items', 'attachments', 'supplier', 'client', 'invoice']);
            $this->notify->disputeRaised($dispute);

            return $dispute;
        });
    }

    protected function assertEligibleForDispute(Invoice $invoice, User $client): void
    {
        $invoiceClientId = $invoice->client_id ?? $invoice->order?->client_id;
        if ((int) $invoiceClientId !== (int) $client->id) {
            throw new InvalidArgumentException('You can only dispute your own invoices.');
        }

        if (in_array($invoice->status, ['Void', 'Cancelled', 'Completed'], true)) {
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

    /**
     * Determine which supplier should respond to this dispute.
     * Resolution path: invoice_item → order_item → supplier_id
     *
     * For multi-supplier disputes, picks the first supplier. Admin can manually
     * involve other suppliers via notes if needed (v1 simplification).
     */
    protected function resolveSupplierId(Invoice $invoice, array $items): ?int
    {
        $invoiceItemIds = collect($items)->pluck('invoice_item_id')->filter()->unique()->values();

        if ($invoiceItemIds->isNotEmpty()) {
            $supplierIds = InvoiceItem::whereIn('id', $invoiceItemIds)
                ->with('orderItem:id,supplier_id')
                ->get()
                ->pluck('orderItem.supplier_id')
                ->filter()
                ->unique()
                ->values();
        } else {
            $supplierIds = $invoice->items()
                ->with('orderItem:id,supplier_id')
                ->get()
                ->pluck('orderItem.supplier_id')
                ->filter()
                ->unique()
                ->values();
        }

        return $supplierIds->isNotEmpty() ? (int) $supplierIds->first() : null;
    }

    // ═══════════════════════════════════════════════════════════════════
    // WITHDRAW  (client)
    // ═══════════════════════════════════════════════════════════════════

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

        $dispute = $dispute->fresh(['invoice', 'client', 'supplier']);
        $this->notify->disputeWithdrawn($dispute);

        return $dispute;
    }

    // ═══════════════════════════════════════════════════════════════════
    // SUPPLIER RESPONSE  (supplier)
    // ═══════════════════════════════════════════════════════════════════

    /**
     * Supplier proposes a resolution. Admin must approve before it takes effect.
     */
    public function supplierRespond(Dispute $dispute, array $data, User $supplier): Dispute
    {
        if ((int) $dispute->supplier_id !== (int) $supplier->id) {
            throw new InvalidArgumentException('You are not assigned to this dispute.');
        }

        if ($dispute->status !== 'awaiting_supplier_response') {
            throw new InvalidArgumentException("Cannot respond to a dispute in status {$dispute->status}.");
        }

        $outcome = $data['proposed_outcome'] ?? null;
        if (!in_array($outcome, Dispute::RESOLUTION_OUTCOMES, true)) {
            throw new InvalidArgumentException(
                "Invalid proposed outcome: {$outcome}. Allowed: " . implode(', ', Dispute::RESOLUTION_OUTCOMES)
            );
        }

        $dispute->update([
            'supplier_responded_at'     => now(),
            'supplier_response_notes'   => $data['response_notes'] ?? null,
            'supplier_proposed_outcome' => $outcome,
            'status'                    => 'supplier_responded',
        ]);

        $this->log(
            $dispute->invoice->order_id,
            $supplier->id,
            'Supplier Response',
            "Supplier proposed '{$outcome}' on dispute {$dispute->dispute_number}"
        );

        $dispute = $dispute->fresh(['supplier', 'client', 'invoice']);
        $this->notify->supplierResponded($dispute);

        return $dispute;
    }

    // ═══════════════════════════════════════════════════════════════════
    // AUTO-ESCALATE  (scheduled job)
    // ═══════════════════════════════════════════════════════════════════

    public function escalateDispute(Dispute $dispute): Dispute
    {
        if ($dispute->status !== 'awaiting_supplier_response') {
            throw new InvalidArgumentException("Cannot escalate a dispute in status {$dispute->status}.");
        }

        if (!$dispute->isSupplierWindowExpired()) {
            throw new InvalidArgumentException('Supplier response window has not yet expired.');
        }

        $dispute->update([
            'status'       => 'under_review',
            'escalated_at' => now(),
        ]);

        $this->log(
            $dispute->invoice->order_id,
            null,
            'Dispute Auto-Escalated',
            "Dispute {$dispute->dispute_number} auto-escalated to admin (supplier did not respond within "
                . Dispute::SUPPLIER_RESPONSE_HOURS . "h)"
        );

        $dispute = $dispute->fresh(['supplier', 'client', 'invoice']);
        $this->notify->disputeEscalated($dispute);

        return $dispute;
    }

    // ═══════════════════════════════════════════════════════════════════
    // MARK UNDER REVIEW  (admin — manual)
    // ═══════════════════════════════════════════════════════════════════

    public function markUnderReview(Dispute $dispute): Dispute
    {
        if (!in_array($dispute->status, ['open', 'awaiting_supplier_response', 'supplier_responded'], true)) {
            throw new InvalidArgumentException("Cannot mark under review a dispute in status {$dispute->status}.");
        }

        $dispute->update(['status' => 'under_review']);
        return $dispute->fresh();
    }

    // ═══════════════════════════════════════════════════════════════════
    // RESOLVE  (admin)
    // ═══════════════════════════════════════════════════════════════════

    /**
     * Outcomes accepted here:
     *   refund          → requires 'amount' + 'lines' (or auto-generates a single line)
     *   partial_credit  → same as refund
     *   replacement     → no monetary effect (Option C: tracked locally, supplier handles offline)
     *
     * For rejection, call rejectDispute() instead.
     */
    public function resolveDispute(Dispute $dispute, array $data, User $admin): Dispute
    {
        if (!$dispute->isOpen()) {
            throw new InvalidArgumentException("Cannot resolve a {$dispute->status} dispute.");
        }

        $outcome = $data['outcome'] ?? null;
        if (!in_array($outcome, ['refund', 'replacement', 'partial_credit'], true)) {
            throw new InvalidArgumentException(
                "Invalid outcome: {$outcome}. Use 'refund', 'replacement', or 'partial_credit'. For rejection, use the reject endpoint."
            );
        }

        $resolutionAmount = null;
        $lines            = [];

        if (in_array($outcome, ['refund', 'partial_credit'], true)) {
            $resolutionAmount = isset($data['amount']) ? (float) $data['amount'] : null;
            if ($resolutionAmount === null || $resolutionAmount <= 0) {
                throw new InvalidArgumentException("Outcome '{$outcome}' requires a positive 'amount'.");
            }

            $lines = $data['lines'] ?? [];
            if (empty($lines)) {
                $lines = [[
                    'description' => ucfirst(str_replace('_', ' ', $outcome)) . " for dispute {$dispute->dispute_number}",
                    'quantity'    => 1,
                    'amount'      => $resolutionAmount,
                ]];
            }

            $sum = round(array_sum(array_map(fn($l) => (float) ($l['amount'] ?? 0), $lines)), 2);
            if (abs($sum - $resolutionAmount) > 0.01) {
                throw new InvalidArgumentException(
                    "Sum of line amounts ({$sum}) must equal resolution amount ({$resolutionAmount})."
                );
            }
        }

        $dispute = DB::transaction(function () use ($dispute, $admin, $outcome, $resolutionAmount, $lines, $data) {
            $dispute->update([
                'status'             => 'resolved',
                'resolution_outcome' => $outcome,
                'resolution_amount'  => $resolutionAmount,
                'resolution_notes'   => $data['notes'] ?? null,
                'resolved_by'        => $admin->id,
                'resolved_at'        => now(),
            ]);

            // Defensive: clear any prior resolution lines if this is being re-resolved
            DisputeResolutionLine::where('dispute_id', $dispute->id)->delete();

            foreach ($lines as $line) {
                DisputeResolutionLine::create([
                    'dispute_id'  => $dispute->id,
                    'description' => $line['description'],
                    'quantity'    => $line['quantity'] ?? 1,
                    'amount'      => $line['amount'],
                ]);
            }

            $this->log(
                $dispute->invoice->order_id,
                $admin->id,
                'Dispute Resolved',
                "Dispute {$dispute->dispute_number} resolved as {$outcome}" . ($resolutionAmount ? " (\${$resolutionAmount})" : '')
            );

            return $dispute->fresh(['resolutionLines', 'invoice', 'client', 'supplier']);
        });

        $this->notify->disputeResolved($dispute);

        return $dispute;
    }

    // ═══════════════════════════════════════════════════════════════════
    // REJECT  (admin)
    // ═══════════════════════════════════════════════════════════════════

    public function rejectDispute(Dispute $dispute, string $reason, User $admin): Dispute
    {
        if (!$dispute->isOpen()) {
            throw new InvalidArgumentException("Cannot reject a {$dispute->status} dispute.");
        }

        $dispute->update([
            'status'             => 'rejected',
            'resolution_outcome' => 'rejection',
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

        $dispute = $dispute->fresh(['invoice', 'client', 'supplier']);
        $this->notify->disputeRejected($dispute);

        return $dispute;
    }

    // ═══════════════════════════════════════════════════════════════════
    // FEEDBACK  (client)
    // ═══════════════════════════════════════════════════════════════════

    public function submitFeedback(Dispute $dispute, array $data, User $client): DisputeFeedback
    {
        if ((int) $dispute->client_id !== (int) $client->id) {
            throw new InvalidArgumentException('You can only submit feedback for your own disputes.');
        }

        if (!in_array($dispute->status, ['resolved', 'rejected'], true)) {
            throw new InvalidArgumentException('Feedback can only be submitted on resolved or rejected disputes.');
        }

        if ($dispute->feedback()->exists()) {
            throw new InvalidArgumentException('Feedback has already been submitted for this dispute.');
        }

        $rating = (int) ($data['rating'] ?? 0);
        if ($rating < 1 || $rating > 5) {
            throw new InvalidArgumentException('Rating must be between 1 and 5.');
        }

        $feedback = DisputeFeedback::create([
            'dispute_id' => $dispute->id,
            'client_id'  => $client->id,
            'rating'     => $rating,
            'comments'   => $data['comments'] ?? null,
        ]);

        $this->log(
            $dispute->invoice->order_id,
            $client->id,
            'Dispute Feedback Submitted',
            "Client rated dispute {$dispute->dispute_number}: {$rating}/5"
        );

        return $feedback;
    }

    // ═══════════════════════════════════════════════════════════════════
    // ATTACHMENTS
    // ═══════════════════════════════════════════════════════════════════

    public function storeAttachment(Dispute $dispute, array $att, User $uploader): DisputeAttachment
    {
        if (empty($att['url']) || empty($att['name'])) {
            throw new InvalidArgumentException("Attachment requires 'url' and 'name'.");
        }

        return DisputeAttachment::create([
            'dispute_id'  => $dispute->id,
            'file_path'   => $att['url'],
            'file_name'   => $att['name'],
            'mime_type'   => $att['mime_type'] ?? null,
            'size'        => isset($att['size']) ? (int) $att['size'] : null,
            'uploaded_by' => $uploader->id,
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════
    // INTERNAL
    // ═══════════════════════════════════════════════════════════════════

    protected function log(?int $orderId, ?int $userId, string $action, string $details): void
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



    /**
     * Admin submits a supplier response on the supplier's behalf.
     *
     * Same outcome as supplierRespond() but with admin-only allowances:
     *   - No "you must be the assigned supplier" check
     *   - Allowed from any open status (not just awaiting_supplier_response)
     *   - Action log clearly attributes to admin
     */
    public function adminRespondAsSupplier(Dispute $dispute, array $data, User $admin): Dispute
    {
        if (!$dispute->isOpen()) {
            throw new InvalidArgumentException("Cannot respond on a {$dispute->status} dispute.");
        }

        $outcome = $data['proposed_outcome'] ?? null;
        if (!in_array($outcome, Dispute::RESOLUTION_OUTCOMES, true)) {
            throw new InvalidArgumentException(
                "Invalid proposed outcome: {$outcome}. Allowed: " . implode(', ', Dispute::RESOLUTION_OUTCOMES)
            );
        }

        $dispute->update([
            'supplier_responded_at'     => now(),
            'supplier_response_notes'   => $data['response_notes'] ?? null,
            'supplier_proposed_outcome' => $outcome,
            'status'                    => 'supplier_responded',
        ]);

        $this->log(
            $dispute->invoice->order_id,
            $admin->id,
            'Supplier Response (Admin)',
            "Admin {$admin->name} submitted supplier response '{$outcome}' on dispute {$dispute->dispute_number}"
                . ($dispute->supplier_id ? " (on behalf of supplier #{$dispute->supplier_id})" : '')
        );

        $dispute = $dispute->fresh(['supplier', 'client', 'invoice']);
        $this->notify->supplierResponded($dispute);

        return $dispute;
    }
}
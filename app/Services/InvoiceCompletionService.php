<?php
// FILE PATH: app/Services/InvoiceCompletionService.php

namespace App\Services;

use App\Models\Dispute;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

/**
 * Handles the "Mark Invoice as Completed" flow.
 *
 *  1. All disputes on the invoice must be in a terminal state
 *     (resolved | rejected | withdrawn).
 *  2. Local: invoice locked, status = Completed.
 *  3. Xero: bundled push containing original lines PLUS adjustment lines
 *     derived from resolved disputes (refunds & partial credits become
 *     negative lines; replacements get a note line; rejections add nothing).
 *
 *  Once Completed, the invoice can no longer be edited or disputed.
 */
class InvoiceCompletionService
{
    public function __construct(private XeroService $xeroService) {}

    public function markCompleted(Invoice $invoice, User $admin): array
    {
        if ($invoice->status === 'Completed') {
            throw new InvalidArgumentException('Invoice is already completed.');
        }

        if (in_array($invoice->status, ['Cancelled', 'Void'], true)) {
            throw new InvalidArgumentException("Cannot complete a {$invoice->status} invoice.");
        }

        $openDisputes = $invoice->disputes()
            ->whereIn('status', Dispute::OPEN_STATUSES)
            ->get(['id', 'dispute_number', 'status']);

        if ($openDisputes->isNotEmpty()) {
            throw new InvalidArgumentException(
                'Cannot complete invoice while disputes are open: '
                . $openDisputes->pluck('dispute_number')->implode(', ')
            );
        }

        // ── Strict atomic: Xero must succeed BEFORE local completion ──

        if (!$this->xeroService->isConnected()) {
            throw new InvalidArgumentException(
                'Cannot complete invoice: Xero is not connected. Connect Xero and try again.'
            );
        }

        $invoice->loadMissing([
            'items.surcharges',
            'items.testingFees',
            'disputes' => fn($q) => $q->where('status', 'resolved')->with('resolutionLines'),
            'client',
            'order.client',
        ]);

        try {
            $xeroResult = $this->xeroService->pushCompletedInvoice($invoice);
        } catch (\Exception $e) {
            Log::error('Xero completion push failed — completion aborted', [
                'invoice_id'     => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'error'          => $e->getMessage(),
            ]);
            throw new InvalidArgumentException(
                'Cannot complete invoice: Xero push failed. ' . $e->getMessage()
            );
        }

        // ── Xero succeeded. Now commit local state. ──

        $invoice = DB::transaction(function () use ($invoice, $admin, $xeroResult) {
            $invoice->update([
                'status'          => 'Completed',
                'completed_at'    => now(),
                'completed_by'    => $admin->id,
                'xero_invoice_id' => $xeroResult['xero_invoice_id'],
            ]);

            if (class_exists(\App\Models\ActionLog::class)) {
                \App\Models\ActionLog::create([
                    'order_id' => $invoice->order_id,
                    'user_id'  => $admin->id,
                    'action'   => 'Invoice Completed',
                    'details'  => "Invoice {$invoice->invoice_number} marked completed and pushed to Xero (ID: {$xeroResult['xero_invoice_id']})",
                ]);
            }

            return $invoice->fresh();
        });

        return [
            'invoice'     => $invoice->fresh(['items', 'disputes.resolutionLines', 'completedBy']),
            'xero_result' => [
                'pushed'              => true,
                'xero_invoice_id'     => $xeroResult['xero_invoice_id'],
                'xero_invoice_number' => $xeroResult['xero_invoice_number'] ?? null,
                'xero_status'         => $xeroResult['xero_status'] ?? null,
            ],
        ];
    }

    protected function pushToXero(Invoice $invoice, User $admin): array
    {
        try {
            if (!$this->xeroService->isConnected()) {
                return [
                    'pushed'  => false,
                    'warning' => 'Xero is not connected. Invoice marked completed locally only.',
                ];
            }

            $invoice->loadMissing([
                'items.surcharges',
                'items.testingFees',
                'disputes' => fn($q) => $q->where('status', 'resolved')->with('resolutionLines'),
                'client',
                'order.client',
            ]);

            $result = $this->xeroService->pushCompletedInvoice($invoice);

            $invoice->update(['xero_invoice_id' => $result['xero_invoice_id']]);

            if (class_exists(\App\Models\ActionLog::class)) {
                \App\Models\ActionLog::create([
                    'order_id' => $invoice->order_id,
                    'user_id'  => $admin->id,
                    'action'   => 'Xero Invoice Synced',
                    'details'  => "Invoice {$invoice->invoice_number} pushed to Xero on completion. Xero ID: {$result['xero_invoice_id']}",
                ]);
            }

            return [
                'pushed'              => true,
                'xero_invoice_id'     => $result['xero_invoice_id'],
                'xero_invoice_number' => $result['xero_invoice_number'] ?? null,
                'xero_status'         => $result['xero_status'] ?? null,
            ];

        } catch (\Exception $e) {
            Log::error('Xero completion push failed', [
                'invoice_id'     => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'error'          => $e->getMessage(),
            ]);

            return [
                'pushed'  => false,
                'warning' => 'Invoice completed locally but Xero push failed: ' . $e->getMessage(),
            ];
        }
    }
}
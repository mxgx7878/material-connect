<?php
// FILE PATH: app/Http/Controllers/Admin/DisputeController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dispute;
use App\Services\DisputeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DisputeController extends Controller
{
    public function __construct(private DisputeService $disputeService) {}

    public function index(Request $request): JsonResponse
    {
        $query = Dispute::with(['invoice:id,invoice_number,total_amount,status', 'client:id,name,email'])
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }
        if ($request->filled('invoice_id')) {
            $query->where('invoice_id', $request->invoice_id);
        }
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to);
        }

        return response()->json([
            'success' => true,
            'data'    => $query->paginate((int) $request->get('per_page', 15)),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $dispute = Dispute::with([
            'invoice.items.surcharges',
            'invoice.items.testingFees',
            'invoice.order:id,po_number',
            'client:id,name,email',
            'items.invoiceItem',
            'items.invoiceItemSurcharge',
            'items.invoiceItemTestingFee',
            'attachments.uploader:id,name',
            'creditNote',
            'resolvedBy:id,name',
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $dispute,
        ]);
    }

    public function markUnderReview(int $id): JsonResponse
    {
        $dispute = Dispute::findOrFail($id);

        try {
            $dispute = $this->disputeService->markUnderReview($dispute);
            return response()->json([
                'success' => true,
                'message' => 'Dispute marked as under review.',
                'data'    => $dispute,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function resolve(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'outcome'       => 'required|in:full_refund,partial_refund,adjustment',
            'notes'         => 'nullable|string|max:2000',
            'refund_amount' => 'nullable|numeric|min:0',
            'lines'         => 'nullable|array',
            'lines.*.description' => 'required_with:lines|string|max:500',
            'lines.*.quantity'    => 'required_with:lines|numeric|min:0',
            'lines.*.amount'      => 'required_with:lines|numeric|min:0',
        ]);

        $dispute = Dispute::with('invoice')->findOrFail($id);

        try {
            $result = $this->disputeService->resolveDispute(
                dispute: $dispute,
                data:    $request->only(['outcome', 'notes', 'refund_amount', 'lines']),
                admin:   $request->user(),
            );

            $response = [
                'success'     => true,
                'message'     => "Dispute resolved as {$request->outcome}.",
                'dispute'     => $result['dispute'],
                'credit_note' => $result['credit_note'],
            ];
            if ($result['xero_warning']) {
                $response['xero_warning'] = $result['xero_warning'];
            }
            return response()->json($response);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function reject(Request $request, int $id): JsonResponse
    {
        $request->validate(['reason' => 'required|string|max:2000']);

        $dispute = Dispute::with('invoice')->findOrFail($id);

        try {
            $dispute = $this->disputeService->rejectDispute(
                dispute: $dispute,
                reason:  $request->reason,
                admin:   $request->user(),
            );
            return response()->json([
                'success' => true,
                'message' => 'Dispute rejected.',
                'data'    => $dispute,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
}
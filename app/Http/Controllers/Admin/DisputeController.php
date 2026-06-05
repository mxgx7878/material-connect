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

    /**
     * GET /api/admin/disputes
     * Filters: status, category, client_id, supplier_id, invoice_id, date_from, date_to
     */
    public function index(Request $request): JsonResponse
    {
        $query = Dispute::with([
                'invoice:id,invoice_number,total_amount,status',
                'client:id,name,email',
                'supplier:id,name,email',
                'feedback',
            ])
            ->orderByDesc('created_at');

        foreach (['status', 'category', 'client_id', 'supplier_id', 'invoice_id'] as $f) {
            if ($request->filled($f)) $query->where($f, $request->get($f));
        }
        if ($request->filled('date_from')) $query->where('created_at', '>=', $request->date_from);
        if ($request->filled('date_to'))   $query->where('created_at', '<=', $request->date_to);

        return response()->json([
            'success' => true,
            'data'    => $query->paginate((int) $request->get('per_page', 15)),
        ]);
    }

    /**
     * GET /api/admin/disputes/{id}
     */
    public function show(int $id): JsonResponse
    {
        $dispute = Dispute::with([
            'invoice.items.surcharges',
            'invoice.items.testingFees',
            'invoice.order:id,po_number',
            'client:id,name,email',
            'supplier:id,name,email',
            'items.invoiceItem',
            'items.invoiceItemSurcharge',
            'items.invoiceItemTestingFee',
            'attachments.uploader:id,name',
            'resolutionLines',
            'resolvedBy:id,name',
            'feedback',
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $dispute,
        ]);
    }

    /**
     * POST /api/admin/disputes/{id}/under-review
     */
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

    /**
     * POST /api/admin/disputes/{id}/resolve
     *
     * Body:
     *   outcome: refund | replacement | partial_credit   (required)
     *   amount:  required for refund / partial_credit
     *   lines:   optional explicit breakdown; auto-generated if omitted
     *   notes:   optional
     */
    public function resolve(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'outcome'             => 'required|in:refund,replacement,partial_credit',
            'amount'              => 'required_if:outcome,refund,partial_credit|nullable|numeric|min:0.01',
            'notes'               => 'nullable|string|max:2000',
            'lines'               => 'nullable|array',
            'lines.*.description' => 'required_with:lines|string|max:500',
            'lines.*.quantity'    => 'required_with:lines|numeric|min:0',
            'lines.*.amount'      => 'required_with:lines|numeric|min:0',
        ]);

        $dispute = Dispute::with('invoice')->findOrFail($id);

        try {
            $dispute = $this->disputeService->resolveDispute(
                dispute: $dispute,
                data:    $request->only(['outcome', 'amount', 'notes', 'lines']),
                admin:   $request->user(),
            );

            return response()->json([
                'success' => true,
                'message' => "Dispute resolved as {$request->outcome}.",
                'data'    => $dispute,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * POST /api/admin/disputes/{id}/reject
     */
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


    /**
     * POST /api/admin/disputes/{id}/respond-as-supplier
     *
     * Admin submits the supplier's response on their behalf. Useful when the
     * supplier is unreachable or admin is mediating directly.
     */
    public function respondAsSupplier(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'proposed_outcome' => 'required|in:' . implode(',', Dispute::RESOLUTION_OUTCOMES),
            'response_notes'   => 'nullable|string|max:2000',
        ]);

        $dispute = Dispute::with('invoice')->findOrFail($id);

        try {
            $dispute = $this->disputeService->adminRespondAsSupplier(
                dispute: $dispute,
                data:    $request->only(['proposed_outcome', 'response_notes']),
                admin:   $request->user(),
            );

            return response()->json([
                'success' => true,
                'message' => 'Response submitted on supplier behalf. Awaiting admin approval.',
                'data'    => $dispute,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
}
<?php
// FILE PATH: app/Http/Controllers/DisputeControllerSupplier.php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Dispute;
use App\Services\DisputeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DisputeControllerSupplier extends Controller
{
    public function __construct(private DisputeService $disputeService) {}

    /**
     * GET /api/supplier/disputes
     */
    public function index(Request $request): JsonResponse
    {
        $disputes = Dispute::with([
                'invoice:id,invoice_number',
                'client:id,name',
            ])
            ->where('supplier_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->paginate((int) $request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data'    => $disputes,
        ]);
    }

    /**
     * GET /api/supplier/disputes/{id}
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $dispute = Dispute::with([
                'invoice:id,invoice_number,issued_date',
                'invoice.items:id,invoice_id,product_name,quantity',
                'client:id,name',
                'items.invoiceItem',
                'attachments',
                'resolutionLines',
            ])
            ->where('supplier_id', $request->user()->id)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $dispute,
        ]);
    }

    /**
     * POST /api/supplier/disputes/{id}/respond
     *
     * Body:
     *   proposed_outcome: refund | replacement | partial_credit | rejection
     *   response_notes:   nullable string
     */
    public function respond(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'proposed_outcome' => 'required|in:' . implode(',', Dispute::RESOLUTION_OUTCOMES),
            'response_notes'   => 'nullable|string|max:2000',
        ]);

        $dispute = Dispute::findOrFail($id);

        try {
            $dispute = $this->disputeService->supplierRespond(
                dispute:  $dispute,
                data:     $request->only(['proposed_outcome', 'response_notes']),
                supplier: $request->user(),
            );

            return response()->json([
                'success' => true,
                'message' => 'Response submitted. Awaiting admin approval.',
                'data'    => $dispute,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
}
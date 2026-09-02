<?php
// FILE PATH: app/Http/Controllers/DisputeControllerClient.php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Dispute;
use App\Models\Invoice;
use App\Services\DisputeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DisputeControllerClient extends Controller
{
    public function __construct(private DisputeService $disputeService) {}

    /**
     * GET /api/client/disputes
     */
    public function index(Request $request): JsonResponse
    {
        $disputes = Dispute::with([
                'invoice:id,invoice_number,total_amount,status',
                'supplier:id,name,email',
                'feedback',
            ])
            ->where('client_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->paginate((int) $request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data'    => $disputes,
        ]);
    }

    /**
     * GET /api/client/disputes/{id}
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $dispute = Dispute::with([
                'invoice.items.surcharges',
                'invoice.items.testingFees',
                'supplier:id,name,email',
                'items.invoiceItem',
                'items.invoiceItemSurcharge',
                'items.invoiceItemTestingFee',
                'attachments',
                'resolutionLines',
                'feedback',
                'resolvedBy:id,name',
            ])
            ->where('client_id', $request->user()->id)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $dispute,
        ]);
    }

    /**
     * POST /api/client/invoices/{invoiceId}/disputes
     */
    public function store(Request $request, int $invoiceId): JsonResponse
    {
        $request->validate([
            'category'          => 'required|in:' . implode(',', Dispute::CATEGORIES),
            'type'              => 'nullable|in:' . implode(',', Dispute::TYPES),
            'reason'            => 'required|string|max:2000',
            'requested_outcome' => 'nullable|string|max:1000',

            'items'                               => 'nullable|array',
            'items.*.invoice_item_id'             => 'nullable|integer|exists:invoice_items,id',
            'items.*.invoice_item_surcharge_id'   => 'nullable|integer|exists:invoice_item_surcharges,id',
            'items.*.invoice_item_testing_fee_id' => 'nullable|integer|exists:invoice_item_testing_fees,id',
            'items.*.disputed_quantity'           => 'nullable|numeric|min:0',
            'items.*.disputed_amount'             => 'nullable|numeric|min:0',
            'items.*.notes'                       => 'nullable|string|max:1000',

            'attachments'              => 'nullable|array|max:10',
            'attachments.*.url'        => 'required_with:attachments|url|max:1000',
            'attachments.*.name'       => 'required_with:attachments|string|max:255',
            'attachments.*.mime_type'  => 'nullable|string|max:255',
            'attachments.*.size'       => 'nullable|integer|min:0|max:10485760',
        ]);

        $invoice = Invoice::with('order')->findOrFail($invoiceId);

        try {
            $dispute = $this->disputeService->raiseDispute(
                invoice:     $invoice,
                data:        $request->only(['type', 'category', 'reason', 'requested_outcome', 'items']),
                client:      $request->user(),
                attachments: $request->input('attachments', []),
            );

            return response()->json([
                'success' => true,
                'message' => "Dispute {$dispute->dispute_number} raised successfully.",
                'data'    => $dispute,
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * POST /api/client/disputes/{id}/withdraw
     */
    public function withdraw(Request $request, int $id): JsonResponse
    {
        $dispute = Dispute::findOrFail($id);

        try {
            $dispute = $this->disputeService->withdrawDispute($dispute, $request->user());
            return response()->json([
                'success' => true,
                'message' => 'Dispute withdrawn.',
                'data'    => $dispute,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * POST /api/client/disputes/{id}/attachments
     */
    public function uploadAttachment(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'attachments'              => 'required|array|max:10',
            'attachments.*.url'        => 'required|url|max:1000',
            'attachments.*.name'       => 'required|string|max:255',
            'attachments.*.mime_type'  => 'nullable|string|max:255',
            'attachments.*.size'       => 'nullable|integer|min:0|max:10485760',
        ]);

        $dispute = Dispute::where('client_id', $request->user()->id)->findOrFail($id);
        $uploaded = [];

        foreach ($request->input('attachments') as $att) {
            $uploaded[] = $this->disputeService->storeAttachment($dispute, $att, $request->user());
        }

        return response()->json([
            'success' => true,
            'message' => count($uploaded) . ' attachment(s) uploaded.',
            'data'    => $uploaded,
        ], 201);
    }

    /**
     * POST /api/client/disputes/{id}/feedback
     */
    public function submitFeedback(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'rating'   => 'required|integer|min:1|max:5',
            'comments' => 'nullable|string|max:2000',
        ]);

        $dispute = Dispute::findOrFail($id);

        try {
            $feedback = $this->disputeService->submitFeedback(
                $dispute,
                $request->only(['rating', 'comments']),
                $request->user()
            );

            return response()->json([
                'success' => true,
                'message' => 'Feedback submitted. Thank you.',
                'data'    => $feedback,
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }


        /**
     * POST /api/client/disputes/{id}/respond-to-proposal
     *
     * Client accepts or declines the proposed outcome shown in the
     * "Material Connect Response" card. Moves the dispute to under_review;
     * admin still finalises via resolve/reject.
     */
    public function respondToProposal(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'response' => 'required|in:accepted,declined',
            'notes'    => 'nullable|string|max:2000',
        ]);

        $dispute = Dispute::findOrFail($id);

        try {
            $dispute = $this->disputeService->clientRespondToProposal(
                $dispute,
                $request->only(['response', 'notes']),
                $request->user()
            );

            return response()->json([
                'success' => true,
                'message' => $request->input('response') === 'accepted'
                    ? 'Response recorded — you accepted the proposed outcome. Our team will finalise it shortly.'
                    : 'Response recorded — you declined the proposed outcome. Our team will review and follow up.',
                'data'    => $dispute,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
}
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

    public function index(Request $request): JsonResponse
    {
        $disputes = Dispute::with(['invoice:id,invoice_number,total_amount,status', 'creditNote'])
            ->where('client_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->paginate((int) $request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data'    => $disputes,
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $dispute = Dispute::with([
            'invoice.items.surcharges',
            'invoice.items.testingFees',
            'items.invoiceItem',
            'items.invoiceItemSurcharge',
            'items.invoiceItemTestingFee',
            'attachments',
            'creditNote',
        ])
            ->where('client_id', $request->user()->id)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $dispute,
        ]);
    }

    /**
     * POST /client/invoices/{invoiceId}/disputes
     *
     * Body:
     *   type:               whole_invoice | line_item | quantity | surcharge | testing_fee   (required)
     *   reason:             string  (required)
     *   requested_outcome:  string  (optional)
     *   items: array of {
     *       invoice_item_id?,
     *       invoice_item_surcharge_id?,
     *       invoice_item_testing_fee_id?,
     *       disputed_quantity?,
     *       disputed_amount?,
     *       notes?
     *   }
     *   attachments[]:      uploaded files (optional)
     */
    public function store(Request $request, int $invoiceId): JsonResponse
    {
        $request->validate([
            'type'              => 'required|in:whole_invoice,line_item,quantity,surcharge,testing_fee',
            'reason'            => 'required|string|max:2000',
            'requested_outcome' => 'nullable|string|max:1000',
            'items'             => 'nullable|array',
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
                data:        $request->only(['type', 'reason', 'requested_outcome', 'items']),
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

        if (!$dispute->isOpen()) {
            return response()->json([
                'success' => false,
                'message' => "Cannot add attachments to a {$dispute->status} dispute.",
            ], 422);
        }

        $stored = [];
        foreach ($request->input('attachments', []) as $att) {
            $stored[] = $this->disputeService->storeAttachment($dispute, $att, $request->user());
        }

        return response()->json([
            'success' => true,
            'message' => count($stored) . ' attachment(s) uploaded.',
            'data'    => $stored,
        ]);
    }
}
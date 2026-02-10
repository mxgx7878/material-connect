<?php
// FILE PATH: app/Http/Controllers/Admin/InvoiceController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Orders;
use App\Models\Invoice;
use App\Models\OrderItemDelivery;
use App\Services\InvoicePricingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class InvoiceController extends Controller
{
    protected InvoicePricingService $pricingService;

    public function __construct(InvoicePricingService $pricingService)
    {
        $this->pricingService = $pricingService;
    }

    /**
     * GET /admin/orders/{orderId}/invoiceable-deliveries
     *
     * Returns all deliveries for this order, grouped by item,
     * with invoiced status clearly marked.
     */
    public function invoiceableDeliveries(int $orderId): JsonResponse
    {
        $order = Orders::with([
            'items.product:id,product_name,unit_of_measure',
            'items.supplier:id,name',
            'items.deliveries',
        ])->findOrFail($orderId);

        $items = $order->items->map(function ($item) {
            $unit_cost = (float) $item->supplier_unit_cost;
            return [
                'id'            => $item->id,
                'product_name'  => $item->product->product_name ?? 'Unknown',
                'unit_of_measure' => $item->product->unit_of_measure ?? '',
                'quantity'      => (float) $item->quantity,
                'supplier_name' => $item->supplier->name ?? 'Unassigned',
                'supplier_id'   => $item->supplier_id,
                'is_quoted'     => (int) $item->is_quoted,
                'unit_cost'     => (float) $item->supplier_unit_cost,
                'quoted_price'  => $item->quoted_price ? (float) $item->quoted_price : null,
                'is_paid'       => (int) $item->is_paid,
                'deliveries'    => $item->deliveries->map(function ($d) use ($item) {
                    return [
                        'id'                => $d->id,
                        'quantity'          => (float) $d->quantity,
                        'delivery_date'     => $d->delivery_date?->format('Y-m-d'),
                        'delivery_time'     => $d->delivery_time,
                        'status'            => $d->status,
                        'supplier_confirms' => (bool) $d->supplier_confirms,
                        'is_invoiced'       => !is_null($d->invoice_id),
                        'invoice_id'        => $d->invoice_id,
                        'unit_cost'         => (float) $item->supplier_unit_cost,
                        'delivery_cost'    => (float) $d->delivery_cost,
                    ];
                }),
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => [
                'order_id'   => $order->id,
                'po_number'  => $order->po_number,
                'client'     => $order->client->name ?? '',
                'items'      => $items,
            ],
        ]);
    }

    /**
     * POST /admin/orders/{orderId}/invoice-preview
     *
     * Body: { "delivery_ids": [1, 2, 5, 8] }
     *
     * Returns calculated pricing preview before invoice creation.
     */
    public function preview(Request $request, int $orderId): JsonResponse
    {
        $request->validate([
            'delivery_ids'   => 'required|array|min:1',
            'delivery_ids.*' => 'integer|exists:order_item_deliveries,id',
        ]);

        $order = Orders::findOrFail($orderId);

        try {
            $calculation = $this->pricingService->calculateForDeliveries(
                $order,
                $request->delivery_ids
            );

            return response()->json([
                'success' => true,
                'data'    => $calculation,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * POST /admin/orders/{orderId}/invoices
     *
     * Body: {
     *   "delivery_ids": [1, 2, 5],
     *   "notes": "Partial invoice for first batch",
     *   "due_date": "2026-03-01",
     *   "discount": 50.00
     * }
     */
    public function store(Request $request, int $orderId): JsonResponse
    {
        $request->validate([
            'delivery_ids'   => 'required|array|min:1',
            'delivery_ids.*' => 'integer|exists:order_item_deliveries,id',
            'notes'          => 'nullable|string|max:1000',
            'due_date'       => 'nullable|date|after_or_equal:today',
            'discount'       => 'nullable|numeric|min:0',
        ]);

        $order = Orders::findOrFail($orderId);

        try {
            $invoice = $this->pricingService->createInvoice(
                order: $order,
                deliveryIds: $request->delivery_ids,
                createdBy: auth()->id(),
                notes: $request->notes,
                dueDate: $request->due_date,
                discount: (float) ($request->discount ?? 0),
            );

            return response()->json([
                'success' => true,
                'message' => "Invoice {$invoice->invoice_number} created successfully.",
                'data'    => $this->formatInvoiceResponse($invoice),
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * GET /admin/orders/{orderId}/invoices
     *
     * List all invoices for an order.
     */
    public function index(int $orderId): JsonResponse
    {
        $order = Orders::findOrFail($orderId);

        $invoices = Invoice::where('order_id', $orderId)
            ->with(['items', 'createdBy:id,name'])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($inv) => $this->formatInvoiceResponse($inv));

        return response()->json([
            'success' => true,
            'data'    => $invoices,
        ]);
    }

    /**
     * GET /admin/invoices/{invoiceId}
     *
     * Single invoice detail.
     */
    public function show(int $invoiceId): JsonResponse
    {
        $invoice = Invoice::with([
            'items.orderItem.product:id,product_name,unit_of_measure',
            'items.delivery',
            'order:id,po_number,delivery_address,client_id',
            'order.client:id,name,email',
            'createdBy:id,name',
        ])->findOrFail($invoiceId);

        return response()->json([
            'success' => true,
            'data'    => $this->formatInvoiceDetailResponse($invoice),
        ]);
    }

    /**
     * POST /admin/invoices/{invoiceId}/status
     *
     * Body: { "status": "Sent" }
     */
    public function updateStatus(Request $request, int $invoiceId): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:' . implode(',', Invoice::STATUSES),
        ]);

        $invoice = Invoice::findOrFail($invoiceId);
        $oldStatus = $invoice->status;
        $invoice->update(['status' => $request->status]);

        // If voided or cancelled, release deliveries
        if (in_array($request->status, ['Void', 'Cancelled'])) {
            OrderItemDelivery::where('invoice_id', $invoice->id)
                ->update(['invoice_id' => null]);
        }

        // Log action
        if (class_exists(\App\Models\ActionLog::class)) {
            \App\Models\ActionLog::create([
                'order_id' => $invoice->order_id,
                'user_id'  => auth()->id(),
                'action'   => 'Invoice Status Updated',
                'details'  => "Invoice {$invoice->invoice_number} status changed from {$oldStatus} to {$request->status}",
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => "Invoice status updated to {$request->status}.",
            'data'    => [
                'id'     => $invoice->id,
                'status' => $invoice->status,
            ],
        ]);
    }

    // ── Response Formatters ──

    protected function formatInvoiceResponse(Invoice $invoice): array
    {
        return [
            'id'              => $invoice->id,
            'invoice_number'  => $invoice->invoice_number,
            'order_id'        => $invoice->order_id,
            'client_id'       => $invoice->client_id,
            'subtotal'        => (float) $invoice->subtotal,
            'delivery_total'  => (float) $invoice->delivery_total,
            'gst_tax'         => (float) $invoice->gst_tax,
            'discount'        => (float) $invoice->discount,
            'total_amount'    => (float) $invoice->total_amount,
            'status'          => $invoice->status,
            'issued_date'     => $invoice->issued_date?->format('Y-m-d'),
            'due_date'        => $invoice->due_date?->format('Y-m-d'),
            'notes'           => $invoice->notes,
            'items_count'     => $invoice->items->count(),
            'created_by'      => $invoice->createdBy?->name ?? 'System',
            'created_at'      => $invoice->created_at->toISOString(),
        ];
    }

    protected function formatInvoiceDetailResponse(Invoice $invoice): array
    {
        return [
            'id'              => $invoice->id,
            'invoice_number'  => $invoice->invoice_number,
            'status'          => $invoice->status,
            'issued_date'     => $invoice->issued_date?->format('Y-m-d'),
            'due_date'        => $invoice->due_date?->format('Y-m-d'),
            'notes'           => $invoice->notes,
            'created_by'      => $invoice->createdBy?->name ?? 'System',
            'created_at'      => $invoice->created_at->toISOString(),

            // Order Info
            'order' => [
                'id'               => $invoice->order->id,
                'po_number'        => $invoice->order->po_number,
                'delivery_address' => $invoice->order->delivery_address,
                'client_name'      => $invoice->order->client->name ?? '',
                'client_email'     => $invoice->order->client->email ?? '',
            ],

            // Pricing
            'subtotal'       => (float) $invoice->subtotal,
            'delivery_total' => (float) $invoice->delivery_total,
            'gst_tax'        => (float) $invoice->gst_tax,
            'discount'       => (float) $invoice->discount,
            'total_amount'   => (float) $invoice->total_amount,

            // Line Items
            'items' => $invoice->items->map(function ($item) {
                return [
                    'id'                     => $item->id,
                    'product_name'           => $item->product_name,
                    'unit_of_measure'        => $item->orderItem?->product?->unit_of_measure ?? '',
                    'quantity'               => (float) $item->quantity,
                    'unit_price'             => (float) $item->unit_price,
                    'delivery_cost'          => (float) $item->delivery_cost,
                    'line_total'             => (float) $item->line_total,
                    'delivery_date'          => $item->delivery?->delivery_date?->format('Y-m-d'),
                    'delivery_time'          => $item->delivery?->delivery_time,
                    'delivery_status'        => $item->delivery?->status,
                ];
            }),
        ];
    }
}
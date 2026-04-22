<?php
// FILE PATH: app/Services/InvoicePricingService.php

namespace App\Services;

use App\Models\Orders;
use App\Models\OrderItem;
use App\Models\OrderItemDelivery;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class InvoicePricingService
{
    protected float $GST_RATE    = 0.10; // 10%
    protected float $ADMIN_MARGIN = 0.50; // 50%

    protected XeroService $xeroService;

    public function __construct(XeroService $xeroService)
    {
        $this->xeroService = $xeroService;
    }

    /**
     * Calculate pricing for selected deliveries (preview or creation).
     *
     * Logic:
     * - For each delivery, find its parent OrderItem
     * - Calculate customer unit price for that item:
     *     If is_quoted=1 && quoted_price != null → unit_price = quoted_price / total_quantity
     *     Else → unit_price = ((supplier_unit_cost * quantity - supplier_discount) * (1 + ADMIN_MARGIN)) / quantity
     * - Delivery line cost = unit_price * delivery_quantity
     * - Delivery cost proportional = (delivery_qty / total_item_qty) * item_delivery_cost
     *
     * @param  Orders $order
     * @param  array  $deliveryIds  Array of order_item_delivery IDs
     * @return array  Calculated breakdown
     */
    public function calculateForDeliveries(Orders $order, array $deliveryIds): array
    {
        // Load deliveries with their parent items and product info
        $deliveries = OrderItemDelivery::whereIn('id', $deliveryIds)
            ->where('order_id', $order->id)
            ->with(['orderItem.product'])
            ->get();

        if ($deliveries->isEmpty()) {
            throw new \InvalidArgumentException('No valid deliveries found for this order.');
        }

        // Check if any are already invoiced
        $alreadyInvoiced = $deliveries->whereNotNull('invoice_id');
        if ($alreadyInvoiced->isNotEmpty()) {
            $ids = $alreadyInvoiced->pluck('id')->implode(', ');
            throw new \InvalidArgumentException("Deliveries already invoiced: {$ids}");
        }

        $lineItems     = [];
        $subtotal      = 0.0;
        $deliveryTotal = 0.0;

        // Group deliveries by order_item_id for efficient calculation
        $grouped = $deliveries->groupBy('order_item_id');

        foreach ($grouped as $orderItemId => $itemDeliveries) {
            $orderItem = $itemDeliveries->first()->orderItem;
            $product   = $orderItem->product;

            // Calculate customer unit price for this item
            $unitPrice = $this->calculateCustomerUnitPrice($orderItem);

            foreach ($itemDeliveries as $delivery) {
                $deliveryQty = (float) $delivery->quantity;

                // Line item cost (material)
                $lineMaterialCost = round($unitPrice * $deliveryQty, 2);

                // Delivery cost directly from delivery record
                $lineDeliveryCost = round((float) ($delivery->delivery_cost ?? 0), 2);

                $lineTotal = round($lineMaterialCost + $lineDeliveryCost, 2);

                $lineItems[] = [
                    'order_item_id'          => $orderItem->id,
                    'order_item_delivery_id' => $delivery->id,
                    'product_name'           => $product->product_name ?? 'Unknown Product',
                    'quantity'               => $deliveryQty,
                    'unit_price'             => $unitPrice,
                    'delivery_cost'          => $lineDeliveryCost,
                    'line_total'             => $lineTotal,
                    // Extra info for preview
                    'delivery_date'     => $delivery->delivery_date?->format('Y-m-d'),
                    'delivery_time'     => $delivery->delivery_time,
                    'delivery_status'   => $delivery->status,
                    'supplier_confirms' => $delivery->supplier_confirms,
                ];

                $subtotal      += $lineMaterialCost;
                $deliveryTotal += $lineDeliveryCost;
            }
        }

        $gstTax      = round(($subtotal + $deliveryTotal) * $this->GST_RATE, 2);
        $totalAmount = round($subtotal + $deliveryTotal + $gstTax, 2);

        return [
            'line_items'     => $lineItems,
            'subtotal'       => round($subtotal, 2),
            'delivery_total' => round($deliveryTotal, 2),
            'gst_tax'        => $gstTax,
            'discount'       => 0.00, // Admin can adjust at creation
            'total_amount'   => $totalAmount,
        ];
    }

    /**
     * Calculate customer-facing unit price for an order item.
     * Matches the logic in OrderPricingService.
     */
    protected function calculateCustomerUnitPrice(OrderItem $item): float
    {
        $quantity = (float) $item->quantity;
        if ($quantity <= 0) return 0.0;

        // If quoted, use quoted price / quantity
        if ((int) $item->is_quoted === 1 && $item->quoted_price !== null) {
            return round((float) $item->quoted_price / $quantity, 2);
        }

        // Standard: (supplier_unit_cost * quantity - discount) * (1 + margin) / quantity
        $baseMaterial = ((float) $item->supplier_unit_cost * $quantity) - (float) ($item->supplier_discount ?? 0);
        if ($baseMaterial < 0) $baseMaterial = 0;

        $customerTotal = $baseMaterial * (1 + $this->ADMIN_MARGIN);

        return round($customerTotal / $quantity, 2);
    }

    /**
     * Create invoice from validated delivery selections.
     *
     * Flow:
     *  1. Calculate pricing via calculateForDeliveries()
     *  2. Inside a DB transaction: create Invoice + InvoiceItems + mark deliveries
     *  3. OUTSIDE the transaction: attempt to push invoice to Xero
     *       - If Xero succeeds  → save xero_invoice_id on the invoice record
     *       - If Xero fails     → log the error, return invoice with xero_warning flag
     *       - If Xero not connected → same as fail, return warning
     *
     * The local invoice is ALWAYS created regardless of Xero outcome.
     *
     * @return array ['invoice' => Invoice, 'xero_warning' => string|null]
     */
    public function createInvoice(
        Orders $order,
        array $deliveryIds,
        int $createdBy,
        ?string $notes = null,
        ?string $dueDate = null,
        float $discount = 0.00
    ): array {
        $calculation = $this->calculateForDeliveries($order, $deliveryIds);

        // Apply discount
        $totalAfterDiscount = round($calculation['total_amount'] - $discount, 2);

        // ── Step 1: Create local invoice inside a DB transaction ──
        // The transaction only wraps DB work. Xero push happens after,
        // so a Xero failure cannot accidentally roll back our local data.
        $invoice = DB::transaction(function () use (
            $order, $calculation, $createdBy, $notes, $dueDate, $discount, $totalAfterDiscount, $deliveryIds
        ) {
            // 1. Create invoice
            $invoice = Invoice::create([
                'invoice_number' => Invoice::generateInvoiceNumber(),
                'order_id'       => $order->id,
                'client_id'      => $order->client_id,
                'subtotal'       => $calculation['subtotal'],
                'delivery_total' => $calculation['delivery_total'],
                'gst_tax'        => $calculation['gst_tax'],
                'discount'       => $discount,
                'total_amount'   => $totalAfterDiscount,
                'status'         => 'Paid',
                'issued_date'    => now()->toDateString(),
                'due_date'       => $dueDate ?? now()->addDays(14)->toDateString(),
                'notes'          => $notes,
                'created_by'     => $createdBy,
                // xero_invoice_id intentionally left null — filled after Xero push
            ]);

            // 2. Create invoice line items
            foreach ($calculation['line_items'] as $line) {
                InvoiceItem::create([
                    'invoice_id'             => $invoice->id,
                    'order_item_id'          => $line['order_item_id'],
                    'order_item_delivery_id' => $line['order_item_delivery_id'],
                    'product_name'           => $line['product_name'],
                    'quantity'               => $line['quantity'],
                    'unit_price'             => $line['unit_price'],
                    'delivery_cost'          => $line['delivery_cost'],
                    'line_total'             => $line['line_total'],
                ]);
            }

            // 3. Mark deliveries as invoiced
            OrderItemDelivery::whereIn('id', $deliveryIds)
                ->update(['invoice_id' => $invoice->id]);

            // 4. Log action
            if (class_exists(\App\Models\ActionLog::class)) {
                \App\Models\ActionLog::create([
                    'order_id' => $order->id,
                    'user_id'  => $createdBy,
                    'action'   => 'Invoice Created',
                    'details'  => "Invoice {$invoice->invoice_number} created for " . count($deliveryIds) . " deliveries. Total: \${$invoice->total_amount}",
                ]);
            }

            // Load items relation so pushInvoice() can access them
            return $invoice->load(['items', 'order.client']);
        });

        // ── Step 2: Push to Xero (outside transaction) ──
        // This runs after DB is committed. If it fails, local data is safe.
        $xeroWarning = null;

        try {
            if (!$this->xeroService->isConnected()) {
                // Xero not set up — warn but don't fail
                $xeroWarning = 'Xero is not connected. Invoice saved locally only. Visit /api/xero/authorize to connect.';
            } else {
                // Push to Xero and get back the Xero invoice UUID
                $xeroResult = $this->xeroService->pushInvoice($invoice);

                // Persist the Xero UUID on our invoice record
                $invoice->update([
                    'xero_invoice_id' => $xeroResult['xero_invoice_id'],
                ]);

                $invoice->xero_invoice_id = $xeroResult['xero_invoice_id'];

                // Log success
                if (class_exists(\App\Models\ActionLog::class)) {
                    \App\Models\ActionLog::create([
                        'order_id' => $order->id,
                        'user_id'  => $createdBy,
                        'action'   => 'Xero Invoice Synced',
                        'details'  => "Invoice {$invoice->invoice_number} pushed to Xero. Xero ID: {$xeroResult['xero_invoice_id']}",
                    ]);
                }
            }
        } catch (\Exception $e) {
            // Xero push failed — log it, return warning, local invoice is safe
            Log::error('Xero invoice push failed', [
                'invoice_id'     => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'error'          => $e->getMessage(),
            ]);

            $xeroWarning = 'Invoice created locally, but Xero sync failed: ' . $e->getMessage();
        }

        return [
            'invoice'      => $invoice,
            'xero_warning' => $xeroWarning,
        ];
    }
}
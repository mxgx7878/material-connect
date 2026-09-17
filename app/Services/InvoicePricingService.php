<?php
// FILE PATH: app/Services/InvoicePricingService.php

namespace App\Services;

use App\Models\Orders;
use App\Models\OrderItem;
use App\Models\OrderItemDelivery;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceItemSurcharge;
use App\Models\InvoiceItemTestingFee;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Invoice creation + preview.
 *
 * ALL margin / discount math is delegated to App\Services\PricingService
 * (single source of truth). This class only assembles invoice lines.
 *
 * Material discount handling:
 *  - unit_price on each line = customer unit price BEFORE discount
 *    (supplier_unit_cost × 1.5; quoted → quoted_price / qty).
 *  - material_discount on each line = per-unit discount × delivery quantity
 *    (0 for quoted items) — shown as its own "Material Discount" line on the
 *    invoice and in Xero.
 *  - line_total = material − material_discount + delivery + surcharges + testing.
 */
class InvoicePricingService
{
    protected XeroService $xeroService;
    protected SurchargeCalculatorService $surchargeCalculator;

    public function __construct(
        XeroService $xeroService,
        SurchargeCalculatorService $surchargeCalculator
    ) {
        $this->xeroService         = $xeroService;
        $this->surchargeCalculator = $surchargeCalculator;
    }

    /**
     * Calculate pricing for selected deliveries (preview or creation).
     */
    public function calculateForDeliveries(Orders $order, array $deliveryIds, float $discount = 0.00): array
    {
        $deliveries = OrderItemDelivery::whereIn('id', $deliveryIds)
            ->where('order_id', $order->id)
            ->with(['orderItem.product', 'testingFees.testingFee'])
            ->get();

        if ($deliveries->isEmpty()) {
            throw new \InvalidArgumentException('No valid deliveries found for this order.');
        }

        $alreadyInvoiced = $deliveries->whereNotNull('invoice_id');
        if ($alreadyInvoiced->isNotEmpty()) {
            $ids = $alreadyInvoiced->pluck('id')->implode(', ');
            throw new \InvalidArgumentException("Deliveries already invoiced: {$ids}");
        }

        $lineItems             = [];
        $materialTotal         = 0.0;
        $materialDiscountTotal = 0.0;
        $deliveryTotal         = 0.0;
        $surchargesTotal       = 0.0;
        $testingTotal          = 0.0;

        $grouped = $deliveries->groupBy('order_item_id');

        foreach ($grouped as $orderItemId => $itemDeliveries) {
            $orderItem = $itemDeliveries->first()->orderItem;
            $product   = $orderItem->product;

            // Customer unit price BEFORE material discount (unified service)
            $unitPrice        = PricingService::customerUnitPrice($orderItem);
            $perUnitDiscount  = PricingService::isQuoted($orderItem)
                ? 0.0
                : (float) ($orderItem->supplier_discount ?? 0);

            foreach ($itemDeliveries as $delivery) {
                $deliveryQty          = (float) $delivery->quantity;
                $lineMaterialCost     = round($unitPrice * $deliveryQty, 2);
                $lineMaterialDiscount = round($perUnitDiscount * $deliveryQty, 2);
                $rawDeliveryCost      = (float) ($delivery->delivery_cost ?? 0);
                $lineDeliveryCost     = PricingService::customerDeliveryCost($rawDeliveryCost);

                // --- Surcharges (calculated) ---
                $surchargeResult  = $this->surchargeCalculator->calculateForDelivery($delivery, $orderItem);
                $lineSurcharges   = $surchargeResult['surcharges'];
                $lineSurchargeSum = $surchargeResult['surcharges_total'];

                // --- Testing fees (from DB) — default included=true on preview ---
                $lineTestingFees = $delivery->testingFees->map(fn($tf) => [
                    'testing_fee_id'  => $tf->testing_fee_id,
                    'billing_code'    => $tf->testingFee?->billing_code,
                    'name'            => $tf->testingFee?->name ?? 'Testing Fee',
                    'amount_snapshot' => (float) $tf->amount_snapshot,
                    'included'        => true,
                ])->values()->all();

                $lineTestingSum = round(
                    collect($lineTestingFees)
                        ->where('included', true)
                        ->sum('amount_snapshot'),
                    2
                );

                // Line total = material − material discount + delivery + surcharges + testing
                $lineTotal = round(
                    max($lineMaterialCost - $lineMaterialDiscount, 0)
                    + $lineDeliveryCost + $lineSurchargeSum + $lineTestingSum,
                    2
                );

                $lineItems[] = [
                    'order_item_id'          => $orderItem->id,
                    'order_item_delivery_id' => $delivery->id,
                    'product_name'           => $product->product_name ?? 'Unknown Product',
                    'quantity'               => $deliveryQty,
                    'unit_price'             => $unitPrice,
                    'material_total'         => $lineMaterialCost,
                    'material_discount'      => $lineMaterialDiscount,
                    'delivery_cost'          => $lineDeliveryCost,
                    'surcharges'             => $lineSurcharges,
                    'surcharges_total'       => $lineSurchargeSum,
                    'testing_fees'           => $lineTestingFees,
                    'testing_total'          => $lineTestingSum,
                    'line_total'             => $lineTotal,

                    'delivery_date'     => $delivery->delivery_date?->format('Y-m-d'),
                    'delivery_time'     => $delivery->getRawOriginal('delivery_time'),
                    'delivery_status'   => $delivery->status,
                    'supplier_confirms' => (bool) $delivery->supplier_confirms,
                ];

                $materialTotal         += $lineMaterialCost;
                $materialDiscountTotal += $lineMaterialDiscount;
                $deliveryTotal         += $lineDeliveryCost;
                $surchargesTotal       += $lineSurchargeSum;
                $testingTotal          += $lineTestingSum;
            }
        }

        // Apply the single-source-of-truth totals formula
        $totals = $this->applyTotalsFormula(
            materialTotal:         $materialTotal,
            materialDiscountTotal: $materialDiscountTotal,
            deliveryTotal:         $deliveryTotal,
            surchargesTotal:       $surchargesTotal,
            testingTotal:          $testingTotal,
            backCharges:           0.00,
            credits:               0.00,
            refunds:               0.00,
            discount:              $discount,
        );

        return array_merge(['line_items' => $lineItems], $totals);
    }

    /**
     * Central totals formula — used by both preview and create.
     *
     *   pre_discount_total = material − material_discount + delivery + surcharges + testing
     *   adjustments_total  = back_charges - credits - refunds
     *   taxable_amount     = MAX(pre_discount + adjustments - discount, 0)
     *   gst_tax            = taxable_amount × GST_RATE
     *   total_amount       = taxable_amount + gst_tax
     */
    protected function applyTotalsFormula(
        float $materialTotal,
        float $materialDiscountTotal,
        float $deliveryTotal,
        float $surchargesTotal,
        float $testingTotal,
        float $backCharges = 0.00,
        float $credits     = 0.00,
        float $refunds     = 0.00,
        float $discount    = 0.00
    ): array {
        $preDiscountTotal  = $materialTotal - $materialDiscountTotal
                           + $deliveryTotal + $surchargesTotal + $testingTotal;
        $adjustmentsTotal  = $backCharges - $credits - $refunds;
        $taxableAmount     = max($preDiscountTotal + $adjustmentsTotal - $discount, 0);
        $gstTax            = round($taxableAmount * PricingService::GST_RATE, 2);
        $totalAmount       = round($taxableAmount + $gstTax, 2);

        return [
            'material_total'          => round($materialTotal, 2),
            'material_discount_total' => round($materialDiscountTotal, 2),
            'delivery_total'          => round($deliveryTotal, 2),
            'surcharges_total'        => round($surchargesTotal, 2),
            'testing_total'           => round($testingTotal, 2),
            'back_charges'            => round($backCharges, 2),
            'credits'                 => round($credits, 2),
            'refunds'                 => round($refunds, 2),
            'discount'                => round($discount, 2),
            'gst_tax'                 => $gstTax,
            'total_amount'            => $totalAmount,
        ];
    }

    /**
     * Create invoice from validated delivery selections.
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
        $calculation = $this->calculateForDeliveries($order, $deliveryIds, $discount);

        // ── Step 1: Local persistence inside a DB transaction ──
        $invoice = DB::transaction(function () use (
            $order, $calculation, $createdBy, $notes, $dueDate, $discount, $deliveryIds
        ) {
            $invoice = Invoice::create([
                'invoice_number'          => Invoice::generateInvoiceNumber(),
                'order_id'                => $order->id,
                'client_id'               => $order->client_id,
                'material_total'          => $calculation['material_total'],
                'material_discount_total' => $calculation['material_discount_total'],
                'delivery_total'          => $calculation['delivery_total'],
                'surcharges_total'        => $calculation['surcharges_total'],
                'testing_total'           => $calculation['testing_total'],
                'back_charges'            => $calculation['back_charges'],
                'credits'                 => $calculation['credits'],
                'refunds'                 => $calculation['refunds'],
                'gst_tax'                 => $calculation['gst_tax'],
                'discount'                => $discount,
                'total_amount'            => $calculation['total_amount'],
                'amount_paid'             => 0.00,
                'balance_due'             => $calculation['total_amount'],
                'status'                  => 'Draft',
                'issued_date'             => now()->toDateString(),
                'due_date'                => $dueDate ?? now()->addDays(14)->toDateString(),
                'notes'                   => $notes,
                'created_by'              => $createdBy,
            ]);

            foreach ($calculation['line_items'] as $line) {
                $invoiceItem = InvoiceItem::create([
                    'invoice_id'             => $invoice->id,
                    'order_item_id'          => $line['order_item_id'],
                    'order_item_delivery_id' => $line['order_item_delivery_id'],
                    'product_name'           => $line['product_name'],
                    'quantity'               => $line['quantity'],
                    'unit_price'             => $line['unit_price'],
                    'material_discount'      => $line['material_discount'],
                    'delivery_cost'          => $line['delivery_cost'],
                    'line_total'             => $line['line_total'],
                ]);

                // Snapshot surcharges for this line
                foreach ($line['surcharges'] as $s) {
                    InvoiceItemSurcharge::create([
                        'invoice_item_id'   => $invoiceItem->id,
                        'surcharge_id'      => $s['surcharge_id'] ?? null,
                        'billing_code'      => $s['billing_code'] ?? null,
                        'name'              => $s['name'] ?? 'Surcharge',
                        'amount_snapshot'   => $s['amount_snapshot'] ?? 0,
                        'calculated_amount' => $s['calculated_amount'] ?? 0,
                    ]);
                }

                // Snapshot testing fees for this line
                foreach ($line['testing_fees'] as $tf) {
                    InvoiceItemTestingFee::create([
                        'invoice_item_id' => $invoiceItem->id,
                        'testing_fee_id'  => $tf['testing_fee_id'] ?? null,
                        'billing_code'    => $tf['billing_code'] ?? null,
                        'name'            => $tf['name'] ?? 'Testing Fee',
                        'amount_snapshot' => $tf['amount_snapshot'] ?? 0,
                        'included'        => $tf['included'] ?? true,
                    ]);
                }
            }

            // Link deliveries to the invoice
            OrderItemDelivery::whereIn('id', $deliveryIds)
                ->update(['invoice_id' => $invoice->id]);

            if (class_exists(\App\Models\ActionLog::class)) {
                \App\Models\ActionLog::create([
                    'order_id' => $order->id,
                    'user_id'  => $createdBy,
                    'action'   => 'Invoice Created',
                    'details'  => "Invoice {$invoice->invoice_number} created for " . count($deliveryIds) . " deliveries. Total: \${$invoice->total_amount}",
                ]);
            }

            return $invoice->load(['items.surcharges', 'items.testingFees', 'order.client']);
        });

        // ── Step 2: Xero push (outside transaction) ──
        $xeroWarning = null;

        return [
            'invoice'      => $invoice,
            'xero_warning' => $xeroWarning,
        ];
    }
}

<?php
// FILE PATH: app/Services/PricingService.php

namespace App\Services;

use App\Models\Orders;
use App\Models\OrderItem;

/**
 * ═══════════════════════════════════════════════════════════════════════════
 * UNIFIED PRICING SERVICE — single source of truth for ALL costing math.
 * ═══════════════════════════════════════════════════════════════════════════
 *
 * Every controller / service that needs customer or supplier pricing MUST go
 * through this class (or its SQL expression helpers for listing queries).
 * Do NOT re-implement margin math anywhere else.
 *
 * ── CANONICAL FORMULAS ──────────────────────────────────────────────────────
 *
 *  supplier_discount is stored PER UNIT on order_items.
 *
 *  Material discount (total)  = supplier_discount × quantity
 *  Supplier gross             = supplier_unit_cost × quantity
 *  Supplier net (MC pays)     = supplier_gross − material_discount   (min 0)
 *
 *  Customer item price:
 *      Quoted item  → quoted_price (final; NO margin, NO discount applied)
 *      Standard     → (supplier_unit_cost × 1.5 × qty) − (supplier_discount × qty)
 *                     i.e. 50% margin on the FULL supplier price, THEN the
 *                     material discount is subtracted.  (min 0)
 *
 *      Example: supplier $20/t, discount $5/t, qty 2
 *               → ($20 × 1.5 × 2) − ($5 × 2) = $60 − $10 = $50 ex GST ✓
 *
 *  Customer delivery cost     = delivery_cost × 1.10  (flat 10%, all types)
 *  GST                        = 10% on (customer items + customer delivery)
 *  Order total                = items + delivery + GST − order discount + other charges
 *  Profit                     = (customer items + delivery) − supplier net total
 * ───────────────────────────────────────────────────────────────────────────
 */
class PricingService
{
    public const ITEM_MARGIN     = 0.50; // 50% on FULL supplier price
    public const DELIVERY_MARGIN = 0.10; // flat 10% — all delivery types
    public const GST_RATE        = 0.10; // 10%

    // ════════════════════════════════════════════════════════════════════
    // PER-ITEM CALCULATIONS
    // ════════════════════════════════════════════════════════════════════

    public static function isQuoted(OrderItem $item): bool
    {
        return (int) $item->is_quoted === 1 && $item->quoted_price !== null;
    }

    /** supplier_unit_cost × quantity (before any discount). */
    public static function supplierGross(OrderItem $item): float
    {
        return round((float) ($item->supplier_unit_cost ?? 0) * (float) ($item->quantity ?? 0), 2);
    }

    /** Material discount TOTAL for the item = per-unit discount × quantity. Quoted items: 0. */
    public static function materialDiscount(OrderItem $item): float
    {
        if (self::isQuoted($item)) {
            return 0.0;
        }
        return round((float) ($item->supplier_discount ?? 0) * (float) ($item->quantity ?? 0), 2);
    }

    /** What Material Connect actually pays the supplier for this item. */
    public static function supplierNet(OrderItem $item): float
    {
        return round(max(self::supplierGross($item) - self::materialDiscount($item), 0), 2);
    }

    /**
     * Customer unit price BEFORE material discount.
     *  Quoted   → quoted_price / qty
     *  Standard → supplier_unit_cost × 1.5
     */
    public static function customerUnitPrice(OrderItem $item): float
    {
        $qty = (float) ($item->quantity ?? 0);

        if (self::isQuoted($item)) {
            return $qty > 0 ? round((float) $item->quoted_price / $qty, 2) : 0.0;
        }

        return round((float) ($item->supplier_unit_cost ?? 0) * (1 + self::ITEM_MARGIN), 2);
    }

    /** Customer item total BEFORE material discount (gross). Quoted → quoted_price. */
    public static function customerItemGross(OrderItem $item): float
    {
        if (self::isQuoted($item)) {
            return round((float) $item->quoted_price, 2);
        }

        return round(
            (float) ($item->supplier_unit_cost ?? 0)
            * (float) ($item->quantity ?? 0)
            * (1 + self::ITEM_MARGIN),
            2
        );
    }

    /**
     * FINAL customer item total (what the client is charged for material, ex GST).
     *  Quoted   → quoted_price (discount NOT applied)
     *  Standard → customerItemGross − materialDiscount   (min 0)
     */
    public static function customerItemTotal(OrderItem $item): float
    {
        if (self::isQuoted($item)) {
            return round((float) $item->quoted_price, 2);
        }

        return round(max(self::customerItemGross($item) - self::materialDiscount($item), 0), 2);
    }

    /** Customer-facing delivery cost for a raw supplier delivery cost. Flat 10% margin. */
    public static function customerDeliveryCost(float $rawDeliveryCost): float
    {
        if ($rawDeliveryCost <= 0) {
            return 0.0;
        }
        return round($rawDeliveryCost * (1 + self::DELIVERY_MARGIN), 2);
    }

    /**
     * Full pricing breakdown for one order item — attach this to API payloads
     * so the frontend NEVER recomputes margins.
     *
     * Delivery costs are summed from the item's `deliveries` relation when
     * loaded (source of truth), falling back to order_items.delivery_cost.
     */
    public static function itemBreakdown(OrderItem $item): array
    {
        $rawDelivery = 0.0;
        if ($item->relationLoaded('deliveries') && $item->deliveries->isNotEmpty()) {
            $rawDelivery = (float) $item->deliveries->sum(fn ($d) => (float) ($d->delivery_cost ?? 0));
        } else {
            $rawDelivery = (float) ($item->delivery_cost ?? 0);
        }

        return [
            'is_quoted'               => self::isQuoted($item),
            'quoted_price'            => self::isQuoted($item) ? round((float) $item->quoted_price, 2) : null,

            // Supplier side
            'supplier_unit_cost'      => round((float) ($item->supplier_unit_cost ?? 0), 2),
            'supplier_gross'          => self::supplierGross($item),
            'material_discount_unit'  => round((float) ($item->supplier_discount ?? 0), 2),
            'material_discount'       => self::materialDiscount($item),
            'supplier_net'            => self::supplierNet($item),
            'supplier_delivery_cost'  => round($rawDelivery, 2),

            // Customer side
            'customer_unit_price'     => self::customerUnitPrice($item),
            'customer_item_gross'     => self::customerItemGross($item),
            'customer_item_total'     => self::customerItemTotal($item),
            'customer_delivery_cost'  => self::customerDeliveryCost($rawDelivery),
        ];
    }

    // ════════════════════════════════════════════════════════════════════
    // ORDER-LEVEL TOTALS
    // ════════════════════════════════════════════════════════════════════

    /**
     * Compute complete order totals bottom-up from items (+ their deliveries).
     *
     * @param Orders        $order
     * @param bool          $confirmedOnly  Only include supplier-confirmed items
     *                                      (used by the confirmation rollup).
     */
    public static function orderTotals(Orders $order, bool $confirmedOnly = false): array
    {
        $order->loadMissing(['items.deliveries']);

        $supplierGross        = 0.0;
        $materialDiscount     = 0.0;
        $supplierNet          = 0.0;
        $supplierDelivery     = 0.0;
        $customerItemGross    = 0.0;
        $customerItemTotal    = 0.0;
        $customerDelivery     = 0.0;

        foreach ($order->items as $item) {
            if ($confirmedOnly && !$item->supplier_confirms) {
                continue;
            }

            $b = self::itemBreakdown($item);

            $supplierGross     += $b['supplier_gross'];
            $materialDiscount  += $b['material_discount'];
            $supplierNet       += $b['supplier_net'];
            $supplierDelivery  += $b['supplier_delivery_cost'];
            $customerItemGross += $b['customer_item_gross'];
            $customerItemTotal += $b['customer_item_total'];
            $customerDelivery  += $b['customer_delivery_cost'];
        }

        $customerSubtotal = round($customerItemTotal + $customerDelivery, 2);
        $gst              = round($customerSubtotal * self::GST_RATE, 2);
        $discount         = round((float) ($order->discount ?? 0), 2);       // order-level (invoice-style) discount
        $otherCharges     = round((float) ($order->other_charges ?? 0), 2);
        $totalPrice       = round($customerSubtotal + $gst - $discount + $otherCharges, 2);

        $supplierTotal = round($supplierNet + $supplierDelivery, 2);
        $profit        = round($customerSubtotal - $supplierTotal, 2);
        $marginPct     = $supplierTotal > 0 ? round($profit / $supplierTotal, 4) : 0.0;

        return [
            // Supplier side
            'supplier_item_gross'      => round($supplierGross, 2),
            'material_discount_total'  => round($materialDiscount, 2),
            'supplier_item_cost'       => round($supplierNet, 2),      // NET — what MC pays for materials
            'supplier_delivery_cost'   => round($supplierDelivery, 2),
            'supplier_total'           => $supplierTotal,

            // Customer side
            'customer_item_gross'      => round($customerItemGross, 2),
            'customer_item_cost'       => round($customerItemTotal, 2), // NET of material discount
            'customer_delivery_cost'   => round($customerDelivery, 2),
            'customer_subtotal'        => $customerSubtotal,

            // Totals
            'gst_tax'                  => $gst,
            'discount'                 => $discount,
            'other_charges'            => $otherCharges,
            'total_price'              => $totalPrice,

            // Profit
            'profit_amount'            => $profit,
            'profit_margin_percent'    => $marginPct,
        ];
    }

    /**
     * Recalculate and PERSIST customer/supplier totals onto the order row.
     * Replaces the old OrderPricingService::recalcCustomer + recalcOnConfirmation math.
     */
    public static function recalcAndSave(Orders $order, bool $confirmedOnly = false): Orders
    {
        $t = self::orderTotals($order, $confirmedOnly);

        $order->supplier_item_cost     = $t['supplier_item_cost'];
        $order->supplier_delivery_cost = $t['supplier_delivery_cost'];
        $order->customer_item_cost     = $t['customer_item_cost'];
        $order->customer_delivery_cost = $t['customer_delivery_cost'];
        $order->gst_tax                = $t['gst_tax'];
        $order->total_price            = $t['total_price'];
        $order->profit_amount          = $t['profit_amount'];
        $order->profit_margin_percent  = $t['profit_margin_percent'];
        $order->save();

        return $order;
    }

    // ════════════════════════════════════════════════════════════════════
    // SQL EXPRESSIONS — for listing subqueries (must mirror the PHP math)
    // ════════════════════════════════════════════════════════════════════
    //
    // NOTE: quoted-price override is intentionally omitted from raw SQL
    // (legacy `is_qouted` column-name issue) — same behaviour as before.
    // Listing figures for quoted items are approximate; detail views use
    // the exact PHP math above.

    /** SUM of customer item cost: (unit × qty × 1.5) − (discount_per_unit × qty), floored at 0. */
    public static function sqlCustomerItemCost(): string
    {
        $m = 1 + self::ITEM_MARGIN;
        return "COALESCE(SUM(GREATEST(supplier_unit_cost * quantity * {$m} - COALESCE(supplier_discount, 0) * quantity, 0)), 0)";
    }

    /** SUM of material discount totals (per-unit discount × qty). */
    public static function sqlMaterialDiscountTotal(): string
    {
        return "COALESCE(SUM(COALESCE(supplier_discount, 0) * quantity), 0)";
    }

    /** SUM of supplier gross (unit × qty, before discount). */
    public static function sqlSupplierItemGross(): string
    {
        return "COALESCE(SUM(supplier_unit_cost * quantity), 0)";
    }

    /** SUM of customer delivery cost with flat 10% margin (order_item_deliveries join). */
    public static function sqlCustomerDeliveryCost(): string
    {
        $m = 1 + self::DELIVERY_MARGIN;
        return "COALESCE(SUM(order_item_deliveries.delivery_cost * {$m}), 0)";
    }

    /** SUM of raw supplier delivery cost (order_item_deliveries join). */
    public static function sqlSupplierDeliveryCost(): string
    {
        return "COALESCE(SUM(order_item_deliveries.delivery_cost), 0)";
    }
}

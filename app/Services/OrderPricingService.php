<?php
// FILE PATH: app/Services/OrderPricingService.php

namespace App\Services;

use App\Models\Orders;

/**
 * Thin wrapper kept for backwards compatibility with existing call sites.
 * ALL pricing math now lives in App\Services\PricingService (single source
 * of truth). Do not add formulas here.
 */
class OrderPricingService
{
    /**
     * Recalculate customer-facing pricing (all items).
     * Signature preserved; $adminMargin / $gstRate overrides are no longer
     * supported — margins are centralised in PricingService.
     */
    public static function recalcCustomer(Orders $order, ?float $adminMargin = null, ?float $gstRate = null, bool $save = true): Orders
    {
        if ($save) {
            return PricingService::recalcAndSave($order, false);
        }

        // Non-persisting path: compute and hydrate without saving.
        $t = PricingService::orderTotals($order, false);
        $order->customer_item_cost     = $t['customer_item_cost'];
        $order->customer_delivery_cost = $t['customer_delivery_cost'];
        $order->gst_tax                = $t['gst_tax'];
        $order->total_price            = $t['total_price'];
        $order->profit_amount          = $t['profit_amount'];
        $order->profit_margin_percent  = $t['profit_margin_percent'];

        return $order;
    }
}

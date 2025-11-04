<?php

namespace App\Services;

use App\Models\Orders;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class OrderPricingService
{
    /**
     * Recalculate customer-facing pricing when admin edits item price/discount/quoted price.
     * Supplier totals remain unchanged.
     *
     * @param  Orders $order  Loaded order with items relation (or will lazy-load).
     * @param  float|null $adminMargin  Override admin margin (default 0.50).
     * @param  float|null $gstRate      Override GST rate (default 0.05).
     * @param  bool $save               Persist changes.
     * @return Orders
     */
    public static function recalcCustomer(Orders $order, ?float $adminMargin = null, ?float $gstRate = null, bool $save = true): Orders
    {
        $ADMIN_MARGIN = $adminMargin ?? 0.50; // 50%
        $GST_RATE     = $gstRate     ?? 0.05; // 5%

        // Ensure items loaded
        $order->loadMissing(['items:id,order_id,quantity,supplier_unit_cost,supplier_discount,is_quoted,quoted_price,delivery_type,delivery_cost']);

        // 1) Rebuild customer_item_cost from items only
        $customer_item_cost = 0.0;
        $allItems = OrderItem::where('order_id',$order->id)->get();
        foreach ($allItems as $item) {
        
            if ((int)$item->is_quoted === 1 && $item->quoted_price !== null) {
  
                $customer_item_cost += (float)$item->quoted_price;
                continue;
            }
           
            $base_material = ($item->supplier_unit_cost * $item->quantity) - $item->supplier_discount;
            if ($base_material < 0) { $base_material = 0; }
     
            $customer_item_cost += $base_material * (1 + $ADMIN_MARGIN);
        }
     

        // 2) Keep existing customer_delivery_cost and supplier totals unchanged
        $customer_delivery_cost = (float) ($order->customer_delivery_cost ?? 0);
        $supplier_item_cost     = (float) ($order->supplier_item_cost ?? 0);
        $supplier_delivery_cost = (float) ($order->supplier_delivery_cost ?? 0);

        // 3) Recompute GST and totals
        $gst_tax = ($customer_item_cost + $customer_delivery_cost) * $GST_RATE;

        $discount      = (float) ($order->discount ?? 0);
        $other_charges = (float) ($order->other_charges ?? 0);

        $total_price = $customer_item_cost
                     + $customer_delivery_cost
                     + $gst_tax
                     - $discount
                     + $other_charges;

        // 4) Profit metrics based on frozen supplier totals
        $supplier_total        = $supplier_item_cost + $supplier_delivery_cost;
        $profit_before_tax     = $total_price - $supplier_total - $gst_tax - $discount - $other_charges;
        $profit_margin_percent = $supplier_total > 0 ? ($profit_before_tax / $supplier_total)*100 : 0.0;


        // 5) Persist
        $order->customer_item_cost     = round($customer_item_cost, 2);
        // customer_delivery_cost unchanged
        $order->gst_tax                = round($gst_tax, 2);
        $order->total_price            = round($total_price, 2);
        $order->profit_margin_percent  = $profit_margin_percent;
        $order->profit_amount          = round($profit_before_tax, 2); // actual profit amount

        if ($save) {
            DB::transaction(function () use ($order) {
                $order->save();
            });
        }

        return $order;
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Orders;
use App\Models\OrderItem;
use App\Models\MasterProducts;
use App\Models\SupplierOffers;
use App\Models\Projects;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    /**
     * Create a new order with multiple products.
     */
    public function createOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'project_id' => 'required|exists:projects,id',
            'delivery_method' => 'required|string|max:255',
            'delivery_address' => 'required|string|max:255',
            'delivery_date' => 'required|date',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:master_products,id',
            'products.*.quantity' => 'required|numeric|min:1',
            'products.*.custom_mix_blend' => 'nullable|string|max:255', // now per product
            'discount' => 'nullable|numeric|min:0|max:100',
            'repeat_order' => 'nullable|boolean',
            'margin' => 'nullable|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $margin = $request->margin ?? 50;

        // Save the order with initial prices as 0
        $order = Orders::create([
            'client_id' => Auth::id(),
            'project_id' => $request->project_id,
            'delivery_method' => $request->delivery_method,
            'delivery_address' => $request->delivery_address,
            'delivery_date' => $request->delivery_date,
            'subtotal' => 0,
            'tax' => 0,
            'fuel_levy' => 0,
            'total_price' => 0,
            'margin' => $margin,
            'supplier_cost' => 0,
            'customer_cost' => 0,
            'discount' => $request->discount,
            'repeat_order' => $request->repeat_order ?? false,
        ]);

        // Save products into order_items table
        foreach ($request->products as $productData) {
            $order->items()->create([
                'product_id' => $productData['product_id'],
                'quantity' => $productData['quantity'],
                'custom_mix_blend' => $productData['custom_mix_blend'] ?? null,
                'supplier_id' => null, // supplier will be assigned later
                'price' => null,       // price will be calculated later
            ]);
        }

        return response()->json(['message' => 'Order created successfully', 'order' => $order], 201);
    }

}

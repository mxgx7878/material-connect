<?php

namespace App\Http\Controllers;

use App\Models\Order;
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
            'client_id' => 'required|exists:users,id',
            'project_id' => 'required|exists:projects,id',  // Validate project_id
            'delivery_method' => 'required|string|max:255',
            'delivery_address' => 'required|string|max:255',
            'delivery_date' => 'required|date',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:master_products,id',
            'products.*.quantity' => 'required|numeric|min:1',
            'products.*.supplier_id' => 'nullable|exists:users,id',
            'products.*.price' => 'required|numeric|min:0', // Price of the product at the time of the order
            'discount' => 'nullable|numeric|min:0|max:100',
            'repeat_order' => 'nullable|boolean',
            'custom_mix_blend' => 'nullable|string|max:255',
            'margin' => 'nullable|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Initialize order totals
        $subtotal = 0;
        $tax = 0;
        $fuelLevy = 0;
        $totalPrice = 0;
        $margin = $request->margin ?? 50; // Default margin is 50%

        // Calculate prices for each product
        foreach ($request->products as $productData) {
            $product = MasterProducts::find($productData['product_id']);
            $supplierOffer = SupplierOffers::where('supplier_id', $productData['supplier_id'])
                                           ->where('master_product_id', $productData['product_id'])
                                           ->first();

            if (!$supplierOffer) {
                return response()->json(['error' => 'Selected product is not available from the chosen supplier'], 404);
            }

            // Calculate the product totals
            $productSubtotal = $supplierOffer->price * $productData['quantity'];
            $productTax = $productSubtotal * 0.1; // Assuming 10% tax rate
            $productFuelLevy = $productSubtotal * 0.05; // Assuming 5% fuel levy

            // Accumulate totals
            $subtotal += $productSubtotal;
            $tax += $productTax;
            $fuelLevy += $productFuelLevy;
        }

        // Calculate the final total price
        $totalPrice = $subtotal + $tax + $fuelLevy;

        // Apply discount if provided
        if ($request->discount) {
            $totalPrice -= ($totalPrice * ($request->discount / 100));
        }

        // Calculate supplier cost and customer cost
        $supplierCost = $subtotal / (1 + ($margin / 100));
        $customerCost = $subtotal - $supplierCost;

        // Save the order
        $order = Order::create([
            'client_id' => $request->client_id,
            'project_id' => $request->project_id,  // Store project_id
            'delivery_method' => $request->delivery_method,
            'delivery_address' => $request->delivery_address,
            'delivery_date' => $request->delivery_date,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'fuel_levy' => $fuelLevy,
            'total_price' => $totalPrice,
            'margin' => $margin,
            'supplier_cost' => $supplierCost,
            'customer_cost' => $customerCost,
            'discount' => $request->discount,
            'repeat_order' => $request->repeat_order ?? false,
            'custom_mix_blend' => $request->custom_mix_blend,
        ]);

        // Attach the products to the order
        foreach ($request->products as $productData) {
            $order->items()->create([
                'product_id' => $productData['product_id'],
                'quantity' => $productData['quantity'],
                'supplier_id' => $productData['supplier_id'],
                'price' => $productData['price'],
            ]);
        }

        return response()->json(['message' => 'Order created successfully', 'order' => $order], 201);
    }
}

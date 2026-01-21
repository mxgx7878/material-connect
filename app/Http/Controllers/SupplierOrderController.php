<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Orders;
use App\Models\OrderItem;
use App\Models\MasterProducts;
use App\Models\SupplierOffers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\ActionLog;
use Illuminate\Support\Facades\Validator;

class SupplierOrderController extends Controller
{
    // Helper function | Workflow 
    private function workflow(Orders $order)
    {
        $currentWorkflow = $order->workflow;

        switch ($currentWorkflow) {
            case 'Supplier Assigned':
                $allConfirmed = $order->items()->where('supplier_confirms', false)->count() === 0;
                if (!$allConfirmed) {
                    break;
                }

                // Constants
                $ADMIN_MARGIN    = 0.50;
                $DELIVERY_MARGIN = 0.10;
                $FLEET_MARGIN    = 0.15;
                $GST_RATE        = 0.10;

                // Accumulators
                $customer_item_cost     = 0.0;
                $customer_delivery_cost = 0.0;
                $supplier_item_cost     = 0.0;
                $supplier_delivery_cost = 0.0;

                foreach ($order->items as $item) {
                    // Supplier material cost
                    $base_material_cost = ($item->supplier_unit_cost * $item->quantity) - $item->supplier_discount;
                    if ($base_material_cost < 0) { $base_material_cost = 0; }
                    $supplier_item_cost += $base_material_cost;

                    // Customer item cost with admin margin or quoted override
                    if ((int)$item->is_quoted === 1 && $item->quoted_price !== null) {
                        $customer_item_cost += (float)$item->quoted_price;
                    } else {
                        $customer_item_cost += $base_material_cost * (1 + $ADMIN_MARGIN);
                    }

                    // Delivery handling
                    $dtype = (string)$item->delivery_type;
                    if ($dtype === 'Supplier' || $dtype === 'ThirdParty') {
                        $customer_delivery_cost += $item->delivery_cost * (1 + $DELIVERY_MARGIN);
                        $supplier_delivery_cost += $item->delivery_cost;
                    } elseif ($dtype === 'Fleet') {
                        $customer_delivery_cost += $item->delivery_cost * (1 + $FLEET_MARGIN);
                        $supplier_delivery_cost += $item->delivery_cost;
                    } elseif ($dtype === 'Included' || $dtype === 'None' || $dtype === '' || $dtype === null) {
                        // No delivery cost
                    } else {
                        // Unknown type: supplier bears base delivery
                        $supplier_delivery_cost += (float)$item->delivery_cost;
                    }
                }

                // GST on customer-facing costs
                $gst_tax = ($customer_item_cost + $customer_delivery_cost) * $GST_RATE;

                // Totals
                $discount      = (float)($order->discount ?? 0);
                $other_charges = (float)($order->other_charges ?? 0);

                $total_price = $customer_item_cost
                            + $customer_delivery_cost
                            + $gst_tax
                            - $discount
                            + $other_charges;

                // Profit metrics
                $supplier_total    = $supplier_item_cost + $supplier_delivery_cost;
                $profit_before_tax = $total_price - $supplier_total - $gst_tax;
                $profit_margin_percent = $supplier_total > 0 ? ($profit_before_tax / $supplier_total) : 0.0;

                // Save to order
                $order->customer_item_cost     = round($customer_item_cost, 2);
                $order->customer_delivery_cost = round($customer_delivery_cost, 2);
                $order->supplier_item_cost     = round($supplier_item_cost, 2);
                $order->supplier_delivery_cost = round($supplier_delivery_cost, 2);
                $order->gst_tax                = round($gst_tax, 2);
                $order->total_price            = round($total_price, 2);

                // Actual profit amount (not percentage)
                $order->profit_amount          = round($profit_before_tax, 2);
                $order->profit_margin_percent  = round($profit_margin_percent,2);

                $order->workflow = 'Payment Requested';
                // dd($customer_item_cost, $customer_delivery_cost, $supplier_item_cost, $supplier_delivery_cost, $profit_margin_percent, $profit_before_tax);
                $order->save();

                break;
        }
    }


    // public function getSupplierOrders(Request $request)
    // {
    //     $user = Auth::user();

    //     // Pagination and filtering parameters
    //     $perPage = (int) $request->get('per_page', 10);
    //     $search = trim((string) $request->get('search', ''));
    //     $confirmed = $request->get('supplier_confirms');
    //     $deliveryDate = $request->get('supplier_delivery_date');
    //     $productId = $request->get('product_id');
    //     $details = $request->boolean('details', false);

    //     // Base query for supplier's order items - reusable for all metrics
    //     $baseQuery = function () use ($user, $search, $confirmed, $deliveryDate, $productId) {
    //         $query = OrderItem::where('supplier_id', $user->id);

    //         // Apply search filter (by product name)
    //         if (!empty($search)) {
    //             $query->whereHas('product', function ($q) use ($search) {
    //                 $q->where('product_name', 'like', '%' . $search . '%');
    //             });
    //         }

    //         // Apply confirmed filter
    //         if ($confirmed !== null) {
    //             $query->where('supplier_confirms', filter_var($confirmed, FILTER_VALIDATE_BOOLEAN));
    //         }

    //         // Apply delivery date filter
    //         if (!empty($deliveryDate)) {
    //             $query->whereDate('supplier_delivery_date', $deliveryDate);
    //         }

    //         // Apply product ID filter
    //         if (!empty($productId)) {
    //             $query->where('product_id', $productId);
    //         }

    //         return $query;
    //     };

    //     // Get metrics with applied filters
    //     $totalOrdersCount = $baseQuery()->count();
    //     $supplierConfirmedCount = $baseQuery()->where('supplier_confirms', true)->count();
    //     $awaitingPaymentCount = $baseQuery()->where('is_paid', false)->count();
    //     $deliveredCount = $baseQuery()->whereHas('order', function ($query) {
    //         $query->where('workflow', 'Delivered');
    //     })->count();

    //     // Get paginated results using the same base query
    //     $orderItemsQuery = $baseQuery()->with(['order', 'product', 'chosenOffer']);
    //     $orderItems = $orderItemsQuery->orderBy('created_at', 'desc')->paginate($perPage);

    //     // Prepare response data
    //     $data = [
    //         'data' => $orderItems->items(),
    //         'pagination' => [
    //             'current_page' => $orderItems->currentPage(),
    //             'per_page' => $orderItems->perPage(),
    //             'total' => $orderItems->total(),
    //             'last_page' => $orderItems->lastPage(),
    //         ],
    //         'metrics' => [
    //             'total_orders_count' => $totalOrdersCount,
    //             'supplier_confirmed_count' => $supplierConfirmedCount,
    //             'awaiting_payment_count' => $awaitingPaymentCount,
    //             'delivered_count' => $deliveredCount,
    //         ]
    //     ];

    //     // Add filters data if details=true
    //     if ($details) {
    //         // Get products that this supplier offers
    //         $supplierProducts = MasterProducts::whereHas('supplierOffers', function ($query) use ($user) {
    //             $query->where('supplier_id', $user->id);
    //         })
    //         ->select('id', 'product_name', 'photo')
    //         ->get()
    //         ->map(function ($product) {
    //             return [
    //                 'id' => $product->id,
    //                 'product_name' => $product->product_name,
    //                 'photo' => $product->photo,
    //             ];
    //         });

    //         $data['filters'] = [
    //             'products' => $supplierProducts
    //         ];
    //     }

    //     return response()->json([
    //         'success' => true,
    //         'data' => $data
    //     ]);
    // }
    public function getSupplierOrders(Request $request)
    {
        $user = Auth::user();

        // Pagination and filtering parameters
        $perPage = (int) $request->get('per_page', 10);
        $search = trim((string) $request->get('search', ''));
        $confirmed = $request->get('supplier_confirms');
        $deliveryDate = $request->get('supplier_delivery_date');
        $productId = $request->get('product_id');
        $details = $request->boolean('details', false);
        // dd($user);
        // Base query for orders that have items assigned to this supplier
        $baseQuery = function () use ($user, $search, $confirmed, $deliveryDate, $productId) {
            $query = Orders::whereHas('items', function ($q) use ($user, $search, $confirmed, $deliveryDate, $productId) {
                $q->where('supplier_id', $user->id);

                // Apply search filter (by product name)
                if (!empty($search)) {
                    $q->whereHas('product', function ($productQuery) use ($search) {
                        $productQuery->where('product_name', 'like', '%' . $search . '%');
                    });
                }

                // Apply confirmed filter
                if ($confirmed !== null) {
                    $q->where('supplier_confirms', filter_var($confirmed, FILTER_VALIDATE_BOOLEAN));
                }

                // Apply delivery date filter
                if (!empty($deliveryDate)) {
                    $q->whereDate('supplier_delivery_date', $deliveryDate);
                }

                // Apply product ID filter
                if (!empty($productId)) {
                    $q->where('product_id', $productId);
                }
            });

            return $query;
        };

        // Get metrics with applied filters
        $totalOrdersCount = $baseQuery()->count();
        
        $supplierConfirmedCount = Orders::whereHas('items', function ($q) use ($user, $search, $confirmed, $deliveryDate, $productId) {
            $q->where('supplier_id', $user->id)
            ->where('supplier_confirms', true);

            // Apply the same filters for consistency
            if (!empty($search)) {
                $q->whereHas('product', function ($productQuery) use ($search) {
                    $productQuery->where('product_name', 'like', '%' . $search . '%');
                });
            }
            if (!empty($deliveryDate)) {
                $q->whereDate('supplier_delivery_date', $deliveryDate);
            }
            if (!empty($productId)) {
                $q->where('product_id', $productId);
            }
        })->count();
        
        // $awaitingPaymentCount = Orders::whereHas('items', function ($q) use ($user, $search, $confirmed, $deliveryDate, $productId) {
        //     $q->where('supplier_id', $user->id)
        //     ->where('is_paid', false);
        $awaitingPaymentCount = Orders::where(function ($orderQuery) use ($user) {
        $orderQuery->whereNull('supplier_paid_ids')
                ->orWhereJsonDoesntContain('supplier_paid_ids', $user->id); // User ID not in paid list
        })
        ->whereHas('items', function ($q) use ($user, $search, $confirmed, $deliveryDate, $productId) {
            $q->where('supplier_id', $user->id);

            // Optional filters
            if (!empty($search)) {
                $q->whereHas('product', function ($productQuery) use ($search) {
                    $productQuery->where('product_name', 'like', '%' . $search . '%');
                });
            }

            if (!empty($deliveryDate)) {
                $q->whereDate('supplier_delivery_date', $deliveryDate);
            }

            if (!empty($productId)) {
                $q->where('product_id', $productId);
            }
        })->count();


        
        $deliveredCount = $baseQuery()->where('workflow', 'Delivered')->count();

        // Get paginated results with supplier's items count and relationship
        $ordersQuery = $baseQuery()->with(['items' => function ($query) use ($user) {
            $query->where('supplier_id', $user->id)
                ->with(['product', 'chosenOffer']);
        }]);

        $orders = $ordersQuery->orderBy('created_at', 'desc')->paginate($perPage);

        // Transform the response to include items count for this supplier
        $transformedOrders = $orders->getCollection()->map(function ($order) use ($user) {
            $supplierItems = $order->items->where('supplier_id', $user->id);
            $totalAmount = 0;
            foreach ($supplierItems as $item) {
                $itemCost = ($item->supplier_unit_cost * $item->quantity) - $item->supplier_discount + $item->supplier_delivery_cost;
                $totalAmount += $itemCost;
            }

            return [
                'id' => $order->id,
                'po_number' => $order->po_number,          // was 'order_number'
                'order_status' => $order->order_status,    // show status to suppliers
                // 'workflow' => $order->workflow,         // remove from supplier view
                'total_amount' => $totalAmount,
                'created_at' => $order->created_at,
                'updated_at' => $order->updated_at,
                'supplier_items_count' => $supplierItems->count(),
                'supplier_items' => $supplierItems->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'quantity' => $item->quantity,
                        'supplier_unit_cost' => $item->supplier_unit_cost,
                        // 'supplier_delivery_cost' => $item->supplier_delivery_cost,
                        'delivery_cost' => $item->delivery_cost ?? null,  // keep if present in model
                        'delivery_type' => $item->delivery_type ?? null,  // keep if present in model
                        'supplier_discount' => $item->supplier_discount,
                        'supplier_delivery_date' => $item->supplier_delivery_date,
                        'supplier_confirms' => $item->supplier_confirms,
                        'supplier_notes' => $item->supplier_notes ?? null,
                        'product' => $item->product,
                        'chosen_offer' => $item->chosenOffer,
                    ];
                }),
            ];
        });

        // Prepare response data
        $data = [
            'data' => $transformedOrders,
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
                'last_page' => $orders->lastPage(),
            ],
            'metrics' => [
                'total_orders_count' => $totalOrdersCount,
                'supplier_confirmed_count' => $supplierConfirmedCount,
                'awaiting_payment_count' => $awaitingPaymentCount,
                'delivered_count' => $deliveredCount,
            ]
        ];

        // Add filters data if details=true
        if ($details) {
            // Get products that this supplier offers
            $supplierProducts = MasterProducts::whereHas('supplierOffers', function ($query) use ($user) {
                $query->where('supplier_id', $user->id);
            })
            ->select('id', 'product_name', 'photo')
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'product_name' => $product->product_name,
                    'photo' => $product->photo,
                ];
            });

            $data['filters'] = [
                'products' => $supplierProducts
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function updateOrderPricing(Request $request, OrderItem $orderItem)
    {
        $v = Validator::make($request->all(), [
            'supplier_unit_cost' => 'sometimes|required|numeric|min:0',
            'supplier_discount' => 'sometimes|required|numeric|min:0',
            'supplier_delivery_date' => 'sometimes|required|date',
            'supplier_confirms' => 'sometimes|required|boolean',
            'delivery_cost' => 'sometimes|required|numeric|min:0',
            'delivery_type'=> 'required|in:Included,Supplier,ThirdParty,Fleet,None ',
            'supplier_notes' => 'sometimes|nullable|string|max:500'
        ]);

        if ($v->fails()) {
            return response()->json(['error' => $v->errors()], 422);
        }

        // Check if the authenticated user is the supplier for this order item
        $user = Auth::user();

        if(!in_array($orderItem->order->workflow, ['Supplier Assigned', 'Requested'])) {  //, 'Payment Requested', 'On Hold', 'Delivered'
            return response()->json([
                'message' => 'Cannot update order item now as the order is in '.$orderItem->order->workflow.' status'
            ], 403);
        }   
        if ($orderItem->supplier_id !== $user->id) {
            return response()->json([
                'message' => 'You are not authorized to update this order item'
            ], 403);
        }

        // Check if order item is already confirmed
        if ($orderItem->supplier_confirms && $request->has('supplier_confirms') && !$request->supplier_confirms) {
            return response()->json([
                'message' => 'Cannot unconfirm an already confirmed order item'
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Update the order item with provided fields
            $updateData = [];
            
            if ($request->has('supplier_unit_cost')) {
                $updateData['supplier_unit_cost'] = $request->supplier_unit_cost;
            }
            
            if ($request->has('delivery_cost')) {
                $updateData['delivery_cost'] = $request->delivery_cost;
            }
            
            if ($request->has('supplier_discount')) {
                $updateData['supplier_discount'] = $request->supplier_discount;
            }

            if ($request->has('delivery_type')) {
                $updateData['delivery_type'] = $request->delivery_type;
            }
            
            if ($request->has('supplier_delivery_date')) {
                $updateData['supplier_delivery_date'] = $request->supplier_delivery_date;
            }
            
            if ($request->has('supplier_confirms')) {
                $updateData['supplier_confirms'] = $request->supplier_confirms;
            }
            
            if ($request->has('supplier_notes')) {
                $updateData['supplier_notes'] = $request->supplier_notes;
            }

            // Update the order item
            $orderItem->update($updateData);

            // If supplier confirmed the order, trigger workflow check
            if ($request->has('supplier_confirms') && $request->supplier_confirms) {
                $this->workflow($orderItem->order);
            }

            DB::commit();

            // Reload the order item with relationships
            $orderItem->load(['order', 'product', 'chosenOffer']);
            ActionLog::create([
                'action' => 'Order Item Pricing Updated By Supplier',
                'details' => "Supplier ID {$user->id} updated pricing for Order Item ID {$orderItem->id} in Order ID {$orderItem->order_id}",
                'order_id' => $orderItem->order_id,
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Order item updated successfully',
                'data' => $orderItem
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Failed to update order item: ' . $e->getMessage()
            ], 500);
        }
    }

    public function viewOrderDetails(Orders $order)
    {
        $user = Auth::user();

        // Ensure the supplier has items in this order
        $supplierItems = $order->items()->where('supplier_id', $user->id)->with(['product', 'chosenOffer'])->get();

        if ($supplierItems->isEmpty()) {
            return response()->json([
                'error' => 'You do not have any items in this order'
            ], 403);
        }

        // Reload the order with only selected fields and relationships
        $order = Orders::where('id', $order->id)
            ->select([
                'id',
                'po_number', // Make sure this field exists in your table
                'project_id',
                'client_id',
                'workflow',
                'delivery_address',
                'delivery_date',
                'delivery_time',
                'delivery_window',
                'delivery_method',
                'load_size',
                'special_equipment',
                'special_notes',
                'created_at',
                'updated_at'
            ])
            ->with(['client:id,name,email', 'project:id,name,site_contact_name,site_contact_phone,site_instructions'])
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'order' => $order,
                'supplier_items' => $supplierItems
            ]
        ]);
    }



    
}

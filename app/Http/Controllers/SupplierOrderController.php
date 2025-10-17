<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Orders;
use App\Models\OrderItem;
use App\Models\MasterProducts;
use App\Models\SupplierOffers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
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
                if ($allConfirmed) {
                    // Calculate pricing
                    $subtotal = 0;
                    $supplierCost = 0;
                    $adminMarginPercentage = 0.50; // 50% admin margin
                    $deliveryCost = 0;
                    $fuelLevy = 0;
                    $itemCostWithMargin= 0;
                    $adminMarginAmount = 0;
                    
                    foreach ($order->items as $item) {
                        // Calculate item cost excluding delivery
                        $itemCost = ($item->supplier_unit_cost * $item->quantity) - $item->supplier_discount;
                        
                        // Add admin margin to item cost (50% on top of item cost)
                        $itemCostWithMargin = $itemCost + ($itemCost * $adminMarginPercentage);
                        
                        $subtotal += $itemCostWithMargin;
                        $supplierCost += $itemCost; // Supplier cost without admin margin
                        $deliveryCost += $item->supplier_delivery_cost;
                    }
                   
                    
                    // Calculate fuel levy (10% on delivery cost as per your example)
                    $fuelLevy = $deliveryCost + ($deliveryCost * 0.10);
                    //  dd($subtotal,$fuelLevy);
                    
                    // Calculate GST (10% on subtotal)
                    $gstTax = $subtotal * 0.10;
                    
                    // Calculate total price
                    $totalPrice = $subtotal + $gstTax + $fuelLevy - $order->discount + $order->other_charges;
                    
                    // Calculate actual admin margin amount (for tracking)
                    $adminMarginAmount = $subtotal * $adminMarginPercentage;

                    // Update order pricing fields
                    $order->subtotal = $subtotal;
                    $order->fuel_levy = $fuelLevy;
                    $order->gst_tax = $gstTax;
                    $order->total_price = $totalPrice;
                    $order->supplier_cost = $supplierCost + $deliveryCost; // Total supplier cost including delivery
                    $order->customer_cost = $totalPrice;
                    $order->admin_margin = $adminMarginAmount; // The actual margin amount
                    $order->workflow = 'Payment Requested';
                    $order->save();
                }
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
        
        $awaitingPaymentCount = Orders::whereHas('items', function ($q) use ($user, $search, $confirmed, $deliveryDate, $productId) {
            $q->where('supplier_id', $user->id)
            ->where('is_paid', false);

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
            $totalAmount=0;
            foreach($supplierItems as $item){
                $itemCost = ($item->supplier_unit_cost * $item->quantity) - $item->supplier_discount;
                $totalAmount += $itemCost;
            }
            
            return [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'workflow' => $order->workflow,
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
                        'supplier_delivery_cost' => $item->supplier_delivery_cost,
                        'supplier_discount' => $item->supplier_discount,
                        'supplier_delivery_date' => $item->supplier_delivery_date,
                        'supplier_confirms' => $item->supplier_confirms,
                        'is_paid' => $item->is_paid,
                        'supplier_notes' => $item->supplier_notes,
                        'product' => $item->product,
                        'chosen_offer' => $item->chosenOffer,
                    ];
                }),
                // Include other order fields you need
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
            'supplier_notes' => 'sometimes|nullable|string|max:500'
        ]);

        if ($v->fails()) {
            return response()->json(['error' => $v->errors()], 422);
        }

        // Check if the authenticated user is the supplier for this order item
        $user = Auth::user();
        // dd($orderItem->order->workflow);
        if(!in_array($orderItem->order->workflow, ['Supplier Assigned', 'Supplier Missing', 'Requested'])) {
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
            
            if ($request->has('supplier_delivery_cost')) {
                $updateData['supplier_delivery_cost'] = $request->supplier_delivery_cost;
            }
            
            if ($request->has('supplier_discount')) {
                $updateData['supplier_discount'] = $request->supplier_discount;
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

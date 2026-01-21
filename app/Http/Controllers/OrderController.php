<?php

namespace App\Http\Controllers;

use App\Models\Orders;
use App\Models\MasterProducts;
use App\Models\SupplierOffers;
use App\Models\OrderItem;
use App\Models\Projects;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\ActionLog;

class OrderController extends Controller
{
    /**
     * Create a new order with multiple products.
     */
    public function createOrder(Request $request)
    {
        $v = Validator::make($request->all(), [
            'po_number'        => 'nullable|unique:orders,po_number|string|max:50',
            'project_id'       => 'required|exists:projects,id',
            'delivery_address' => 'required|string',
            'delivery_lat'     => 'required|numeric',
            'delivery_long'    => 'required|numeric',
            'delivery_date'    => 'required|date',
            'delivery_time'    => 'nullable|date_format:H:i',
            // 'delivery_method'  => 'in:Other,Tipper,Agitator,Pump,Ute',
            'load_size'        => 'nullable|string|max:50',
            'special_equipment'=> 'nullable|string|max:255',
            'items'            => 'required|array|min:1',
            'items.*.product_id'       => 'required|exists:master_products,id',
            'items.*.quantity'         => 'required|numeric|min:0.01',
            'items.*.custom_blend_mix' => 'nullable|string',
            'repeat_order'     => 'nullable|boolean',
            'special_notes'    => 'nullable|string|max:1000',
        ]);

        if ($v->fails()) {
            return response()->json(['error' => $v->errors()], 422);
        }

        $user = Auth::user();

        return DB::transaction(function () use ($request, $user) {
            // 1) Create order
            /** @var Orders $order */
            $order = Orders::create([
                'po_number'        => $request->po_number,
                'client_id'        => $user->id,
                'project_id'       => $request->project_id,
                'delivery_address' => $request->delivery_address,
                'delivery_lat'     => $request->delivery_lat,
                'delivery_long'    => $request->delivery_long,
                'delivery_date'    => $request->delivery_date,
                'delivery_time'    => $request->delivery_time,
                // 'delivery_method'  => $request->delivery_method,
                'load_size'        => $request->load_size,
                'special_equipment'=> $request->special_equipment,
                // monetary fields start at zero; you’ll roll them up later
                'other_charges'    => 0,
                'gst_tax'          => 0,
                'discount'         => 0,
                'total_price'      => 0,
                'supplier_item_cost'    => 0,
                'customer_item_cost'    => 0,
                'customer_delivery_cost'    => 0,
                'supplier_delivery_cost'    => 0,
                // initial states per spec
                'payment_status'   => 'Pending',
                'order_status'     => 'Draft',
                'order_process'    => 'Automated',
                'generate_invoice' => 0,
                'repeat_order'     => $request->repeat_order ? $request->repeat_order : 0,
                'special_notes'    => $request->special_notes ?? null,
                // add 'workflow' column in your table if not present yet
            ]);

            $lat = (float) $order->delivery_lat;
            $lng = (float) $order->delivery_long;

            // 2) Preload candidate offers for all requested product_ids
            $productIds = collect($request->items)->pluck('product_id')->unique()->values();
            // dd($productIds);
            $offers = SupplierOffers::with(['supplier:id,delivery_zones'])
                ->whereIn('master_product_id', $productIds)   // adjust column name if different
                ->where('status', 'Approved')                     // only approved offers
                ->whereIn('availability_status', ['In Stock', 'Limited'])                       // optional filter if you have it
                ->get()
                ->groupBy('master_product_id');
            $anyMissingSupplier = false;
            // dd($offers);
            // 3) Create items with nearest in-zone supplier if available
            foreach ($request->items as $row) {
                $pid   = (int) $row['product_id'];
                $qty   = (float) $row['quantity'];
                $blend = $row['custom_blend_mix'] ?? null;

                [$chosenOffer, $distanceKm] = $this->pickNearestOfferInZone(
                    $offers->get($pid) ?? collect(), $lat, $lng
                );

                if ($chosenOffer) {
                    $order->items()->create([
                        'product_id'             => $pid,
                        'quantity'               => $qty,
                        'supplier_id'            => $chosenOffer->supplier_id,
                        'custom_blend_mix'       => $blend,
                        'supplier_unit_cost'     => (float) ($chosenOffer->unit_cost ?? $chosenOffer->price ?? 0),
                        'supplier_delivery_cost' => (float) ($chosenOffer->delivery_cost ?? 0),
                        'supplier_delivery_date' => $order->delivery_date,
                        'choosen_offer_id'      => $chosenOffer->id,
                        'supplier_confirms'      => 0,
                    ]);
                } else {
                    $anyMissingSupplier = true;

                    $order->items()->create([
                        'product_id'             => $pid,
                        'quantity'               => $qty,
                        'supplier_id'            => null,
                        'custom_blend_mix'       => $blend,
                        'supplier_unit_cost'     => 0,
                        'supplier_delivery_cost' => 0,
                        'supplier_delivery_date' => $order->delivery_date,
                        'supplier_confirms'      => 0,
                    ]);
                }
            }

            // 4) Update workflow + order_process if any item unassigned
            if ($anyMissingSupplier) {
                $order->workflow      = 'Supplier Missing';
                $order->order_process = 'Action Required';
                $order->save();
            } else {
                // all items assigned to a supplier
                $order->workflow      = 'Supplier Assigned';
                $order->order_process = 'Automated';
                $order->save();
                // $this->workflow($order);
            }
            ActionLog::create([
                'action' => 'Order Created',
                'details' => "Order ID {$order->id} created by Client {$user->contact_name}",
                'order_id' => $order->id,
                'user_id' => $user->id,
            ]);

            // Optionally eager-load for response
            $order->load(['items.product','items.supplier']);

            return response()->json([
                'message' => 'Order created',
                'order'   => $order
            ], 201);
        });
    }

    /** Repeat Order */
    public function repeatOrder(Request $request, Orders $order)
    {
        // Step 1: Validate the request data
        $v = Validator::make($request->all(), [
            'delivery_date'    => 'required|date',
            'items'            => 'required|array|min:1',
            'items.*.product_id'       => 'required|exists:master_products,id',
            'items.*.quantity'         => 'required|numeric|min:0.01',
            'special_notes'    => 'nullable|string|max:1000',
        ]);

        if ($v->fails()) {
            return response()->json(['error' => $v->errors()], 422);
        }

        $user = Auth::user();

        return DB::transaction(function () use ($request, $user, $order) {
            // Step 2: Create a new order (replicating the original order)
            $newOrder = $order->replicate();
            $newOrder->client_id = $user->id;
            $newOrder->delivery_date = $request->delivery_date;  // New delivery date
            $newOrder->payment_status = 'Pending';
            $newOrder->order_process = 'Automated';
            $newOrder->order_status = 'Draft';  // Default order status
            $newOrder->repeat_order = 1;  // Mark as repeat order
            $newOrder->special_notes = $request->special_notes ?? null;
            $newOrder->other_charges    = 0;
            $newOrder->gst_tax          = 0;
            $newOrder->discount         = 0;
            $newOrder->total_price      = 0;
            $newOrder->supplier_item_cost    = 0;
            $newOrder->customer_item_cost    = 0;
            $newOrder->customer_delivery_cost    = 0;
            $newOrder->supplier_delivery_cost    = 0;
            $newOrder->save();

            $lat = (float) $newOrder->delivery_lat;
            $lng = (float) $newOrder->delivery_long;

            // Step 3: Preload candidate offers for all requested product_ids
            $productIds = collect($request->items)->pluck('product_id')->unique()->values();
            $offers = SupplierOffers::with(['supplier:id,delivery_zones'])
                ->whereIn('master_product_id', $productIds)
                ->where('status', 'Approved')
                ->whereIn('availability_status', ['In Stock', 'Limited'])
                ->get()
                ->groupBy('master_product_id');

            $anyMissingSupplier = false;

            // Step 4: Create items for the new order with nearest available supplier
            foreach ($request->items as $row) {
                $pid   = (int) $row['product_id'];
                $qty   = (float) $row['quantity'];
                $blend = $row['custom_blend_mix'] ?? null;

                // Pick nearest available supplier
                [$chosenOffer, $distanceKm] = $this->pickNearestOfferInZone(
                    $offers->get($pid) ?? collect(), $lat, $lng
                );

                if ($chosenOffer) {
                    // If supplier found, create the order item with supplier data
                    $newOrder->items()->create([
                        'product_id'             => $pid,
                        'quantity'               => $qty,
                        'supplier_id'            => $chosenOffer->supplier_id,
                        'custom_blend_mix'       => $blend,
                        'supplier_unit_cost'     => (float) ($chosenOffer->unit_cost ?? $chosenOffer->price ?? 0),
                        'supplier_delivery_cost' => (float) ($chosenOffer->delivery_cost ?? 0),
                        'supplier_delivery_date' => $newOrder->delivery_date,
                        'choosen_offer_id'      => $chosenOffer->id,
                        'supplier_confirms'      => 0,
                    ]);
                } else {
                    // If no supplier is available, mark as missing supplier
                    $anyMissingSupplier = true;

                    $newOrder->items()->create([
                        'product_id'             => $pid,
                        'quantity'               => $qty,
                        'supplier_id'            => null,
                        'custom_blend_mix'       => $blend,
                        'supplier_unit_cost'     => 0,
                        'supplier_delivery_cost' => 0,
                        'supplier_delivery_date' => $newOrder->delivery_date,
                        'supplier_confirms'      => 0,
                    ]);
                }
            }

            // Step 5: Update the order workflow based on supplier assignment
            if ($anyMissingSupplier) {
                $newOrder->workflow = 'Supplier Missing';
                $newOrder->order_process = 'Action Required';
            } else {
                $newOrder->workflow = 'Supplier Assigned';
                $newOrder->order_process = 'Automated';
            }

            $newOrder->save();

            // Optionally eager-load for response
            $newOrder->load(['items.product', 'items.supplier']);

            return response()->json([
                'message' => 'Order repeated successfully',
                'order'   => $newOrder
            ], 201);
        });
    }


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


    /**
     * Pick nearest offer whose supplier has a delivery zone covering the point.
     * Zones format expected on users.delivery_zones: JSON array of
     * [{ "lat": <float>, "long": <float>, "radius": <km as number>, "address": "..." }, ...]
     * Returns [SupplierOffers|null, float distanceKm]
     */
    private function pickNearestOfferInZone($offersForProduct, float $lat, float $lng): array
    {
        // dd( $offersForProduct, $lat, $lng);
        $bestOffer = null;
        $bestDist  = PHP_FLOAT_MAX;

        foreach ($offersForProduct as $offer) {
            $supplier = $offer->supplier;
            if (!$supplier || empty($supplier->delivery_zones)) {
                continue;
            }

            $zones = is_string($supplier->delivery_zones)
                ? json_decode($supplier->delivery_zones, true)
                : $supplier->delivery_zones;
            // dd($zones);

            if (!is_array($zones)) {
                continue;
            }

            foreach ($zones as $z) {
                if (!isset($z['lat'], $z['long'], $z['radius'])) {
                    continue;
                }
                $distKm = $this->haversineKm($lat, $lng, (float)$z['lat'], (float)$z['long']);
                
                if ($distKm <= (float)$z['radius']) {
                    // dd($distKm, $bestDist);
                    // inside this zone
                    if ($distKm < $bestDist) {
                        $bestDist  = $distKm;
                        $bestOffer = $offer;
                    }
                }
            }
        }

        return [$bestOffer, $bestOffer ? $bestDist : INF];
    }

    private function haversineKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $R = 6371.0088; // mean Earth radius in km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat/2) * sin($dLat/2)
           + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
           * sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        return $R * $c;
    }


    public function getClientProducts(Request $request)
    {
        // $user    = Auth::user();
        $perPage = (int) $request->integer('per_page', 10);
        $page    = (int) $request->integer('page', 1);
        $perPage = 1000;
        // aggregate approved, available offers per product (scoped to this client's suppliers)
        $offersAgg = SupplierOffers::query()
            ->select('supplier_offers.master_product_id')
            ->selectRaw('MIN(supplier_offers.price) AS price_min')
            ->selectRaw('MAX(supplier_offers.price) AS price_max')
            ->join('users as suppliers', 'suppliers.id', '=', 'supplier_offers.supplier_id')
            // ->where('suppliers.client_id', $user->id)
            ->where('supplier_offers.status', 'Approved')
            ->whereIn('supplier_offers.availability_status', ['In Stock', 'Limited'])
            ->groupBy('supplier_offers.master_product_id');

        $query = MasterProducts::query()
            ->joinSub($offersAgg, 'oa', function ($join) {
                $join->on('oa.master_product_id', '=', 'master_products.id');
            })
            ->select('master_products.*', 'oa.price_min', 'oa.price_max');

        // search by product_name
        if ($search = trim((string) $request->get('search'))) {
            $query->where('master_products.product_name', 'like', "%{$search}%");
        }

        // filter by category_id
        if ($request->filled('category')) {
            $query->where('master_products.category', $request->get('category'));
        }

        // sort
        if ($request->get('sort') === 'price') {
            $query->orderBy('oa.price_min', 'asc');
        } else {
            $query->orderBy('master_products.product_name', 'asc');
        }
        $query->whereNotIn('master_products.product_type', ['pavers','paver','bricks','brick','blocks','block']);

        $products = $query->with('category')->paginate($perPage, ['*'], 'page', $page);

        $products->getCollection()->transform(function ($p) {
            $p->price = ($p->price_min == $p->price_max)
                ? sprintf('$%.2f', $p->price_min)
                : sprintf('$%.2f - $%.2f', $p->price_min, $p->price_max);
            return $p;
        });

        return response()->json([
            'data' => $products->items(),
            'meta' => [
                'current_page' => $products->currentPage(),
                'per_page'     => $products->perPage(),
                'total'        => $products->total(),
                'last_page'    => $products->lastPage(),
            ],
        ]);
    }

    public function getClientProductDetails($id)
    {
        // dd($id);
        // $user = Auth::user();

        $product = MasterProducts::with('category')->find($id);
        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        // fetch approved, available offers for this product (scoped to this client's suppliers)
        $offers = SupplierOffers::with(['supplier:id,company_name,delivery_zones'])
            ->where('master_product_id', $product->id)
            // ->whereHas('supplier', function ($q) use ($user) {
            //     $q->where('client_id', $user->id);
            // })
            ->where('status', 'Approved')
            ->whereIn('availability_status', ['In Stock', 'Limited'])
            ->orderBy('price', 'asc')
            ->get();

        return response()->json($product);
    }

    //Get my orders
    // public function getMyOrders(Request $request)
    // {
    //     $user = Auth::user();

    //     $details = filter_var($request->get('details', false), FILTER_VALIDATE_BOOLEAN);

    //     $perPage = (int) $request->get('per_page', 10);
    //     $search  = trim((string) $request->get('search', ''));
    //     $sort    = $request->get('sort', 'created_at');
    //     $dir     = strtolower($request->get('dir', 'desc')) === 'asc' ? 'asc' : 'desc';
    //     $delivery_date = $request->get('delivery_date');
    //     $project_id    = $request->get('project_id');
    //     $workflow      = $request->get('workflow');
    //     $order_status = $request->get('order_status');
    //     $repeat_order   = null;
    //     if($request->has('repeat_order')) {
    //         $repeat_order = $request->get('repeat_order');
    //         if($repeat_order === 'true' || $repeat_order === '1') {
    //             $repeat_order = true;
    //         } elseif($repeat_order === 'false' || $repeat_order === '0') {
    //             $repeat_order = false;
    //         } else {
    //             $repeat_order = null; // invalid value, ignore filter
    //         }
    //     } else {
    //         $repeat_order = null;
    //     }

    //     $query = Orders::with(['project','items.product','items.supplier'])
    //         ->where('client_id', $user->id);

    //     if ($search !== '')        $query->where('po_number', 'like', "%{$search}%");
    //     if ($delivery_date)        $query->whereDate('delivery_date', $delivery_date);
    //     if ($project_id)           $query->where('project_id', $project_id);
    //     if ($workflow)             $query->where('workflow', $workflow);
    //     if ($repeat_order !== null) $query->where('repeat_order', (bool)$repeat_order);
    //     if ($order_status)         $query->where('order_status',$order_status);
    //     // dd($repeat_order);
    //     $allowedSorts = ['po_number','delivery_date','created_at','updated_at'];
    //     if (!in_array($sort, $allowedSorts, true)) $sort = 'created_at';

    //     $orders = $query->orderBy($sort, $dir)->paginate($perPage);

    //     // enrich each order with order_info
    //     $enriched = collect($orders->items())->map(function (Orders $o) {
    //         $missing = $o->items->whereNull('supplier_id');
    //         if ($o->workflow === 'Supplier Missing') {
    //             $missingNames = $missing->map(fn($it) => optional($it->product)->product_name)
    //                                     ->filter()->unique()->values()->all();
    //             $o->order_info = 'Supplier missing for: ' . implode(', ', $missingNames);
    //         } elseif ($o->workflow === 'Supplier Assigned') {
    //             $o->order_info = 'Waiting for suppliers to confirm';
    //         } elseif ($o->workflow === 'Payment Requested') {
    //             $o->order_info = 'Awaiting your payment';
    //         } else {
    //             $o->order_info = null;
    //         }
    //         return $o;
    //     })->values()->all();

    //     $base = Orders::where('client_id', $user->id);
    //     $metrics = [
    //         'total_orders_count'      => (clone $base)->count(),
    //         'supplier_missing_count'  => (clone $base)->where('workflow', 'Supplier Missing')->count(),
    //         'supplier_assigned_count' => (clone $base)->where('workflow', 'Supplier Assigned')->count(),
    //         'awaiting_payment_count'  => (clone $base)->where('workflow', 'Payment Requested')->count(),
    //         'delivered_count'         => (clone $base)->where('workflow', 'Delivered')->count(),
    //     ];

    //     $response = [
    //         'data' => $enriched,
    //         'pagination' => [
    //             'per_page' => $orders->perPage(),
    //             'current_page' => $orders->currentPage(),
    //             'total_pages' => $orders->lastPage(),
    //             'total_items' => $orders->total(),
    //             'has_more_pages' => $orders->hasMorePages(),
    //         ],
    //         'metrics' => $metrics,
    //     ];

    //     if ($details) {
    //         $projects = Projects::where('added_by', $user->id)
    //             ->orderBy('created_at','desc')
    //             ->get(['id','name']);
    //         $response['projects'] = $projects;
    //     }

    //     return response()->json($response);
    // }
    public function getMyOrders(Request $request)
    {
        $user = Auth::user();

        $details = filter_var($request->get('details', false), FILTER_VALIDATE_BOOLEAN);

        $perPage = (int) $request->get('per_page', 10);
        $search  = trim((string) $request->get('search', ''));
        $sort    = $request->get('sort', 'created_at');
        $dir     = strtolower($request->get('dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        $delivery_date = $request->get('delivery_date');
        $project_id    = $request->get('project_id');
        $order_status  = $request->get('order_status');
        $payment_status = $request->get('payment_status');
        $delivery_method = $request->get('delivery_method');
        
        $repeat_order   = null;
        if($request->has('repeat_order')) {
            $repeat_order = $request->get('repeat_order');
            if($repeat_order === 'true' || $repeat_order === '1') {
                $repeat_order = true;
            } elseif($repeat_order === 'false' || $repeat_order === '0') {
                $repeat_order = false;
            } else {
                $repeat_order = null;
            }
        }

        $query = Orders::with(['project','items.product'])
            ->where('client_id', $user->id)->where('is_archived', false);

        if ($search !== '')         $query->where('po_number', 'like', "%{$search}%");
        if ($delivery_date)         $query->whereDate('delivery_date', $delivery_date);
        if ($project_id)            $query->where('project_id', $project_id);
        if ($order_status)          $query->where('order_status', $order_status);
        if ($payment_status)        $query->where('payment_status', $payment_status);
        if ($delivery_method)       $query->where('delivery_method', $delivery_method);
        if ($repeat_order !== null) $query->where('repeat_order', (bool)$repeat_order);

        $allowedSorts = ['po_number','delivery_date','total_price','created_at','updated_at'];
        if (!in_array($sort, $allowedSorts, true)) $sort = 'created_at';

        $orders = $query->orderBy($sort, $dir)->paginate($perPage);

        $enriched = collect($orders->items())->map(function (Orders $o) {
            if ($o->order_status === 'Draft') {
                $o->order_info = 'Order draft created';
            } elseif ($o->order_status === 'Confirmed') {
                $o->order_info = 'Order confirmed, awaiting schedule';
            } elseif ($o->order_status === 'Scheduled') {
                $o->order_info = 'Order scheduled for delivery';
            } elseif ($o->order_status === 'In Transit') {
                $o->order_info = 'Order in transit';
            } elseif ($o->order_status === 'Delivered') {
                $o->order_info = 'Order delivered';
            } elseif ($o->order_status === 'Completed') {
                $o->order_info = 'Order completed';
            } elseif ($o->order_status === 'Cancelled') {
                $o->order_info = 'Order cancelled';
            } elseif ($o->payment_status === 'Unpaid' || $o->payment_status === 'Requested') {
                $o->order_info = 'Payment required';
            } else {
                $o->order_info = null;
            }
            return $o;
        })->values()->all();

        $base = Orders::where('client_id', $user->id);
        $metrics = [
            'total_orders_count'   => (clone $base)->count(),
            'draft_count'          => (clone $base)->where('order_status', 'Draft')->count(),
            'confirmed_count'      => (clone $base)->where('order_status', 'Confirmed')->count(),
            'scheduled_count'      => (clone $base)->where('order_status', 'Scheduled')->count(),
            'in_transit_count'     => (clone $base)->where('order_status', 'In Transit')->count(),
            'delivered_count'      => (clone $base)->where('order_status', 'Delivered')->count(),
            'completed_count'      => (clone $base)->where('order_status', 'Completed')->count(),
            'cancelled_count'      => (clone $base)->where('order_status', 'Cancelled')->count(),
        ];

        $response = [
            'data' => $enriched,
            'pagination' => [
                'per_page' => $orders->perPage(),
                'current_page' => $orders->currentPage(),
                'total_pages' => $orders->lastPage(),
                'total_items' => $orders->total(),
                'has_more_pages' => $orders->hasMorePages(),
            ],
            'metrics' => $metrics,
        ];

        if ($details) {
            $projects = Projects::where('added_by', $user->id)
                ->orderBy('created_at','desc')
                ->get(['id','name']);
            
            $response['projects'] = $projects;
            $response['order_statuses'] = ['Draft', 'Confirmed', 'Scheduled', 'In Transit', 'Delivered', 'Completed', 'Cancelled'];
            $response['payment_statuses'] = ['Unpaid', 'Partially Paid', 'Paid'];
            $response['delivery_methods'] = ['Tipper', 'Agitator', 'Pump', 'Ute', 'Other'];
        }

        return response()->json($response);
    }


    /**
     * Client view. If workflow = Supplier Missing, include eligible suppliers per missing item.
     */
    public function viewMyOrder(Orders $order)
    {
        abort_unless($order->client_id === Auth::id(), 403);

        $order->load(['project','items.product','items.supplier']);

        $missing = $order->items->whereNull('supplier_id');
            if ($order->workflow === 'Supplier Missing') {
                $missingNames = $missing->map(fn($it) => optional($it->product)->product_name)
                                        ->filter()->unique()->values()->all();
                $order->order_info = 'Supplier missing for: ' . implode(', ', $missingNames);
            } elseif ($order->workflow === 'Supplier Assigned') {
                $order->order_info = 'Waiting for suppliers to confirm';
            } elseif ($order->workflow === 'Payment Requested') {
                $order->order_info = 'Awaiting your payment';
            } else {
                $order->order_info = null;
            }

        $orderData = $order->only([
            'id','po_number','project_id','client_id','workflow','delivery_address','order_status',
            'delivery_date','delivery_time','delivery_method','repeat_order','customer_item_cost','payment_status','customer_delivery_cost','discount','other_charges','gst_tax','total_price','reason','created_at','updated_at'
        ]);
        
        $order->items->each(function (OrderItem $item) use ($order) {
            $item->supplier_unit_cost = (((float) $item->supplier_unit_cost - (float)$item->supplier_discount)/2) + (float) $item->supplier_unit_cost ;
        });

        $orderData['project'] = optional($order->project)?->only(['id','name','site_contact_name','site_contact_phone','site_instructions']);
        $orderData['order_info'] = $order->order_info;
        return response()->json([
            'success' => true,
            'data' => [
                'order' => $orderData,
                'items' => $order->items,
            ],
        ]);
    }


    /**
     * Assign a supplier to a specific order item and advance workflow if all assigned.
     * Body: { "supplier_id": <int> }
     */
    public function assignSupplier(Request $request, OrderItem $item)
    {
        $v = Validator::make($request->all(), ['supplier_id' => 'required|exists:users,id']);
        if ($v->fails()) return response()->json(['error'=>$v->errors()], 422);

        $order = $item->order()->with('items')->first();
        abort_unless($order && $order->client_id === Auth::id(), 403);

        $supplierId = (int) $request->supplier_id;

        $chosenOffer = SupplierOffers::with('supplier:id,delivery_zones')
            ->where('supplier_id', $supplierId)
            ->where('master_product_id', $item->product_id)
            ->where('status', 'Approved')
            ->whereIn('availability_status', ['In Stock','Limited'])
            ->whereHas('supplier', fn($q) => $q->whereJsonLength('delivery_zones','>=',1))
            ->first();

        if (!$chosenOffer) {
            return response()->json(['error' => 'No valid offer with delivery zones for this product'], 422);
        }

        $item->update([
            'supplier_id'             => $chosenOffer->supplier_id,
            'choosen_offer_id'        => $chosenOffer->id,
            'supplier_unit_cost'      => (float) ($chosenOffer->unit_cost ?? $chosenOffer->price ?? 0),
            'supplier_delivery_cost'  => (float) ($chosenOffer->delivery_cost ?? 0),
            'supplier_delivery_date'  => $order->delivery_date,
            'supplier_confirms'       => false,
        ]);

        if ($order->items()->whereNull('supplier_id')->count() === 0) {
            $order->update(['workflow' => 'Supplier Assigned', 'order_process' => 'Automated']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Supplier assigned',
            'order_workflow' => $order->workflow,
            'item' => $item->fresh(['product','supplier','chosenOffer']),
        ]);
    }



    /**
     * Mark or unmark an order as repeat order.
     */    
    public function markRepeatOrder(Orders $order, Request $request)
    {
        abort_unless($order->client_id === Auth::id(), 403);
        $order->repeat_order = true;
        $order->save();
        return response()->json([
            'success' => true,
            'message' => 'Order marked as repeat order',
            'order'   => $order->only(['id','repeat_order']),
        ]); 
    }

    
    public function reorderFromProject(Request $request)
    {
        $v = Validator::make($request->all(), [
            'project_id'       => 'required|exists:projects,id',
            'product_id'       => 'required|exists:master_products,id',
            'order_item_id'    => 'required|exists:order_items,id', // NEW
            'quantity'         => 'required|numeric|min:0.01',
            'po_number'        => 'nullable|unique:orders,po_number|string|max:50',
            'delivery_date'    => 'required|date',
            'delivery_time'    => 'nullable|date_format:H:i',
            'delivery_method'  => 'nullable|in:Other,Tipper,Agitator,Pump,Ute',
            'special_notes'    => 'nullable|string|max:1000',
            'custom_blend_mix' => 'nullable|string',
        ]);

        if ($v->fails()) {
            return response()->json(['error' => $v->errors()], 422);
        }

        $user = Auth::user();

        // Verify project belongs to user
        $project = Projects::where('id', $request->project_id)
            ->where('added_by', $user->id)
            ->first();

        if (!$project) {
            return response()->json(['error' => 'Project not found or unauthorized'], 404);
        }

        // Find template item and ensure it belongs to this client's project
        /** @var \App\Models\OrderItem|null $templateItem */
        $templateItem = OrderItem::where('id', $request->order_item_id)
            ->whereHas('order', function ($q) use ($user, $project) {
                $q->where('client_id', $user->id)
                ->where('project_id', $project->id);
            })
            ->first();

        if (!$templateItem) {
            return response()->json(['error' => 'Order item not found for this project'], 404);
        }

        return DB::transaction(function () use ($request, $user, $project, $templateItem) {
            // 1) Create order
            /** @var Orders $order */
            $order = Orders::create([
                'po_number'              => $request->po_number,
                'client_id'              => $user->id,
                'project_id'             => $request->project_id,
                'delivery_address'       => $project->delivery_address,
                'delivery_lat'           => $project->delivery_lat,
                'delivery_long'          => $project->delivery_long,
                'delivery_date'          => $request->delivery_date,
                'delivery_time'          => $request->delivery_time,
                'delivery_method'        => $request->delivery_method ?? 'Other',
                'load_size'              => null,
                'special_equipment'      => null,
                'other_charges'          => 0,
                'gst_tax'                => 0,
                'discount'               => 0,
                'total_price'            => 0,
                'supplier_item_cost'     => 0,
                'customer_item_cost'     => 0,
                'customer_delivery_cost' => 0,
                'supplier_delivery_cost' => 0,
                'payment_status'         => 'Pending',
                'order_status'           => 'Draft',
                'order_process'          => 'Automated',
                'generate_invoice'       => 0,
                'repeat_order'           => 1,
                'special_notes'          => $request->special_notes,
            ]);

            // 2) Create order item by COPYING pricing from template item
            $qty = (float) $request->quantity;

            $order->items()->create([
                'product_id'             => $request->product_id, // should match $templateItem->product_id
                'quantity'               => $qty,

                // copy supplier + offer
                'supplier_id'            => $templateItem->supplier_id,
                'choosen_offer_id'       => $templateItem->choosen_offer_id,

                // copy pricing
                'supplier_unit_cost'     => $templateItem->supplier_unit_cost,
                'supplier_delivery_cost' => $templateItem->supplier_delivery_cost,
                'supplier_discount'      => $templateItem->supplier_discount,

                // quoted logic (if used in your system)
                'is_quoted'              => $templateItem->is_quoted ?? $templateItem->is_qouted,
                'quoted_price'           => $templateItem->quoted_price,

                // copy other attributes
                'custom_blend_mix'       => $request->custom_blend_mix ?? $templateItem->custom_blend_mix,
                'delivery_type'          => $templateItem->delivery_type,
                'delivery_cost'          => $templateItem->delivery_cost,
                'supplier_delivery_date' => $order->delivery_date,

                // IMPORTANT: reset confirmation + payment
                'supplier_confirms'      => 0,
                'is_paid'                => 0,
            ]);

            // 3) Workflow based on whether supplier exists
            if ($templateItem->supplier_id) {
                $order->workflow      = 'Supplier Assigned';
                $order->order_process = 'Automated';
            } else {
                $order->workflow      = 'Supplier Missing';
                $order->order_process = 'Action Required';
            }

            $order->save();
            ActionLog::create([
                'action' => 'Reorder from Project',
                'details' => "Client {$user->contact_name} reordered items from Project ID {$project->id}",
                'order_id' => null, // No specific order for this action
                'user_id' => Auth::id(),
            ]);

            // If you want to recalc totals using your pricing service:
            // OrderPricingService::recalcCustomer($order, null, null, true);

            $order->load(['items.product', 'items.supplier']);

            return response()->json([
                'message' => 'Order created successfully from project item',
                'order'   => $order,
            ], 201);
        });
    }

    public function setOrderStatus(Orders $order, Request $request)
    {

        $v = Validator::make($request->all(), [
            'order_status' => 'required|in:Cancelled',
        ]);

        if ($v->fails()) {
            return response()->json(['error' => $v->errors()], 422);
        }

        $order->order_status = $request->order_status;
        $order->save();

        //Action Log
        ActionLog::create([
            'action' => 'Order Status Updated',
            'details' => "Client ".Auth::user()->contact_name." updated order #{$order->id} status to {$request->order_status}",
            'order_id' => $order->id,
            'user_id' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Order status updated',
            'order'   => $order->only(['id','order_status']),
        ]);
    }

    public function archiveOrder(Orders $order)
    {
        if($order->client_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $order->is_archived = 1;
        $order->archived_by = Auth::id();
        $order->save();

        //Action Log
        ActionLog::create([
            'action' => 'Order Archived',
            'details' => "Client ".Auth::user()->contact_name." archived order #{$order->id}",
            'order_id' => $order->id,
            'user_id' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Order archived',
            'order'   => $order->only(['id','is_archived']),
        ]);
    }
        

}



<?php

namespace App\Http\Controllers;

use App\Models\Orders;
use App\Models\MasterProducts;
use App\Models\SupplierOffers;
use App\Models\OrderItem;
use App\Models\OrderItemDelivery;
use App\Models\Projects;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\ActionLog;
use App\Models\Invoice;
use App\Models\User;
use Carbon\Carbon;

class OrderController extends Controller
{
    /**
     * Create a new order with multiple products.
     */
    public function createOrder(Request $request)
    {
        //  dd('createOrder endpoint hit');
        $v = Validator::make($request->all(), [
            'po_number'        => 'nullable|unique:orders,po_number|string|max:50',
            'project_id'       => 'required|exists:projects,id',
            'delivery_address' => 'required|string',
            'delivery_lat'     => 'required|numeric',
            'delivery_long'    => 'required|numeric',

            // order-level date is optional now (because slots define real schedule)
            'delivery_date'    => 'nullable|date',

            'load_size'        => 'nullable|string|max:50',
            'special_equipment'=> 'nullable|string|max:255',

            'contact_person_name'   => 'required|string|max:100',
            'contact_person_number' => 'required|string|max:30',

            'repeat_order'     => 'nullable|boolean',
            'special_notes'    => 'nullable|string|max:1000',

            'items'                  => 'required|array|min:1',
            'items.*.product_id'     => 'required|exists:master_products,id',
            'items.*.quantity'       => 'required|numeric|min:0.01',
            'items.*.custom_blend_mix' => 'nullable|string',

            'items.*.delivery_slots'                       => 'required|array|min:1',
            'items.*.delivery_slots.*.quantity'            => 'required|numeric|min:0.01',
            'items.*.delivery_slots.*.delivery_date'       => 'required|date',
            'items.*.delivery_slots.*.delivery_time'       => 'required|date_format:H:i',
            'items.*.delivery_slots.*.truck_type'            => 'nullable|string|max:50',
            'items.*.delivery_slots.*.load_size'            => 'nullable|string|max:50',
            'items.*.delivery_slots.*.time_interval'         => 'nullable|string|max:50',
        ]);

        if ($v->fails()) {
            return response()->json(['error' => $v->errors()], 422);
        }

        // enforce: item.quantity == sum(slots.quantity)
        foreach ($request->items as $idx => $item) {
            $slotSum = collect($item['delivery_slots'])->sum(function ($s) {
                return (float) ($s['quantity'] ?? 0);
            });

            $itemQty = (float) ($item['quantity'] ?? 0);

            // allow tiny rounding tolerance
            if (abs($slotSum - $itemQty) > 0.01) {
                return response()->json([
                    'error' => [
                        "items.$idx.quantity" => ["Item quantity must equal sum of delivery slot quantities (expected {$slotSum})."]
                    ]
                ], 422);
            }
        }

        $user = Auth::user();

        return DB::transaction(function () use ($request, $user) {
            /** @var Orders $order */
            $order = Orders::create([
                'po_number'        => $request->po_number,
                'client_id'        => $user->id,
                'project_id'       => $request->project_id,
                'delivery_address' => $request->delivery_address,
                'delivery_lat'     => $request->delivery_lat,
                'delivery_long'    => $request->delivery_long,

                // will be set after we find earliest slot (optional)
                'delivery_date'    => $request->delivery_date,
                'delivery_time'    => null,

                'load_size'        => $request->load_size,
                'special_equipment'=> $request->special_equipment,

                'contact_person_name'   => $request->contact_person_name,
                'contact_person_number' => $request->contact_person_number,

                'other_charges'    => 0,
                'gst_tax'          => 0,
                'discount'         => 0,
                'total_price'      => 0,

                // keep your current fields
                'customer_item_cost'         => 0,
                'customer_delivery_cost'     => 0,
                'supplier_item_cost'         => 0,
                'supplier_delivery_cost'     => 0,

                'payment_status'   => 'Pending',
                'order_status'     => 'Draft',
                'order_process'    => 'Automated',
                'generate_invoice' => 0,
                'repeat_order'     => $request->repeat_order ? 1 : 0,
                'special_notes'    => $request->special_notes ?? null,
            ]);

            $lat = (float) $order->delivery_lat;
            $lng = (float) $order->delivery_long;

            // collect product ids
            $productIds = collect($request->items)->pluck('product_id')->unique()->values();

            // preload offers
            $offers = SupplierOffers::with(['supplier:id,delivery_zones'])
                ->whereIn('master_product_id', $productIds)
                ->where('status', 'Approved')
                ->whereIn('availability_status', ['In Stock', 'Limited'])
                ->get()
                ->groupBy('master_product_id');

            $anyMissingSupplier = false;

            // track earliest slot across all items
            $earliest = null; // Carbon-like array or string compare; we will keep simple with strings

            foreach ($request->items as $row) {
                $pid   = (int) $row['product_id'];
                $blend = $row['custom_blend_mix'] ?? null;

                // total item qty (already validated equals sum of slots)
                $totalQty = (float) $row['quantity'];

                // pick supplier per item (not per slot)
                [$chosenOffer, $distanceKm] = $this->pickNearestOfferInZone(
                    $offers->get($pid) ?? collect(),
                    $lat,
                    $lng
                );

                $supplierId = $chosenOffer ? (int) $chosenOffer->supplier_id : null;

                if (!$supplierId) {
                    $anyMissingSupplier = true;
                }

                /** @var OrderItem $orderItem */
                $orderItem = $order->items()->create([
                    'product_id'             => $pid,
                    'quantity'               => $totalQty,
                    'supplier_id'            => $supplierId,
                    'custom_blend_mix'       => $blend,

                    // keep your unit/delivery costs at item level (recommended)
                    'supplier_unit_cost'     => (float) ($chosenOffer->unit_cost ?? $chosenOffer->price ?? 0),
                    'supplier_delivery_cost' => (float) ($chosenOffer->delivery_cost ?? 0),

                    // legacy column - optional; keep null or set earliest slot date for this item
                    'supplier_delivery_date' => null,

                    'choosen_offer_id'       => $chosenOffer ? $chosenOffer->id : null,
                    'supplier_confirms'      => 0,
                ]);

                // create delivery slot rows for this item
                foreach ($row['delivery_slots'] as $slot) {
                    $slotQty  = (float) $slot['quantity'];
                    $slotDate = $slot['delivery_date'];
                    $slotTime = $slot['delivery_time'];
                    $truckType = $slot['truck_type'] ?? null;
                    $loadSize = $slot['load_size'] ?? null;
                    $timeInterval = $slot['time_interval'] ?? null;

                    // update earliest slot
                    $slotKey = $slotDate . ' ' . $slotTime;
                    if ($earliest === null || $slotKey < $earliest) {
                        $earliest = $slotKey;
                    }

                    // Store supplier_id here ONLY if you want it duplicated. Otherwise omit supplier_id column or set null.
                    \App\Models\OrderItemDelivery::create([
                        'order_id'         => $order->id,
                        'order_item_id'    => $orderItem->id,
                        'supplier_id'      => $supplierId, // set null if supplier missing
                        'quantity'         => $slotQty,
                        'delivery_date'    => $slotDate,
                        'delivery_time'    => $slotTime,
                        'truck_type'     => $truckType,
                        'load_size'      => $loadSize,
                        'time_interval'   => $timeInterval,
                        'supplier_confirms'=> 0,
                    ]);
                }
            }

            // set order-level delivery_date/time from earliest slot (recommended)
            if ($earliest !== null) {
                [$d, $t] = explode(' ', $earliest);
                $order->delivery_date = $d;
                $order->delivery_time = $t;
            }

            if ($anyMissingSupplier) {
                $order->workflow      = 'Supplier Missing';
                $order->order_process = 'Action Required';
            } else {
                $order->workflow      = 'Supplier Assigned';
                $order->order_process = 'Automated';
            }
            $order->save();

            ActionLog::create([
                'action'   => 'Order Created',
                'details'  => "Order ID {$order->id} created by Client {$user->contact_name}",
                'order_id' => $order->id,
                'user_id'  => $user->id,
            ]);

            // Response load
            $order->load([
                'items.product',
                'items.supplier',
                'items.deliveries',
            ]);

            return response()->json([
                'message' => 'Order created',
                'order'   => $order,
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
        $perPage = (int) $request->integer('per_page', 10);
        $page    = (int) $request->integer('page', 1);

        // Check if location parameters are provided
        $hasLocation  = $request->filled('delivery_lat') && $request->filled('delivery_long');
        $deliveryLat  = $hasLocation ? (float) $request->get('delivery_lat') : null;
        $deliveryLong = $hasLocation ? (float) $request->get('delivery_long') : null;

        // When location is provided, pre-filter suppliers by delivery zone
        $supplierIdsInZone = null;
        if ($hasLocation) {
            $allSuppliers = User::whereNotNull('delivery_zones')
                ->where('role', 'supplier')
                ->where('status', 'active')
                ->get(['id', 'delivery_zones']);

            $supplierIdsInZone = $allSuppliers->filter(function ($supplier) use ($deliveryLat, $deliveryLong) {
                $zones = is_string($supplier->delivery_zones)
                    ? json_decode($supplier->delivery_zones, true)
                    : $supplier->delivery_zones;

                if (!is_array($zones)) return false;

                foreach ($zones as $z) {
                    if (!isset($z['lat'], $z['long'], $z['radius'])) continue;
                    $dist = $this->haversineKm($deliveryLat, $deliveryLong, (float)$z['lat'], (float)$z['long']);
                    if ($dist <= (float)$z['radius']) return true;
                }
                return false;
            })->pluck('id')->toArray();

            // If no suppliers found in zone, return empty result early
            if (empty($supplierIdsInZone)) {
                return response()->json([
                    'data' => [],
                    'meta' => [
                        'current_page' => $page,
                        'per_page'     => $perPage,
                        'total'        => 0,
                        'last_page'    => 1,
                    ],
                ]);
            }
        }

        // Aggregate approved, available offers per product
        $offersAgg = SupplierOffers::query()
            ->select('supplier_offers.master_product_id')
            ->selectRaw('MIN(supplier_offers.price) AS price_min')
            ->selectRaw('MAX(supplier_offers.price) AS price_max')
            ->selectRaw('COUNT(DISTINCT supplier_offers.supplier_id) AS suppliers_count')
            ->join('users as suppliers', 'suppliers.id', '=', 'supplier_offers.supplier_id')
            ->where('supplier_offers.status', 'Approved')
            ->whereIn('supplier_offers.availability_status', ['In Stock', 'Limited']);

        // Scope to zone-filtered suppliers when location is provided
        if ($supplierIdsInZone !== null) {
            $offersAgg->whereIn('supplier_offers.supplier_id', $supplierIdsInZone);
        }

        $offersAgg->groupBy('supplier_offers.master_product_id');

        // Build main query
        $query = MasterProducts::query()
            ->joinSub($offersAgg, 'oa', function ($join) {
                $join->on('oa.master_product_id', '=', 'master_products.id');
            })
            ->select('master_products.*', 'oa.price_min', 'oa.price_max', 'oa.suppliers_count');

        // Search by product_name
        if ($search = trim((string) $request->get('search'))) {
            $query->where('master_products.product_name', 'like', "%{$search}%");
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('master_products.category', $request->get('category'));
        }

        // Filter by product_type
        if ($request->filled('product_type')) {
            $query->where('master_products.product_type', $request->get('product_type'));
        }

        // Exclude certain product types
        $query->whereNotIn('master_products.product_type', ['pavers', 'paver', 'bricks', 'brick', 'blocks', 'block']);

        // Sort
        if ($request->get('sort') === 'price') {
            $query->orderBy('oa.price_min', 'asc');
        } else {
            $query->orderBy('master_products.product_name', 'asc');
        }

        $products = $query->with('category')->paginate($perPage, ['*'], 'page', $page);

        // Transform response
        $products->getCollection()->transform(function ($p) use ($hasLocation) {
            $p->price = ($p->price_min == $p->price_max)
                ? sprintf('$%.2f', $p->price_min)
                : sprintf('$%.2f - $%.2f', $p->price_min, $p->price_max);

            // When location is passed, all returned products are available (pre-filtered)
            // When no location, null indicates unknown
            $p->is_available = $hasLocation ? true : null;

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

    public function getMyOrders(Request $request)
    {
        $user = Auth::user();

        // Constants
        $ITEM_MARGIN     = 1.50; // 50% margin multiplier
        $DELIVERY_MARGIN = 1.10; // 10% margin multiplier
        $GST_RATE        = 0.10;

        $details         = filter_var($request->get('details', false), FILTER_VALIDATE_BOOLEAN);
        $perPage         = (int) $request->get('per_page', 10);
        $search          = trim((string) $request->get('search', ''));
        $sort            = $request->get('sort', 'created_at');
        $dir             = strtolower($request->get('dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        $delivery_date   = $request->get('delivery_date');
        $project_id      = $request->get('project_id');
        $order_status    = $request->get('order_status');
        $payment_status  = $request->get('payment_status');
        $delivery_method = $request->get('delivery_method');

        $repeat_order = null;
        if ($request->has('repeat_order')) {
            $repeat_order = $request->get('repeat_order');
            if ($repeat_order === 'true' || $repeat_order === '1') {
                $repeat_order = true;
            } elseif ($repeat_order === 'false' || $repeat_order === '0') {
                $repeat_order = false;
            } else {
                $repeat_order = null;
            }
        }

        // Base query with computed cost subqueries
        $query = Orders::query()
            ->with(['project:id,name'])
            ->withCount('items as items_count')

            // Invoice counts
            ->withCount('invoices as invoices_count')
            ->addSelect([
                'invoiced_amount' => DB::table('invoices')
                    ->selectRaw('COALESCE(SUM(total_amount), 0)')
                    ->whereColumn('invoices.order_id', 'orders.id')
                    ->whereNotIn('status', ['Cancelled', 'Void']),
            ])

            // Customer item cost (supplier_unit_cost × qty × 1.5 margin)
            ->addSelect([
                'calc_customer_item_cost' => DB::table('order_items')
                    ->selectRaw("COALESCE(SUM(supplier_unit_cost * quantity * {$ITEM_MARGIN}), 0)")
                    ->whereColumn('order_items.order_id', 'orders.id'),
            ])

            // Customer delivery cost (delivery_cost × 1.1 margin)
            ->addSelect([
                'calc_customer_delivery_cost' => DB::table('order_item_deliveries')
                    ->join('order_items', 'order_item_deliveries.order_item_id', '=', 'order_items.id')
                    ->selectRaw("COALESCE(SUM(order_item_deliveries.delivery_cost * {$DELIVERY_MARGIN}), 0)")
                    ->whereColumn('order_items.order_id', 'orders.id'),
            ])

            ->where('client_id', $user->id)
            ->where('is_archived', false);

        // Filters
        if ($search !== '')         $query->where('po_number', 'like', "%{$search}%");
        if ($delivery_date)         $query->whereDate('delivery_date', $delivery_date);
        if ($project_id)            $query->where('project_id', $project_id);
        if ($order_status)          $query->where('order_status', $order_status);
        if ($payment_status)        $query->where('payment_status', $payment_status);
        if ($delivery_method)       $query->where('delivery_method', $delivery_method);
        if ($repeat_order !== null) $query->where('repeat_order', (bool) $repeat_order);

        // Sorting
        $allowedSorts = ['po_number', 'delivery_date', 'created_at', 'updated_at'];
        if (!in_array($sort, $allowedSorts, true)) $sort = 'created_at';
        $query->orderBy($sort, $dir);

        $paginator = $query->paginate($perPage);

        // Transform rows
        $data = $paginator->getCollection()->map(function (Orders $o) use ($GST_RATE) {
            $customerItemCost     = round((float)($o->calc_customer_item_cost ?? 0), 2);
            $customerDeliveryCost = round((float)($o->calc_customer_delivery_cost ?? 0), 2);
            $customerSubtotal     = round($customerItemCost + $customerDeliveryCost, 2);
            $gst                  = round($customerSubtotal * $GST_RATE, 2);
            $discount             = round((float)($o->discount ?? 0), 2);
            $otherCharges         = round((float)($o->other_charges ?? 0), 2);
            $totalPrice           = round($customerSubtotal + $gst - $discount + $otherCharges, 2);

            // Order info text
            $orderInfo = null;
            if ($o->order_status === 'Draft') {
                $orderInfo = 'Order draft created';
            } elseif ($o->order_status === 'Confirmed') {
                $orderInfo = 'Order confirmed, awaiting schedule';
            } elseif ($o->order_status === 'Scheduled') {
                $orderInfo = 'Order scheduled for delivery';
            } elseif ($o->order_status === 'In Transit') {
                $orderInfo = 'Order in transit';
            } elseif ($o->order_status === 'Delivered') {
                $orderInfo = 'Order delivered';
            } elseif ($o->order_status === 'Completed') {
                $orderInfo = 'Order completed';
            } elseif ($o->order_status === 'Cancelled') {
                $orderInfo = 'Order cancelled';
            } elseif ($o->payment_status === 'Unpaid' || $o->payment_status === 'Requested') {
                $orderInfo = 'Payment required';
            }

            return [
                'id'                     => $o->id,
                'po_number'              => $o->po_number,
                'project_id'             => $o->project_id,
                'client_id'              => $o->client_id,
                'workflow'               => $o->workflow,
                'order_status'           => $o->order_status,
                'payment_status'         => $o->payment_status,

                'delivery_address'       => $o->delivery_address,
                'delivery_date'          => $o->delivery_date,
                'delivery_time'          => $o->delivery_time,
                'delivery_method'        => $o->delivery_method,

                'repeat_order'           => (bool) $o->repeat_order,
                'order_info'             => $orderInfo,

                'created_at'             => $o->created_at,
                'updated_at'             => $o->updated_at,

                // Counts
                'items_count'            => $o->items_count ?? 0,

                // Pricing (computed bottom-up)
                'customer_item_cost'     => $customerItemCost,
                'customer_delivery_cost' => $customerDeliveryCost,
                'gst_tax'                => $gst,
                'discount'               => $discount,
                'other_charges'          => $otherCharges,
                'total_price'            => $totalPrice,

                // Invoices
                'invoices_count'         => $o->invoices_count ?? 0,
                'invoiced_amount'        => round((float)($o->invoiced_amount ?? 0), 2),

                // Project relation
                'project'                => $o->project ? [
                    'id'   => $o->project->id,
                    'name' => $o->project->name,
                ] : null,
            ];
        });

        // Metrics
        $base = Orders::where('client_id', $user->id)->where('is_archived', 0);
        $metrics = [
            'total_orders_count' => (clone $base)->count(),
            'draft_count'        => (clone $base)->where('order_status', 'Draft')->count(),
            'confirmed_count'    => (clone $base)->where('order_status', 'Confirmed')->count(),
            'scheduled_count'    => (clone $base)->where('order_status', 'Scheduled')->count(),
            'in_transit_count'   => (clone $base)->where('order_status', 'In Transit')->count(),
            'delivered_count'    => (clone $base)->where('order_status', 'Delivered')->count(),
            'completed_count'    => (clone $base)->where('order_status', 'Completed')->count(),
            'cancelled_count'    => (clone $base)->where('order_status', 'Cancelled')->count(),
        ];

        $response = [
            'data'       => $data,
            'pagination' => [
                'per_page'       => $paginator->perPage(),
                'current_page'   => $paginator->currentPage(),
                'total_pages'    => $paginator->lastPage(),
                'total_items'    => $paginator->total(),
                'has_more_pages' => $paginator->hasMorePages(),
            ],
            'metrics' => $metrics,
        ];

        if ($details) {
            $projects = Projects::where('added_by', $user->id)
                ->orderBy('created_at', 'desc')
                ->get(['id', 'name']);

            $response['projects']         = $projects;
            $response['order_statuses']   = ['Draft', 'Confirmed', 'Scheduled', 'In Transit', 'Delivered', 'Completed', 'Cancelled'];
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

        // Constants
        $ITEM_MARGIN     = 1.50; // 50% margin multiplier
        $DELIVERY_MARGIN = 1.10; // 10% margin multiplier
        $GST_RATE        = 0.10;

        $order->load([
            'project',
            'client.company',
            'items.product',
            'items.supplier',
            'items.deliveries',
            'invoices.items.orderItem.product',
            'invoices.items.delivery',
            'invoices.createdBy:id,name',
        ]);

        // Order info text
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

        // ==================== COMPUTE COSTS BOTTOM-UP ====================
        $customerItemCost     = 0;
        $customerDeliveryCost = 0;

        $order->items->each(function (OrderItem $item) use ($ITEM_MARGIN, $DELIVERY_MARGIN, &$customerItemCost, &$customerDeliveryCost) {
            // Customer item cost: supplier_unit_cost × quantity × margin
            $supplierUnitCost = (float) ($item->supplier_unit_cost ?? 0);
            $qty              = (float) ($item->quantity ?? 0);
            $customerItemCost += $supplierUnitCost * $qty * $ITEM_MARGIN;

            // Customer delivery cost: sum of each delivery's cost × margin
            if ($item->relationLoaded('deliveries')) {
                foreach ($item->deliveries as $delivery) {
                    $deliveryCost = (float) ($delivery->delivery_cost ?? 0);
                    $customerDeliveryCost += $deliveryCost * $DELIVERY_MARGIN;
                }

                // Sort deliveries for UI
                $item->deliveries = $item->deliveries
                    ->sortBy(fn($d) => $d->delivery_date . ' ' . ($d->delivery_time ?? '00:00'))
                    ->values();
            }
        });

        $customerItemCost     = round($customerItemCost, 2);
        $customerDeliveryCost = round($customerDeliveryCost, 2);
        $customerSubtotal     = round($customerItemCost + $customerDeliveryCost, 2);
        $gst                  = round($customerSubtotal * $GST_RATE, 2);
        $discount             = round((float) ($order->discount ?? 0), 2);
        $otherCharges         = round((float) ($order->other_charges ?? 0), 2);
        $totalPrice           = round($customerSubtotal + $gst - $discount + $otherCharges, 2);

        // ==================== BUILD ORDER DATA ====================
        $orderData = [
            'id'                     => $order->id,
            'po_number'              => $order->po_number,
            'project_id'             => $order->project_id,
            'client_id'              => $order->client_id,
            'order_process'          => $order->order_process,
            'delivery_address'       => $order->delivery_address,
            'order_status'           => $order->order_status,
            'delivery_date'          => $order->delivery_date,
            'delivery_time'          => $order->delivery_time,
            'delivery_method'        => $order->delivery_method,
            'repeat_order'           => $order->repeat_order,
            'workflow'               => $order->workflow,
            'contact_person_name'    => $order->contact_person_name,
            'contact_person_number'  => $order->contact_person_number,
            'payment_status'         => $order->payment_status,
            'reason'                 => $order->reason,
            'created_at'             => $order->created_at,
            'updated_at'             => $order->updated_at,

            // Computed costs (bottom-up)
            'customer_item_cost'     => $customerItemCost,
            'customer_delivery_cost' => $customerDeliveryCost,
            'gst_tax'                => $gst,
            'discount'               => $discount,
            'other_charges'          => $otherCharges,
            'total_price'            => $totalPrice,

            'order_info'             => $order->order_info,
        ];

        // Project
        $orderData['project'] = optional($order->project)?->only([
            'id',
            'name',
            'site_contact_name',
            'site_contact_phone',
            'site_instructions',
        ]);

        // Client + Company
        $orderData['client'] = optional($order->client)->only([
            'id',
            'name',
            'email',
            'profile_image',
            'phone',
        ]);

        if ($order->client && $order->client->company) {
            $orderData['client']['company'] = $order->client->company->only([
                'id',
                'name',
                'abn',
                'address',
                'phone',
                'email',
            ]);
        }

        // ==================== FORMAT INVOICES ====================
        $formattedInvoices = $order->invoices->map(function ($invoice) {
            return [
                'id'              => $invoice->id,
                'invoice_number'  => $invoice->invoice_number,
                'status'          => $invoice->status,
                'issued_date'     => $invoice->issued_date?->format('Y-m-d'),
                'due_date'        => $invoice->due_date?->format('Y-m-d'),
                'notes'           => $invoice->notes,

                'subtotal'        => round((float) $invoice->subtotal, 2),
                'delivery_total'  => round((float) $invoice->delivery_total, 2),
                'gst_tax'         => round((float) $invoice->gst_tax, 2),
                'discount'        => round((float) $invoice->discount, 2),
                'total_amount'    => round((float) $invoice->total_amount, 2),

                'created_by'      => $invoice->createdBy?->name ?? 'System',
                'created_at'      => $invoice->created_at?->toISOString(),

                'items'           => $invoice->items->map(function ($item) {
                    return [
                        'id'                     => $item->id,
                        'product_name'           => $item->product_name,
                        'quantity'               => round((float) $item->quantity, 2),
                        'unit_price'             => round((float) $item->unit_price, 2),
                        'delivery_cost'          => round((float) $item->delivery_cost, 2),
                        'line_total'             => round((float) $item->line_total, 2),
                        'unit_of_measure'        => $item->orderItem?->product?->unit_of_measure ?? 'unit',
                        'order_item_id'          => $item->order_item_id,
                        'order_item_delivery_id' => $item->order_item_delivery_id,
                        'delivery_date'          => $item->delivery?->delivery_date?->format('Y-m-d'),
                        'delivery_time'          => $item->delivery?->delivery_time,
                        'delivery_status'        => $item->delivery?->status,
                    ];
                }),
            ];
        })->sortByDesc('created_at')->values();

        return response()->json([
            'success' => true,
            'data' => [
                'order'    => $orderData,
                'items'    => $order->items,
                'invoices' => $formattedInvoices,
            ],
        ]);
    }

    /**
     * Edit Order
     */

    public function editMyOrder(Request $request, Orders $order)
    {
        abort_unless($order->client_id === Auth::id(), 403);

        $order->load(['items.deliveries']);

        $v = Validator::make($request->all(), [
            'order' => ['nullable', 'array'],
            'order.contact_person_name'   => ['nullable', 'string', 'max:255'],
            'order.contact_person_number' => ['nullable', 'string', 'max:50'],
            'order.site_instructions'     => ['nullable', 'string'],

            'items_add' => ['nullable', 'array'],
            'items_add.*.product_id' => ['required', 'integer', 'exists:master_products,id'],
            'items_add.*.quantity'   => ['required', 'numeric', 'min:0.01'],
            'items_add.*.deliveries' => ['nullable', 'array'],
            'items_add.*.deliveries.*.id'            => ['nullable', 'integer'], // should be null for new
            'items_add.*.deliveries.*.quantity'           => ['required_with:items_add.*.deliveries', 'numeric', 'min:0.01'],
            'items_add.*.deliveries.*.delivery_date' => ['required_with:items_add.*.deliveries', 'date'],
            'items_add.*.deliveries.*.delivery_time' => ['nullable', 'date_format:H:i'],
            'items_add.*.deliveries.*.load_size' => ['nullable', 'string', 'max:100'],
            'items_add.*.deliveries.*.time_interval' => ['nullable', 'string', 'max:50'],
            

            'items_update' => ['nullable', 'array'],
            'items_update.*.order_item_id' => ['required', 'integer'],
            'items_update.*.quantity'      => ['required', 'numeric', 'min:0.01'],
            'items_update.*.deliveries'    => ['nullable', 'array'],
            'items_update.*.deliveries.*.id'            => ['nullable', 'integer'],
            'items_update.*.deliveries.*.quantity'           => ['required_with:items_update.*.deliveries', 'numeric', 'min:0.01'],
            'items_update.*.deliveries.*.delivery_date' => ['required_with:items_update.*.deliveries', 'date'],
            'items_update.*.deliveries.*.delivery_time' => ['nullable', 'date_format:H:i'],
            'items_update.*.deliveries.*.load_size' => ['nullable', 'string', 'max:100'],
            'items_update.*.deliveries.*.time_interval' => ['nullable', 'string', 'max:50'],

            'items_remove' => ['nullable', 'array'],
            'items_remove.*' => ['integer'],
        ]);

        if ($v->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $v->errors(),
            ], 422);
        }

        $payload = $v->validated();

        return DB::transaction(function () use ($order, $payload) {

            $order->refresh()->load(['items.deliveries']);
            $itemsById = $order->items->keyBy('id');

            // -------------------------
            // 1) Update order fields (ONLY allowed fields)
            // -------------------------
            if (!empty($payload['order'])) {
                $patch = [];
                if (array_key_exists('contact_person_name', $payload['order'])) {
                    $patch['contact_person_name'] = $payload['order']['contact_person_name'];
                }
                if (array_key_exists('contact_person_number', $payload['order'])) {
                    $patch['contact_person_number'] = $payload['order']['contact_person_number'];
                }
                if (array_key_exists('site_instructions', $payload['order'])) {
                    $patch['site_instructions'] = $payload['order']['site_instructions'];
                }

                if (!empty($patch)) {
                    $order->fill($patch);
                    $order->save();
                }
            }

            // -------------------------
            // 2) Remove items (ONLY if no delivered deliveries)
            // -------------------------
            if (!empty($payload['items_remove'])) {
                foreach ($payload['items_remove'] as $removeItemId) {
                    /** @var OrderItem|null $item */
                    $item = $itemsById->get($removeItemId);

                    if (!$item) {
                        return response()->json([
                            'success' => false,
                            'message' => "Item {$removeItemId} does not belong to this order.",
                        ], 422);
                    }

                    // delivered = sum where status == delivered (you use this rule)
                    $hasDelivered = $item->deliveries->where('status', 'delivered')->isNotEmpty();
                    if ($hasDelivered) {
                        return response()->json([
                            'success' => false,
                            'message' => "Cannot remove item {$removeItemId} because it has delivered split deliveries.",
                        ], 422);
                    }

                    // delete non-delivered deliveries, then item
                    OrderItemDelivery::where('order_item_id', $item->id)
                        ->where(function ($q) {
                            $q->whereNull('status')->orWhere('status', '!=', 'delivered');
                        })
                        ->delete();

                    $item->delete();
                }
            }

            // refresh after deletions
            $order->refresh()->load(['items.deliveries']);
            $itemsById = $order->items->keyBy('id');

            // -------------------------
            // Supplier assignment preload for NEW items (same as createOrder)
            // -------------------------
            $lat = (float) $order->delivery_lat;
            $lng = (float) $order->delivery_long;

            $addProductIds = collect($payload['items_add'] ?? [])
                ->pluck('product_id')
                ->unique()
                ->values();

            $offersByProduct = collect();
            if ($addProductIds->isNotEmpty()) {
                $offersByProduct = SupplierOffers::with(['supplier:id,delivery_zones'])
                    ->whereIn('master_product_id', $addProductIds)
                    ->where('status', 'Approved')
                    ->whereIn('availability_status', ['In Stock', 'Limited'])
                    ->get()
                    ->groupBy('master_product_id');
            }

            $anyMissingSupplierInOrder = false;

            // -------------------------
            // 3) Add items (+ deliveries) + assign nearest supplier (NEW REQUIREMENT)
            // -------------------------
            if (!empty($payload['items_add'])) {
                foreach ($payload['items_add'] as $add) {
                    $pid = (int) $add['product_id'];

                    // pick supplier exactly like createOrder()
                    [$chosenOffer, $distanceKm] = $this->pickNearestOfferInZone(
                        $offersByProduct->get($pid) ?? collect(),
                        $lat,
                        $lng
                    );

                    $supplierId = $chosenOffer ? (int) $chosenOffer->supplier_id : null;

                    /** @var OrderItem $newItem */
                    $newItem = $order->items()->create([
                        'product_id'             => $pid,
                        'quantity'               => (float) $add['quantity'],
                        'supplier_id'            => $supplierId,

                        'supplier_unit_cost'     => (float) ($chosenOffer->unit_cost ?? $chosenOffer->price ?? 0),
                        'supplier_delivery_cost' => (float) ($chosenOffer->delivery_cost ?? 0),

                        'supplier_delivery_date' => null,
                        'choosen_offer_id'       => $chosenOffer ? $chosenOffer->id : null,
                        'supplier_confirms'      => 0,
                    ]);

                    $deliveries = $add['deliveries'] ?? [];

                    if (!empty($deliveries)) {
                        // IMPORTANT: payload uses qty, DB uses quantity
                        $sum = (float) collect($deliveries)->sum('quantity');

                        if (abs($sum - (float) $newItem->quantity) > 0.01) {
                            return response()->json([
                                'success' => false,
                                'message' => "Split deliveries total must match item quantity for product_id {$sum}.",
                            ], 422);
                        }

                        foreach ($deliveries as $d) {
                            OrderItemDelivery::create([
                                'order_id'          => $order->id,
                                'order_item_id'     => $newItem->id,
                                'supplier_id'       => $supplierId,   // same as createOrder
                                'quantity'          => (float) $d['quantity'],
                                'delivery_date'     => $d['delivery_date'],
                                'delivery_time'     => $d['delivery_time'] ?? null,
                                'load_size'        => $d['load_size'] ?? null,
                                'time_interval'    => $d['time_interval'] ?? null,
                                'supplier_confirms' => 0,

                                // ensure consistent status for edit rules
                                'status'            => 'scheduled',
                            ]);
                        }
                    }

                    if (!$supplierId) {
                        $anyMissingSupplierInOrder = true;
                    }
                }
            }

            // refresh after additions
            $order->refresh()->load(['items.deliveries']);
            $itemsById = $order->items->keyBy('id');

            // -------------------------
            // 4) Update items (quantity + sync scheduled deliveries by id)
            // -------------------------
            if (!empty($payload['items_update'])) {
                foreach ($payload['items_update'] as $upd) {

                    /** @var OrderItem|null $item */
                    $item = $itemsById->get($upd['order_item_id']);

                    if (!$item) {
                        return response()->json([
                            'success' => false,
                            'message' => "Item {$upd['order_item_id']} does not belong to this order.",
                        ], 422);
                    }

                    $item->loadMissing('deliveries');

                    $deliveredQty = (float) $item->deliveries
                        ->where('status', 'delivered')
                        ->sum('quantity');

                    $newQty = (float) $upd['quantity'];

                    if ($newQty < $deliveredQty) {
                        return response()->json([
                            'success' => false,
                            'message' => "Item {$item->id} quantity cannot be less than delivered quantity ({$deliveredQty}).",
                        ], 422);
                    }

                    $item->quantity = $newQty;
                    $item->save();

                    // Sync scheduled deliveries only (delivered rows are read-only)
                    if (array_key_exists('deliveries', $upd)) {
                        $reqDeliveries = $upd['deliveries'] ?? [];

                        $requiredScheduledTotal = $newQty - $deliveredQty;
                        $reqTotal = (float) collect($reqDeliveries)->sum('quantity');

                        if (abs($reqTotal - $requiredScheduledTotal) > 0.01) {
                            return response()->json([
                                'success' => false,
                                'message' => "Scheduled deliveries total must be {$requiredScheduledTotal} for item {$item->id} (delivered: {$deliveredQty}).",
                            ], 422);
                        }

                        $existing = $item->deliveries->keyBy('id');

                        $existingScheduledIds = $item->deliveries
                            ->filter(fn($d) => $d->status !== 'delivered') // includes null/scheduled
                            ->pluck('id')
                            ->filter()
                            ->values()
                            ->all();

                        $requestIds = collect($reqDeliveries)
                            ->pluck('id')
                            ->filter()
                            ->values()
                            ->all();

                        foreach ($reqDeliveries as $d) {
                            $did = $d['id'] ?? null;

                            if ($did) {
                                /** @var OrderItemDelivery|null $row */
                                $row = $existing->get($did);

                                if (!$row || (int) $row->order_item_id !== (int) $item->id) {
                                    return response()->json([
                                        'success' => false,
                                        'message' => "Delivery {$did} is invalid for item {$item->id}.",
                                    ], 422);
                                }

                                if ($row->status === 'delivered') {
                                    return response()->json([
                                        'success' => false,
                                        'message' => "Delivered delivery {$did} cannot be edited.",
                                    ], 422);
                                }

                                $row->quantity      = (float) $d['quantity'];
                                $row->delivery_date = $d['delivery_date'];
                                $row->delivery_time = $d['delivery_time'] ?? null;
                                $row->load_size     = $d['load_size'] ?? null;
                                $row->time_interval = $d['time_interval'] ?? null;
                                $row->status        = $row->status ?: 'scheduled';
                                $row->save();
                            } else {
                                OrderItemDelivery::create([
                                    'order_id'          => $order->id,
                                    'order_item_id'     => $item->id,
                                    'supplier_id'       => $item->supplier_id, // keep same supplier for this item
                                    'quantity'          => (float) $d['quantity'],
                                    'delivery_date'     => $d['delivery_date'],
                                    'delivery_time'     => $d['delivery_time'] ?? null,
                                    'load_size'        => $d['load_size'] ?? null,
                                    'time_interval'    => $d['time_interval'] ?? null,
                                    'supplier_confirms' => 0,
                                    'status'            => 'scheduled',
                                ]);
                            }
                        }

                        // delete scheduled (non-delivered) deliveries not present in request
                        $toDelete = array_values(array_diff($existingScheduledIds, $requestIds));
                        if (!empty($toDelete)) {
                            OrderItemDelivery::whereIn('id', $toDelete)
                                ->where('order_item_id', $item->id)
                                ->where(function ($q) {
                                    $q->whereNull('status')->orWhere('status', '!=', 'delivered');
                                })
                                ->delete();
                        }
                    }
                }
            }

            // -------------------------
            // 5) Recompute workflow/order_process (same rule as createOrder)
            // -------------------------
            $hasMissing = OrderItem::where('order_id', $order->id)->whereNull('supplier_id')->exists();
            if ($hasMissing || $anyMissingSupplierInOrder) {
                $order->workflow      = 'Supplier Missing';
                $order->order_process = 'Action Required';
            } else {
                $order->workflow      = 'Supplier Assigned';
                $order->order_process = 'Automated';
            }

            // Optional: recompute order-level earliest delivery_date/time from deliveries
            $earliest = OrderItemDelivery::where('order_id', $order->id)
                ->orderBy('delivery_date')
                ->orderBy('delivery_time')
                ->first();

            if ($earliest) {
                $order->delivery_date = $earliest->delivery_date;
                $order->delivery_time = $earliest->delivery_time;
            }

            $order->save();

            // Response
            $order->refresh()->load(['project', 'items.product', 'items.supplier', 'items.deliveries']);

            return response()->json([
                'success' => true,
                'message' => 'Order updated successfully.',
                'data' => [
                    'order' => $order,
                    'items' => $order->items,
                ],
            ]);
        });
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
        


    /**
     * Client Dashboard — enhanced with monthly deliveries + recent orders
     */
    public function clientDashboard(Request $request)
    {
        $clientId = Auth::id();

        // Range for graph (default: last 14 days)
        $days = (int) ($request->get('days', 14));
        if ($days < 7) $days = 7;
        if ($days > 90) $days = 90;

        $tz = config('app.timezone', 'UTC');
        $today = Carbon::now($tz)->toDateString();
        $from  = Carbon::now($tz)->subDays($days - 1)->toDateString();

        // -----------------------------
        // TOTAL ORDERS
        // -----------------------------
        $totalOrders = Orders::where('client_id', $clientId)->count();

        $openOrders = Orders::where('client_id', $clientId)
            ->whereNotIn('order_status', ['Completed', 'Cancelled'])
            ->count();

        // -----------------------------
        // TODAY'S DELIVERIES
        // -----------------------------
        $todaysDeliveries = OrderItemDelivery::query()
            ->whereRaw('DATE(delivery_date) = ?', [$today])
            ->whereHas('order', fn($q) => $q->where('client_id', $clientId))
            ->with([
                'order:id,client_id,project_id,delivery_address,po_number',
                'order.project:id,name',
                'orderItem:id,product_id,quantity',
                'orderItem.product:id,product_name',
                'supplier:id,company_name',
            ])
            ->orderBy('delivery_time')
            ->get()
            ->map(function ($d) {
                return [
                    'id'               => $d->id,
                    'order_id'         => $d->order_id,
                    'po_number'        => $d->order?->po_number,
                    'project'          => $d->order?->project?->only(['id', 'name']),
                    'delivery_date'    => optional($d->delivery_date)->toDateString(),
                    'delivery_time'    => $d->delivery_time,
                    'delivery_address' => $d->order?->delivery_address,
                    'qty'              => (float) $d->quantity,
                    'status'           => $d->status,
                    'product'          => $d->orderItem?->product?->only(['id', 'product_name']),
                    'supplier'         => $d->supplier?->only(['id', 'company_name']),
                ];
            });

        $todaysDeliveryCount = $todaysDeliveries->count();
        $todaysDeliveryQty   = (float) $todaysDeliveries->sum('qty');

        // -----------------------------
        // GRAPH: Deliveries per day (range)
        // -----------------------------
        $deliveriesPerDayRaw = OrderItemDelivery::query()
            ->selectRaw('DATE(delivery_date) as d_date, COUNT(*) as deliveries_count, SUM(quantity) as total_qty')
            ->whereRaw('DATE(delivery_date) BETWEEN ? AND ?', [$from, $today])
            ->whereHas('order', fn($q) => $q->where('client_id', $clientId))
            ->groupBy('d_date')
            ->orderBy('d_date')
            ->get()
            ->keyBy('d_date');

        $series = [];
        $cursor = Carbon::parse($from, $tz);
        $end    = Carbon::parse($today, $tz);

        while ($cursor->lte($end)) {
            $date = $cursor->toDateString();
            $row  = $deliveriesPerDayRaw->get($date);

            $series[] = [
                'date'             => $date,
                'deliveries_count' => (int) ($row->deliveries_count ?? 0),
                'total_qty'        => (float) ($row->total_qty ?? 0),
            ];

            $cursor->addDay();
        }

        // Status breakdown (pie chart)
        $statusBreakdown = OrderItemDelivery::query()
            ->selectRaw('COALESCE(status, "scheduled") as status, COUNT(*) as count')
            ->whereRaw('DATE(delivery_date) BETWEEN ? AND ?', [$from, $today])
            ->whereHas('order', fn($q) => $q->where('client_id', $clientId))
            ->groupBy('status')
            ->orderBy('count', 'desc')
            ->get();

        // =============================================
        // MONTHLY DELIVERIES
        // =============================================
        $monthParam = $request->get('month'); // e.g. "2026-02"
        if ($monthParam && preg_match('/^\d{4}-\d{2}$/', $monthParam)) {
            $monthStart = Carbon::parse($monthParam . '-01', $tz)->startOfMonth();
        } else {
            $monthStart = Carbon::now($tz)->startOfMonth();
        }
        $monthEnd = $monthStart->copy()->endOfMonth();

        $monthlyDeliveriesRaw = OrderItemDelivery::query()
            ->whereRaw('DATE(delivery_date) BETWEEN ? AND ?', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->whereHas('order', fn($q) => $q->where('client_id', $clientId))
            ->with([
                'order:id,client_id,project_id,po_number,delivery_address',
                'order.project:id,name',
                'orderItem:id,product_id,quantity,supplier_confirms',
                'orderItem.product:id,product_name,unit_of_measure',
                'supplier:id,company_name',
            ])
            ->orderBy('delivery_date')
            ->orderBy('delivery_time')
            ->get();

        // Per-day aggregation for graph
        $monthlyPerDay = [];
        $dayCursor = $monthStart->copy();
        while ($dayCursor->lte($monthEnd)) {
            $d = $dayCursor->toDateString();

            $dayItems = $monthlyDeliveriesRaw->filter(function ($item) use ($d) {
                return optional($item->delivery_date)->toDateString() === $d;
            });

            $monthlyPerDay[] = [
                'date'             => $d,
                'total_deliveries' => $dayItems->count(),
                'total_qty'        => (float) $dayItems->sum('quantity'),
                'confirmed'        => $dayItems->where('supplier_confirms', true)->count(),
                'unconfirmed'      => $dayItems->where('supplier_confirms', false)->count(),
            ];
            $dayCursor->addDay();
        }

        // Flat list of monthly deliveries
        $monthlyDeliveries = $monthlyDeliveriesRaw->map(function ($d) {
            return [
                'id'                 => $d->id,
                'order_id'           => $d->order_id,
                'order_item_id'      => $d->order_item_id,
                'po_number'          => $d->order?->po_number,
                'project'            => $d->order?->project?->only(['id', 'name']),
                'product'            => $d->orderItem?->product?->only(['id', 'product_name', 'unit_of_measure']),
                'supplier'           => $d->supplier?->only(['id', 'company_name']),
                'delivery_date'      => optional($d->delivery_date)->toDateString(),
                'delivery_time'      => $d->delivery_time,
                'qty'                => (float) $d->quantity,
                'status'             => $d->status ?? 'scheduled',
                'supplier_confirmed' => (bool) $d->supplier_confirms,
                'delivery_address'   => $d->order?->delivery_address,
            ];
        })->values();

        // Monthly summary
        $monthlySummary = [
            'month'             => $monthStart->format('Y-m'),
            'month_label'       => $monthStart->format('F Y'),
            'total_deliveries'  => $monthlyDeliveriesRaw->count(),
            'total_qty'         => (float) $monthlyDeliveriesRaw->sum('quantity'),
            'confirmed_count'   => $monthlyDeliveriesRaw->where('supplier_confirms', true)->count(),
            'unconfirmed_count' => $monthlyDeliveriesRaw->where('supplier_confirms', false)->count(),
            'delivered_count'   => $monthlyDeliveriesRaw->filter(fn($d) => strtolower($d->status ?? '') === 'delivered')->count(),
            'scheduled_count'   => $monthlyDeliveriesRaw->filter(fn($d) => in_array(strtolower($d->status ?? 'scheduled'), ['scheduled', '']))->count(),
            'cancelled_count'   => $monthlyDeliveriesRaw->filter(fn($d) => strtolower($d->status ?? '') === 'cancelled')->count(),
        ];

        // =============================================
        // RECENT ORDERS (latest 10)
        // =============================================
        $recentOrders = Orders::with([
                'project:id,name',
                'items:id,order_id,product_id,quantity',
                'items.product:id,product_name',
            ])
            ->where('client_id', $clientId)
            ->where('is_archived', false)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function (Orders $o) {
                $deliveryStats = OrderItemDelivery::where('order_id', $o->id)
                    ->selectRaw('COUNT(*) as total, SUM(CASE WHEN supplier_confirms = 1 THEN 1 ELSE 0 END) as confirmed')
                    ->first();

                return [
                    'id'                       => $o->id,
                    'po_number'                => $o->po_number,
                    'project'                  => $o->project?->only(['id', 'name']),
                    'order_status'             => $o->order_status,
                    'payment_status'           => $o->payment_status,
                    'delivery_date'            => $o->delivery_date,
                    'delivery_address'         => $o->delivery_address,
                    'total_price'              => (float) $o->total_price,
                    'items_count'              => $o->items->count(),
                    'products'                 => $o->items->map(fn($i) => [
                        'product_name' => $i->product?->product_name,
                        'quantity'     => (float) $i->quantity,
                    ])->values(),
                    'delivery_slots_total'     => (int) ($deliveryStats->total ?? 0),
                    'delivery_slots_confirmed' => (int) ($deliveryStats->confirmed ?? 0),
                    'repeat_order'             => (bool) $o->repeat_order,
                    'created_at'               => $o->created_at?->toIso8601String(),
                ];
            });

        // =============================================
        // RESPONSE
        // =============================================
        return response()->json([
            'success' => true,
            'data' => [
                'stats' => [
                    'total_orders'            => $totalOrders,
                    'open_orders'             => $openOrders,
                    'todays_deliveries_count' => $todaysDeliveryCount,
                    'todays_deliveries_qty'   => $todaysDeliveryQty,
                    'range_days'              => $days,
                    'range_from'              => $from,
                    'range_to'                => $today,
                ],
                'todays_deliveries' => $todaysDeliveries,
                'graphs' => [
                    'deliveries_per_day' => $series,
                    'delivery_statuses'  => $statusBreakdown,
                ],
                'monthly' => [
                    'summary'    => $monthlySummary,
                    'per_day'    => $monthlyPerDay,
                    'deliveries' => $monthlyDeliveries,
                ],
                'recent_orders' => $recentOrders,
            ],
        ]);
    }


    public function payInvoice(Request $request, $invoice_id)
    {
        // Find the invoice
        $invoice = Invoice::findOrFail($invoice_id);
        
        // Load the order to check ownership
        $invoice->load('order');
        
        // Authorization: Only the invoice's client can mark it as paid
        if ($invoice->client_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You can only pay your own invoices.',
            ], 403);
        }
        
        // Check if invoice is already paid
        if ($invoice->status === 'Paid') {
            return response()->json([
                'success' => false,
                'message' => 'Invoice is already marked as paid.',
            ], 422);
        }
        
        // Check if invoice can be paid (not Cancelled or Void)
        if (in_array($invoice->status, ['Cancelled', 'Void'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot pay a cancelled or void invoice.',
            ], 422);
        }
        
        // Update invoice status to Paid and record payment time
        $invoice->update([
            'status' => 'Paid',
            'paid_at' => now(),
        ]);
        
        // Recalculate order payment status based on all invoices
        $order = $invoice->order;
        $allInvoices = $order->invoices;
        
        $totalInvoices = $allInvoices->count();
        $paidInvoices = $allInvoices->where('status', 'Paid')->count();
        
        // Determine order payment status
        if ($paidInvoices === 0) {
            $orderPaymentStatus = 'Unpaid';
        } elseif ($paidInvoices === $totalInvoices) {
            $orderPaymentStatus = 'Paid';
        } else {
            $orderPaymentStatus = 'Partially Paid';
        }
        
        // Update order payment status
        $order->update([
            'payment_status' => $orderPaymentStatus,
        ]);
        
        // Log the payment action
        if (class_exists(\App\Models\ActionLog::class)) {
            \App\Models\ActionLog::create([
                'order_id' => $order->id,
                'user_id'  => Auth::id(),
                'action'   => 'Invoice Paid',
                'details'  => "Client marked invoice {$invoice->invoice_number} as paid. Amount: \${$invoice->total_amount}",
            ]);
        }
        
        // Return response in the exact format specified
        return response()->json([
            'success' => true,
            'message' => 'Invoice marked as paid',
            'data' => [
                'invoice' => [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'status' => $invoice->status,
                    'paid_at' => $invoice->paid_at->toISOString(),
                ],
                'order' => [
                    'id' => $order->id,
                    'payment_status' => $order->payment_status,
                ],
            ],
        ]);
    }


}



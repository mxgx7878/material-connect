<?php

namespace App\Http\Controllers;

use App\Models\Orders;
use App\Models\MasterProducts;
use App\Models\SupplierOffers;
use Illuminate\Support\Facades\DB;
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
        $v = Validator::make($request->all(), [
            'po_number'        => 'nullable|unique:orders,po_number|string|max:50',
            'project_id'       => 'required|exists:projects,id',
            'delivery_address' => 'required|string',
            'delivery_lat'     => 'required|numeric',
            'delivery_long'    => 'required|numeric',
            'delivery_date'    => 'required|date',
            'delivery_time'    => 'nullable|date_format:H:i',
            'delivery_method'  => 'required|in:Other,Tipper,Agitator,Pump,Ute',
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
                'delivery_method'  => $request->delivery_method,
                'load_size'        => $request->load_size,
                'special_equipment'=> $request->special_equipment,
                // monetary fields start at zero; you’ll roll them up later
                'subtotal'         => 0,
                'fuel_levy'        => 0,
                'other_charges'    => 0,
                'gst_tax'          => 0,
                'discount'         => 0,
                'total_price'      => 0,
                'supplier_cost'    => 0,
                'customer_cost'    => 0,
                // initial states per spec
                'payment_status'   => 'Pending',
                'order_status'     => 'In-Progress',
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

            // Optionally eager-load for response
            $order->load(['items.product','items.supplier']);

            return response()->json([
                'message' => 'Order created',
                'order'   => $order
            ], 201);
        });
    }

    // Helper function | Workflow 
    private function workflow(Orders $order)
    {
        $currentWorkflow = $order->workflow;
        
        //Switch case based on current workflow
        switch ($currentWorkflow) {
            case 'Supplier Assigned':
                //Check if all suppliers have confirmed
                $allConfirmed = $order->items()->where('supplier_confirms', false)->count() === 0;
                if ($allConfirmed) {
                    //Calculate pricing
                    $subtotal = 0;
                    $supplierCost = 0;
                    foreach ($order->items as $item) {
                        $itemTotal = ($item->supplier_unit_cost * $item->quantity) + $item->supplier_delivery_cost - $item->supplier_discount;
                        $subtotal += $itemTotal;
                        $supplierCost += $itemTotal; // Assuming supplier cost is same as item total here
                    }
                    $gstTax = $subtotal * 0.1; // Assuming 10% GST
                    $totalPrice = $subtotal + $gstTax - $order->discount + $order->other_charges + $order->fuel_levy;
                    //Calculate admin margin in percentage
                    $adminMarginAmount = $totalPrice - $supplierCost;
                    $adminMarginPercentage = $supplierCost > 0 ? round(($adminMarginAmount / $supplierCost) * 100, 2) : 0;


                    //Update order pricing fields
                    $order->subtotal = $subtotal;
                    $order->gst_tax = $gstTax;
                    $order->total_price = $totalPrice;
                    $order->supplier_cost = $supplierCost;
                    $order->customer_cost = $totalPrice; // Assuming customer cost is same as total price
                    $order->admin_margin = $adminMarginPercentage;
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
    public function getMyOrders(Request $request)
    {
        $user = Auth::user();
        // dd($user);
        //Pagination & filters by project id, delivery_date, workflow,
        $perPage = (int) $request->get('per_page', 10);
        $search  = trim((string) $request->get('search', ''));
        $sort    = $request->get('sort', 'created_at');
        $dir     = $request->get('dir', 'desc');
        $delivery_date = $request->get('delivery_date', null);
        $project_id    = $request->get('project_id', null);
        $workflow      = $request->get('workflow', null);
        $query = Orders::with('project','items.product','items.supplier')
            ->where('client_id', $user->id);
        if ($search !== '') {
            $query->where('po_number', 'like', "%{$search}%");
        }
        if ($delivery_date) {
            $query->where('delivery_date', $delivery_date);
        }
        if ($project_id) {
            $query->where('project_id', $project_id);
        }
        if ($workflow) {
            $query->where('workflow', $workflow);
        }
        $allowedSorts = ['po_number','delivery_date','created_at','updated_at'];
        if (!in_array($sort, $allowedSorts, true)) $sort = 'created_at';
        $dir = strtolower($dir) === 'asc' ? 'asc' : 'desc';
        // Organize response with pagination
        $orders = $query->orderBy($sort, $dir)->paginate($perPage);
        
        $data = [
            'pagination' => [
                'per_page' => $orders->perPage(),
                'current_page' => $orders->currentPage(),
                'total_pages' => $orders->lastPage(),
                'total_items' => $orders->total(),
                'has_more_pages' => $orders->hasMorePages(),
            ],
            'data' => $orders->items()
        ];
        
        return response()->json($data);

    }
    
        

}



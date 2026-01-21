<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Orders; // Adjust if your model is named Order
use App\Models\Projects;
use App\Models\OrderItem;
use App\Services\OrderPricingService;
use App\Models\SupplierOffers;
use App\Models\User; // assuming clients are users with role=client
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Stripe\Climate\Order;
use App\Models\ActionLog;

// use Pest\Configuration\Project;

class OrderAdminController extends Controller
{
    //


    public function index(Request $request)
    {
        $perPage   = (int) $request->get('per_page', 10);
        $search    = trim((string) $request->get('search', ''));
        $clientId  = $request->get('client_id');
        $projectId = $request->get('project_id');
        $supplierId= $request->get('supplier_id');
        $workflow  = $request->get('workflow');
        $payment   = $request->get('payment_status');
        $ddFrom    = $request->get('delivery_date_from');
        $ddTo      = $request->get('delivery_date_to');
        $method    = $request->get('delivery_method');
        $repeat    = $request->get('repeat_order') ?? null;
        $hasMissing= $request->get('has_missing_supplier');
        $confirms  = $request->get('supplier_confirms'); // "true"/"false" or null
        $minTotal  = $request->get('min_total');
        $maxTotal  = $request->get('max_total');
        $sort      = $request->get('sort', 'created_at');
        $dir       = strtolower($request->get('dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        $details   = filter_var($request->get('details', false), FILTER_VALIDATE_BOOLEAN);

        if (!is_null($confirms)) {
            $confirms = $confirms === "true";
        }

        // Allowed sort columns (updated)
        $sortMap = [
            'po_number'      => 'po_number',
            'delivery_date'  => 'delivery_date',
            'created_at'     => 'created_at',
            'updated_at'     => 'updated_at',
            'total_price'    => 'total_price',          // use this instead of customer_cost/total
            'profit_amount'  => 'profit_amount',        // actual profit amount column
            'profit_before_tax'    => 'profit_before_tax',
            'profit_margin_percent'=> 'profit_margin_percent',
            'items_count'    => DB::raw('items_count'),
        ];
        if (!array_key_exists($sort, $sortMap)) {
            $sort = 'created_at';
        }

        // Base query
        $query = Orders::query()
            ->with([
                'client:id,name',
                'project:id,name',
                'items:id,order_id,supplier_id,quantity,supplier_confirms'
            ])
            ->withCount([
                'items as items_count',
                'items as unassigned_items_count' => function ($q) {
                    $q->whereNull('supplier_id');
                },
            ])
            ->withCount(['items as suppliers_count' => function ($q) {
                $q->whereNotNull('supplier_id')->select(DB::raw('COUNT(DISTINCT supplier_id)'));
            }])
            ->where('is_archived', false);

        // Text search
        if ($search !== '') {
            $query->where('po_number', 'like', "%{$search}%");
        }
        if ($clientId) {
            $query->where('client_id', $clientId);
        }
        if ($projectId) {
            $query->where('project_id', $projectId);
        }
        if ($workflow) {
            $query->where('workflow', $workflow);
        }
        if ($payment) {
            $query->where('payment_status', $payment);
        }
        if ($method) {
            $query->where('delivery_method', $method);
        }
        if ($ddFrom) {
            $query->whereDate('delivery_date', '>=', $ddFrom);
        }
        if ($ddTo) {
            $query->whereDate('delivery_date', '<=', $ddTo);
        }
        if (isset($repeat) && $repeat !== '') {
            $query->where('repeat_order', filter_var($repeat, FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
        }
        if ($supplierId) {
            $query->whereHas('items', function ($q) use ($supplierId) {
                $q->where('supplier_id', $supplierId);
            });
        }
        if ($hasMissing) {
            $query->whereHas('items', function ($q) {
                $q->whereNull('supplier_id');
            });
        }
        if (!is_null($confirms)) {
            $query->whereHas('items', function ($q) use ($confirms) {
                $q->where('supplier_confirms', $confirms);
            });
        }

        // Totals filter now uses total_price only
        if ($minTotal !== null) {
            $query->where('total_price', '>=', $minTotal);
        }
        if ($maxTotal !== null) {
            $query->where('total_price', '<=', $maxTotal);
        }

        // Sorting
        if ($sort === 'items_count') {
            $query->orderBy('items_count', $dir);
        } else {
            $query->orderBy($sortMap[$sort], $dir);
        }

        $paginator = $query->paginate($perPage);

        // Transform rows
        $data = $paginator->getCollection()->map(function (Orders $o) {
            $total  = (float)($o->total_price ?? 0);
            $profit = (float)($o->profit_amount ?? $o->profit_amount ?? 0);

            $orderInfo = null;
            if ($o->workflow === 'Supplier Missing' && $o->unassigned_items_count > 0) {
                $orderInfo = 'Supplier missing for ' . $o->unassigned_items_count . ' items';
            } elseif ($o->workflow === 'Supplier Assigned') {
                $orderInfo = 'Waiting for suppliers to confirm';
            } elseif ($o->workflow === 'Payment Requested') {
                $orderInfo = 'Awaiting client payment';
            }
          
            //claude
            return [
                'id'                       => $o->id,
                'po_number'                => $o->po_number,
                'client'                   => optional($o->client)->name,
                'project'                  => optional($o->project)->name,
                'delivery_date'            => $o->delivery_date,
                'delivery_time'            => $o->delivery_time,
                'delivery_method'          => $o->delivery_method,
                'workflow'                 => $o->workflow,
                'payment_status'           => $o->payment_status,
                'order_process'            => $o->order_process,
                'items_count'              => $o->items_count,
                'unassigned_items_count'   => $o->unassigned_items_count,
                'suppliers_count'          => $o->suppliers_count ?? 0,
                
                // NEW: Supplier costs
                'supplier_item_cost'       => round((float)($o->supplier_item_cost ?? 0), 2),
                'supplier_delivery_cost'   => round((float)($o->supplier_delivery_cost ?? 0), 2),
                'supplier_total'           => round((float)($o->supplier_item_cost ?? 0) + (float)($o->supplier_delivery_cost ?? 0), 2),
                
                // NEW: Customer costs
                'customer_item_cost'       => round((float)($o->customer_item_cost ?? 0), 2),
                'customer_delivery_cost'   => round((float)($o->customer_delivery_cost ?? 0), 2),
                
                'total_price'              => round($total, 2),
                'profit_amount'            => round($profit, 2),
                'profit_margin_percent'    => round((float)($o->profit_margin_percent ?? 0), 4),
                
                // NEW: Other charges
                'gst_tax'                  => round((float)($o->gst_tax ?? 0), 2),
                'discount'                 => round((float)($o->discount ?? 0), 2),
                'other_charges'            => round((float)($o->other_charges ?? 0), 2),
                
                'order_info'               => $orderInfo,
                'repeat_order'             => $o->repeat_order,
                'created_at'               => $o->created_at,
                'updated_at'               => $o->updated_at,
            ];
        });

        // Metrics
        $base = Orders::query();
        $metrics = [
            'total_orders_count'     => (clone $base)->count(),
            'supplier_missing_count' => (clone $base)->where('workflow', 'Supplier Missing')->count(),
            'supplier_assigned_count'=> (clone $base)->where('workflow', 'Supplier Assigned')->count(),
            'awaiting_payment_count' => (clone $base)->where('workflow', 'Payment Requested')->count(),
            'delivered_count'        => (clone $base)->where('workflow', 'Delivered')->count(),
        ];

        $response = [
            'data' => $data,
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
            $response['filters'] = [
                'clients'          => User::query()->where('role', 'client')->select('id','name','profile_image')->orderBy('name')->get(),
                'suppliers'        => User::where('role','supplier')->select('id','name','profile_image')->orderBy('name')->get(),
                'projects'         => Projects::query()->select('id','name')->orderBy('name')->get(),
                'workflows'        => ['Requested','Supplier Missing','Supplier Assigned','Payment Requested','On Hold','Delivered'],
                'payment_statuses' => ['Pending','Requested','Paid','Partially Paid','Partial Refunded','Refunded'],
                'delivery_methods' => ['Other','Tipper','Agitator','Pump','Ute'],
            ];
        }

        return response()->json($response);
    }



    public function show(Orders $order)
    {
        // dd($order->delivery_method);
        $order->load(['client:id,name', 'project:id,name', 'items.product:id,product_name']);
        
        $deliveryLat = $order->delivery_lat ?? null;   // adjust if different
        $deliveryLng = $order->delivery_long ?? null;
        // dd($order->project->id);
        $filters['projects'] = Projects::where('added_by', $order->client_id)->where('id', '!=', $order->project->id)->get();
        
        $logs = ActionLog::where('order_id', $order->id)->orderBy('created_at', 'desc')->get();

        $payload = [
            //Order Details For Display (Not Changeable)
            'id' => $order->id,
            'po_number' => $order->po_number,
            'client' => optional($order->client)->name,
            'project' => optional($order->project)->name,
            'delivery_address' => $order->delivery_address,
            'delivery_lat'=>$order->delivery_lat,
            'delivery_long'=> $order->delivery_long,
            'delivery_date' => $order->delivery_date,
            'delivery_time' => $order->delivery_time,
            'delivery_window'=> $order->delivery_window,
            'delivery_method' => $order->delivery_method,
            'load_size'=> $order->load_size,
            'special_equipment'=> $order->special_equipment,
            'repeat_order'=> (int) $order->repeat_order,
            'order_process'=> $order->order_process,
                //status
            'workflow' => $order->workflow,
            'payment_status' => $order->payment_status,
            'order_status' => $order->order_status,

            'gst_tax'=> round((float)$order->gst_tax),
            'discount'=>round((float)$order->discount),
            'other_charges'=>round((float)$order->other_charges),
            'total_price'=>round((float)$order->total_price),
            'customer_item_cost'=>round((float)$order->customer_item_cost),
            'customer_delivery_cost'=>round((float)$order->customer_delivery_cost),
            'supplier_item_cost'=>round((float)$order->supplier_item_cost),
            'supplier_delivery_cost'=>round((float)$order->supplier_delivery_cost),
            'profit_margin_percent'=>(float)$order->profit_margin_percent,
            'profit_amount'=>round((float)$order->profit_amount),
            'items' => [],
            'filters'=>$filters,
            'logs'=> $logs,
        ];

        // If not Supplier Missing, return items without eligibility calc
        if ($order->workflow !== 'Supplier Missing') {
            
            foreach ($order->items as $it) {
            //    dd($it->is_quoted);
                $payload['items'][] = [
                    'id' => $it->id,
                    'product_id' => $it->product_id,
                    'product_name' => optional($it->product)->product_name,
                    'quantity' => $it->quantity,
                    'supplier' => $it->supplier,
                    'choosen_offer_id' => $it->choosen_offer_id ?? null,
                    'supplier_confirms' => $it->supplier_confirms,
                    'supplier_unit_cost' => $it->supplier_unit_cost,
                    'delivery_cost' => $it->delivery_cost,
                    'supplier_discount' => $it->supplier_discount,
                    'delivery_type'=>$it->delivery_type,
                    'is_quoted'         => (int) $it->is_quoted,
                    'is_paid' => (int) $it->is_paid,
                    'quoted_price'=>$it->quoted_price,
                    'eligible_suppliers' => [],
                ];
            }
            
            return response()->json(['data' => $payload]);
        }
        // dd('ues');

        $canComputeDistance = is_numeric($deliveryLat) && is_numeric($deliveryLng);

        // preload offers for all products on this order
        $masterIds = $order->items->pluck('product_id')->filter()->unique()->values();

        $offersByProduct = SupplierOffers::query()
            ->whereIn('master_product_id', $masterIds)
            ->with(['supplier:id,name,role,delivery_zones'])
            ->get()
            ->groupBy('master_product_id');

      
     
        foreach ($order->items as $it) {
            // already assigned → just echo item info
            if (!is_null($it->supplier_id)) {
                
                // dd($it);
                $payload['items'][] = [
                    'id' => $it->id,
                    'product_id' => $it->product_id,
                    'product_name' => optional($it->product)->product_name,
                    'quantity' => $it->quantity,
                    'supplier' => $it->supplier()->select('id','name','profile_image','delivery_zones')->first(),
                    'choosen_offer_id' => $it->choosen_offer_id ?? null,
                    'supplier_confirms' => $it->supplier_confirms,
                    'supplier_unit_cost' => $it->supplier_unit_cost,
                    'delivery_cost' => $it->delivery_cost,
                    'supplier_discount' => $it->supplier_discount,
                    'delivery_type'=>$it->delivery_type,
                    'is_quoted'         => (int) $it->is_quoted,
                    'is_paid' => (int) $it->is_paid,
                    'quoted_price'=>$it->quoted_price,
                    'eligible_suppliers' => [],
                ];
                continue;
            }
            // unassigned → build eligible_suppliers
            $eligible = [];
            $productOffers = $offersByProduct->get($it->product_id, collect());

            foreach ($productOffers as $offer) {
                $supplier = $offer->supplier; // users table row
                if (!$supplier) continue;
                if (strtolower((string)$supplier->role) !== 'supplier') continue;

                if (empty($supplier->delivery_zones)) continue; // must not be null/empty
                $zones = is_array($supplier->delivery_zones)
                    ? $supplier->delivery_zones
                    : json_decode($supplier->delivery_zones,true);
                    if (empty($zones)) continue;

                
                $distance = null;
                // dd($canComputeDistance);
                // $canComputeDistance = true;
                if ($canComputeDistance) {
                    // dd($zones, $deliveryLat, $deliveryLng);
                    $distance = $this->nearestZoneDistanceKm($zones, (float)$deliveryLat, (float)$deliveryLng);
                }

                $eligible[] = [
                    'supplier_id' => (int) $supplier->id,
                    'name'=> (string) $supplier->name,
                    'offer_id'    => (int) $offer->id,
                    'unit_cost' => (float) $offer->price,
                    'distance'    => is_null($distance) ? null : round($distance, 3),
                ];
            }

            usort($eligible, function ($a, $b) {
                if ($a['distance'] === null && $b['distance'] === null) return 0;
                if ($a['distance'] === null) return 1;
                if ($b['distance'] === null) return -1;
                return $a['distance'] <=> $b['distance'];
            });

            $payload['items'][] = [
                'id' => $it->id,
                'product_id' => $it->product_id,
                'product_name' => optional($it->product)->product_name,
                'quantity' => $it->quantity,
                'supplier_id' => $it->supplier_id,
                'eligible_suppliers' => $eligible,
            ];
        }

      

        return response()->json(['data' => $payload]);
    }

    public function assignSupplier(Request $request)
    {
        $v = Validator::make($request->all(), [
            'order_id' => ['required','integer','min:1'],
            'item_id'  => ['required','integer','min:1'],
            'supplier' => ['required','integer','min:1'],     // supplier_id
            'offer_id' => ['sometimes','integer','min:1'],    // optional
        ]);
        if ($v->fails()) return response()->json(['error' => $v->errors()], 422);

        $orderId    = (int) $request->order_id;
        $itemId     = (int) $request->item_id;
        $supplierId = (int) $request->supplier;
        $offerId    = $request->filled('offer_id') ? (int) $request->offer_id : null;

        return DB::transaction(function () use ($orderId, $itemId, $supplierId, $offerId) {

            /** @var Orders $order */
            $order = Orders::with('items')->lockForUpdate()->findOrFail($orderId);

            /** @var OrderItem $item */
            $item = $order->items()->whereKey($itemId)->lockForUpdate()->firstOrFail();
            if (!$item->product_id) {
                return response()->json(['error' => ['item' => ['Item has no product_id.']]], 422);
            }

            // supplier must exist and be role=supplier
            $supplier = User::find($supplierId);
            if (!$supplier || strtolower((string)$supplier->role) !== 'supplier') {
                return response()->json(['error' => ['supplier' => ['Invalid supplier.']]], 422);
            }

            // pick matching offer by supplier+product (or the explicit one)
            $offerQ = SupplierOffers::query()
                ->where('supplier_id', $supplierId)
                ->where('master_product_id', $item->product_id)
                ->where('status','Approved')
                ->whereIn('availability_status',['In Stock','Limited']);
            if ($offerId) $offerQ->whereKey($offerId);

            $offer = $offerQ->orderBy('price')->first();
            if (!$offer) {
                return response()->json(['error' => ['offer' => ['No offer for this supplier and product.']]], 422);
            }

            $unitCost         = (float)($offer->price ?? $offer->price ?? 0);
            // $supplierDiscount = (float)($offer->supplier_discount ?? $offer->discount ?? 0);

            // assign. delivery fields stay null. no confirms flag.
            $item->forceFill([
                'supplier_id'            => $supplierId,
                'choosen_offer_id'       => $offer->id,
                'supplier_unit_cost'     => round($unitCost, 2),
                'supplier_discount'      => 0.00,
                'supplier_delivery_cost' => 0.00,
                'delivery_type'          => null,
                'delivery_cost'          => 0.00,
            ])->saveOrFail();
            ActionLog::create([
                'action' => 'Supplier Assigned to Order',
                'details' => "Supplier {$supplier->contact_name} assigned to item ID {$itemId}",
                'order_id' => $orderId,
                'user_id' => Auth::id(),
            ]);

            // if all items now have a supplier -> set workflow
            $unassignedCount = $order->items()->whereNull('supplier_id')->count();
            if ($unassignedCount === 0 && $order->workflow !== 'Supplier Assigned') {
                $order->workflow = 'Supplier Assigned';
                $order->save();
            }

            // recalc customer totals
            $order->load('items');
            OrderPricingService::recalcCustomer($order, null, null, true);

            return response()->json([
                'message' => 'Supplier assigned.',
                'order' => [
                    'id'                     => $order->id,
                    'workflow'               => $order->workflow,
                    'total_price'            => $order->total_price,
                    'profit_amount'          => $order->profit_amount,
                    'profit_margin_percent'  => $order->profit_margin_percent,
                ],
                'item' => [
                    'id'                     => $item->id,
                    'product_id'             => $item->product_id,
                    'supplier_id'            => $item->supplier_id,
                    'choosen_offer_id'       => $item->choosen_offer_id,
                    'supplier_unit_cost'     => $item->supplier_unit_cost,
                    'supplier_discount'      => $item->supplier_discount,
                    'supplier_delivery_cost' => $item->supplier_delivery_cost, // null
                    'delivery_type'          => $item->delivery_type,          // null
                    'delivery_cost'          => $item->delivery_cost,          // null
                ],
                'offer' => [
                    'id'                => $offer->id,
                    'supplier_id'       => $offer->supplier_id,
                    'master_product_id' => $offer->master_product_id,
                ],
            ]);
        });
    }

    public function supplierPaidStatus(Request $request, Orders $order, OrderItem $orderItem)
    {
        // only admins hit this via middleware
        $v = Validator::make($request->all(), [
            'is_paid'         => ['sometimes','required','boolean'],
            
        ]);
        if ($v->fails()) {
            return response()->json(['error' => $v->errors()], 422);
        }

        $item = OrderItem::find((int)$orderItem->id);
        if (!$item) {
            return response()->json(['error' => 'Item not found'], 404);
        }

        $isPaid = filter_var($request->is_paid, FILTER_VALIDATE_BOOLEAN);

        $item->is_paid = $isPaid ? 1 : 0;
        // $item->supplier_status = $isPaid ? 'Paid' : 'Unpaid';
        $item->save();
        ActionLog::create([
            'action' => 'Order Item Marked as Paid',
            'details' => "Order Item ID {$orderItem->id} in Order ID {$orderItem->order_id} marked as paid",
            'order_id' => $orderItem->order_id,
            'user_id' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => $isPaid
                ? 'Item marked as paid and supplier status set to Paid'
                : 'Item marked as unpaid and supplier status set to Unpaid',
            'item' => $item,
        ]);

    }

    public function adminUpdate(Request $request, Orders $order)
    {
        // only admins hit this via middleware
        $v = Validator::make($request->all(), [
            'discount'        => ['sometimes','required','numeric','min:0'],
            'is_paid'         => ['sometimes','required','boleean'],
            'item_id'         => ['sometimes','required','numeric','min:1'],  
        ]);

        if ($v->fails()) {
            return response()->json(['error' => $v->errors()], 422);
        }
        //for discount
        

        $dirty = [];

        //for discount
        if ($request->has('discount')) {
            $dirty['discount'] = (float)$request->discount;
            // dd($request->discount);
            // Apply updates
            $order->update($dirty);

            // Refresh minimal payload
            $order->load(['project:id,name','items:id,order_id,product_id,supplier_id,supplier_delivery_date']);
            // Recalculate customer-facing totals only
            OrderPricingService::recalcCustomer($order, null, null, true);

            return response()->json([
                'success' => true,
                'message' => 'Order updated by admin',
                'order'   => $order,
            ]);
        }

        if ($request->has('item_id') && $request->has('is_paid')) {
            $item = OrderItem::find((int)$request->item_id);
            if (!$item) {
                return response()->json(['error' => 'Item not found'], 404);
            }

            $isPaid = filter_var($request->is_paid, FILTER_VALIDATE_BOOLEAN);

            $item->is_paid = $isPaid ? 1 : 0;
            $item->supplier_status = $isPaid ? 'Paid' : 'Unpaid';
            $item->save();

            return response()->json([
                'success' => true,
                'message' => $isPaid
                    ? 'Item marked as paid and supplier status set to Paid'
                    : 'Item marked as unpaid and supplier status set to Unpaid',
                'item' => $item,
            ]);
        }
        
    }


    /** Decode users.delivery_zones JSON to [['lat'=>..,'lng'=>..,'radius_km'=>?...],...] */
    private function decodeZones($json): array
    {
        try {
            $raw = is_array($json) ? $json : json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            return [];
        }
        $zones = [];
        foreach ((array)$raw as $z) {
            $lat = $z['lat'] ?? $z['latitude'] ?? null;
            $lng = $z['lng'] ?? $z['longitude'] ?? null;
            $rkm = $z['radius_km'] ?? $z['radius'] ?? null;
            if (is_numeric($lat) && is_numeric($lng)) {
                $zones[] = ['lat'=>(float)$lat,'lng'=>(float)$lng,'radius_km'=>is_numeric($rkm)?(float)$rkm:null];
            }
        }
        return $zones;
    }

    /** Nearest zone’s center distance in km */
    private function nearestZoneDistanceKm(array $zones, float $lat, float $lng): ?float
    {
        // dd($zones, $lat , $lng);
        $min = null;
        foreach ($zones as $z) {
            $d = $this->haversineKm($lat, $lng, $z['lat'], $z['long']);
            if ($min === null || $d < $min) $min = $d;
        }
        return $min;
    }

    /** Haversine distance in km */
    private function haversineKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $R = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat/2)**2 + cos(deg2rad($lat1))*cos(deg2rad($lat2))*sin($dLon/2)**2;
        return 2 * $R * atan2(sqrt($a), sqrt(1 - $a));
    }



    public function setItemQuotedPrice(Request $request, int $orderId, int $itemId)
    {
       
        $data = $request->validate([
            'quoted_price' => 'nullable|numeric|min:0', // null clears quote
        ]);

        $check = Orders::where('id',$orderId)->where('payment_status','pending')->first();

        if(!$check)
        {
            return response()->json([
                'error'=>"Can not update it's pricing now",
            ], 409);
        }

        return DB::transaction(function () use ($orderId, $itemId, $data) {
            // Lock the item row and guarantee it belongs to the order
            /** @var OrderItem $item */
            $item = OrderItem::where('order_id', $orderId)
                ->lockForUpdate()
                ->findOrFail($itemId);

            // Important: do not coerce null to 0.00
            $quoted = array_key_exists('quoted_price', $data) ? $data['quoted_price'] : null;

            // Assign and save
            $item->forceFill([
                'quoted_price' => $quoted,                 // stays null if clearing
                'is_quoted'    => is_null($quoted) ? 0 : 1,
            ]);

            $item->saveOrFail();

            // Recalculate customer-facing totals only
            $order = Orders::with('items')->findOrFail($orderId);
            OrderPricingService::recalcCustomer($order, null, null, true);

            return response()->json([
                'message' => 'Quoted price saved. Customer totals recalculated.',
                'order' => [
                    'id'                     => $order->id,
                    'customer_item_cost'     => $order->customer_item_cost,
                    'customer_delivery_cost' => $order->customer_delivery_cost,
                    'gst_tax'                => $order->gst_tax,
                    'total_price'            => $order->total_price,
                    'profit_amount'          => $order->profit_amount,
                    'profit_margin_percent'  => $order->profit_margin_percent,
                ],
                'item' => [
                    'id'           => $item->id,
                    'is_quoted'    => (int)$item->is_quoted,
                    'quoted_price' => $item->quoted_price, // null if cleared
                ],
            ]);
        });
    }


    public function updateOrderPricingAdmin(Request $request, OrderItem $orderItem)
    {
        $v = Validator::make($request->all(), [
            'supplier_unit_cost' => 'sometimes|required|numeric|min:0',
            'supplier_discount' => 'sometimes|required|numeric|min:0',
            'supplier_delivery_date' => 'sometimes|required|date',
            'supplier_confirms' => 'sometimes|required|boolean',
            'delivery_cost' => 'sometimes|required|numeric|min:0',
            'delivery_type'=> 'required|in:Included,Supplier,ThirdParty,Fleet,None ',
            'supplier_notes' => 'sometimes|nullable|string|max:500',
            'quantity' => 'sometimes|nullable|numeric|min:1'
        ]);

        if ($v->fails()) {
            return response()->json(['error' => $v->errors()], 422);
        }

        // Check if the authenticated user is the supplier for this order item

        if(!in_array($orderItem->order->workflow, ['Supplier Assigned', 'Requested', 'Payment Requested'])) {  //, 'Payment Requested', 'On Hold', 'Delivered'
            return response()->json([
                'message' => 'Cannot update order item now as the order is in '.$orderItem->order->workflow.' status'
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
            
            if($request->has('quantity')) {
                $updateData['quantity'] = $request->quantity;
            }

            // Update the order item
            $orderItem->update($updateData);

            // If supplier confirmed the order, trigger workflow check
            if ($request->has('supplier_confirms') && $request->supplier_confirms) {
                $this->workflow($orderItem->order);
            }

            DB::commit();
            ActionLog::create([
                'action' => 'Order Pricing Updated',
                'details' => "Updated pricing for Order Item ID {$orderItem->id} in Order ID {$orderItem->order_id}",
                'order_id' => $orderItem->order_id,
                'user_id' => Auth::id(),
            ]);

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
                $order->order_status = "Confirmed";
                // dd($customer_item_cost, $customer_delivery_cost, $supplier_item_cost, $supplier_delivery_cost, $profit_margin_percent, $profit_before_tax);
                $order->save();

                break;
        }
    
    }

    public function updateOrderStatus(Request $request,Order $order){
        $v = Validator::make($request->all(), [
            'order_status' => ['required','string'],
        ]);
        if ($v->fails()) return response()->json(['error' => $v->errors()], 422);
        
        $order->order_status = $request->order_status;
        $order->save();
        ActionLog::create([
            'action' => 'Order Status Updated',
            'details' => "Order ID {$order->id} status changed to {$order->order_status}",
            'order_id' => $order->id,
            'user_id' => Auth::id(),
        ]);

        // FIXED: Remove the extra "->order"
        return response()->json([
            'order_status' => $order->order_status,
            'message' => 'Order Status set to ' . $order->order_status
        ], 200);
    }

    public function updatePaymentStatus(Request $request,Orders $order){
        $v = Validator::make($request->all(), [
            'payment_status' => ['required','string'],
        ]);
        if ($v->fails()) return response()->json(['error' => $v->errors()], 422);
       
        $order->payment_status = $request->payment_status;
        $order->save();
        ActionLog::create([
            'action' => 'Order Payment Status Updated',
            'details' => "Payment status for Order ID {$order->id} updated to {$order->payment_status}",
            'order_id' => $order->id,
            'user_id' => Auth::id(),
        ]);

        return response()->json([
            'payment_status' => $order->payment_status,
            'message' => 'Order Payment Status set to ' . $order->payment_status
        ], 200);
        
    }

    public function archiveOrder(Orders $order)
    {

        $order->is_archived = 1;
        $order->archived_by = Auth::id();
        $order->save();
        ActionLog::create([
            'action' => 'Order Archived',
            'details' => "Order ID {$order->id} archived by admin",
            'order_id' => $order->id,
            'user_id' => Auth::id(),
        ]);
        return response()->json([
            'success' => true,
            'message' => 'Order Deleted (Archived) successfully',
            'order'   => $order->only(['id','is_archived']),
        ]);
    }

    public function getArchive(Request $request) {
        $ordersPerPage = $request->get('orders_per_page', 10);
        $usersPerPage = $request->get('users_per_page', 10);
        $projectsPerPage = $request->get('projects_per_page', 10);

        // Fetch archived orders with relationships
        $orders = Orders::where('is_archived', 1)
                    ->with(['project:id,name', 'client:id,name'])
                    ->latest('updated_at')
                    ->paginate($ordersPerPage);

        // Fetch deleted users
        $users = User::where('isDeleted', 1)
                    ->latest('updated_at')
                    ->paginate($usersPerPage);

        // Fetch archived projects with client
        $projects = Projects::where('is_archived', 1)
                    ->with(['added_by:id,name'])
                    ->latest('updated_at')
                    ->paginate($projectsPerPage);

        return response()->json([
            'success' => true,
            'data' => [
                'orders' => [
                    'data' => $orders->items(),
                    'current_page' => $orders->currentPage(),
                    'per_page' => $orders->perPage(),
                    'total' => $orders->total(),
                    'last_page' => $orders->lastPage(),
                ],
                'users' => [
                    'data' => $users->items(),
                    'current_page' => $users->currentPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                    'last_page' => $users->lastPage(),
                ],
                'projects' => [
                    'data' => $projects->items(),
                    'current_page' => $projects->currentPage(),
                    'per_page' => $projects->perPage(),
                    'total' => $projects->total(),
                    'last_page' => $projects->lastPage(),
                ],
            ]
        ]);
    }
}

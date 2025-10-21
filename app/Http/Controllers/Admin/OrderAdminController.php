<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Orders; // Adjust if your model is named Order
use App\Models\Projects;
use App\Models\OrderItems;
use App\Models\SupplierOffers;
use App\Models\User; // assuming clients are users with role=client
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Pest\Configuration\Project;

class OrderAdminController extends Controller
{
    //


    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 10);
        $search = trim((string) $request->get('search', ''));
        $clientId = $request->get('client_id');
        $projectId = $request->get('project_id');
        $supplierId= $request->get('supplier_id');
        $workflow = $request->get('workflow');
        $payment = $request->get('payment_status');
        $ddFrom = $request->get('delivery_date_from');
        $ddTo = $request->get('delivery_date_to');
        $method = $request->get('delivery_method');
        $repeat = $request->get('repeat_order') ?? null;
        $hasMissing= $request->get('has_missing_supplier');
        $confirms = $request->get('supplier_confirms'); // 0/1
        $minTotal = $request->get('min_total');
        $maxTotal = $request->get('max_total');
        $sort = $request->get('sort', 'created_at');
        $dir = strtolower($request->get('dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        $details = filter_var($request->get('details', false), FILTER_VALIDATE_BOOLEAN);
        if(!is_null($confirms))
        {
            if($confirms=="true")
            {
                $confirms=true;
            } else {
                $confirms = false;
            }
        }

        // Allowed sort columns
        $sortMap = [
        'po_number' => 'po_number',
        'delivery_date' => 'delivery_date',
        'created_at' => 'created_at',
        'updated_at' => 'updated_at',
        // pricing related (ensure these fields exist in your Orders table)
        'total' => 'total', // alias of customer_cost if you prefer
        'customer_cost' => 'customer_cost',
        'supplier_cost' => 'supplier_cost',
        'admin_margin' => DB::raw('(COALESCE(customer_cost,0) - COALESCE(supplier_cost,0))'),
        'items_count' => DB::raw('items_count'), // will order by appended attribute; fallback to created_at if driver rejects
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
        // derive counts in SQL for efficiency
        ->withCount([
            'items as items_count',
            'items as unassigned_items_count' => function ($q) {
                $q->whereNull('supplier_id');
            },
        ])
        // Optional: expose suppliers_count per order
        ->withCount(['items as suppliers_count' => function ($q) {
            $q->whereNotNull('supplier_id')->select(DB::raw('COUNT(DISTINCT supplier_id)'));
        }]);

        
        // Text search on PO number
        if ($search !== '') {
            $query->where('po_number', 'like', "%{$search}%");
        }
        if ($clientId) {
            dd('here');
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

        if (isset($repeat) && $repeat !== '' && $repeat !== null && ($repeat === true || $repeat === false)) {
            $query->where('repeat_order', $repeat ? 1 : 0);
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
        
       // dd($search, $clientId, $projectId,$workflow,$payment,$method,$ddFrom,$ddTo,$repeat,$hasMissing,$supplierId);
        if (!is_null($confirms)) {
            $query->whereHas('items', function ($q) use ($confirms) {
                $q->where('supplier_confirms', $confirms);
            });
        }
        if ($minTotal !== null) {
            $query->where(function ($q) use ($minTotal) {
                // prefer customer_cost/total
                $q->where('customer_cost', '>=', $minTotal)
                ->orWhere('total', '>=', $minTotal);
            });
        }
        if ($maxTotal !== null) {
            $query->where(function ($q) use ($maxTotal) {
                $q->where('customer_cost', '<=', $maxTotal)
                ->orWhere('total', '<=', $maxTotal);
            });
        }
        // Sorting
        if ($sort === 'admin_margin') {
            $query->orderByRaw('(COALESCE(customer_cost,0) - COALESCE(supplier_cost,0)) ' . $dir);
        } elseif ($sort === 'items_count') {
            $query->orderBy('items_count', $dir);
        } else {
            $query->orderBy($sortMap[$sort], $dir);
        }
        $paginator = $query->paginate($perPage);
        // Transform rows for the list table
        $data = $paginator->getCollection()->map(function (Orders $o) {
        // Compute pricing view values. Adjust field names if different in your schema.
            $customer = $o->customer_cost ?? $o->total ?? 0;
            $supplier = $o->supplier_cost ?? 0;
            $margin = ($o->admin_margin ?? null) !== null
            ? $o->admin_margin
            : ($customer - $supplier);


            // Optional info text similar to client list
            $orderInfo = null;
            if ($o->workflow === 'Supplier Missing' && $o->unassigned_items_count > 0) {
                $orderInfo = 'Supplier missing for ' . $o->unassigned_items_count . ' items';
            } elseif ($o->workflow === 'Supplier Assigned') {
                $orderInfo = 'Waiting for suppliers to confirm';
            } elseif ($o->workflow === 'Payment Requested') {
                $orderInfo = 'Awaiting client payment';
            }


            return [
            'id' => $o->id,
            'po_number' => $o->po_number,
            'client' => optional($o->client)->name,
            'project' => optional($o->project)->name,
            'delivery_date' => $o->delivery_date,
            'delivery_time' => $o->delivery_time,
            'workflow' => $o->workflow,
            'payment_status' => $o->payment_status,
            'items_count' => $o->items_count,
            'unassigned_items_count' => $o->unassigned_items_count,
            'supplier_cost' => round((float)$supplier, 2),
            'customer_cost' => round((float)$customer, 2),
            'admin_margin' => round((float)$margin, 2),
            'order_info' => $orderInfo,
            'created_at' => $o->created_at,
            'updated_at' => $o->updated_at,
            ];
        });
        // Metrics
        $base = Orders::query();
        $metrics = [
        'total_orders_count' => (clone $base)->count(),
        'supplier_missing_count' => (clone $base)->where('workflow', 'Supplier Missing')->count(),
        'supplier_assigned_count'=> (clone $base)->where('workflow', 'Supplier Assigned')->count(),
        'awaiting_payment_count' => (clone $base)->where('workflow', 'Payment Requested')->count(),
        'delivered_count' => (clone $base)->where('workflow', 'Delivered')->count(),
        ];
        $response = [
            'data' => $data,
            'pagination' => [
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'total_pages' => $paginator->lastPage(),
                'total_items' => $paginator->total(),
                'has_more_pages'=> $paginator->hasMorePages(),
            ],
            'metrics' => $metrics,
        ];
        if ($details) {
            $response['filters'] = [
                'clients' => User::query()->where('role', 'client')->select('id','name','profile_image')->orderBy('name')->get(),
                'suppliers' => User::where('role','supplier')->select('id','name','profile_image')->orderBy('name')->get(),
                'projects' => Projects::query()->select('id','name')->orderBy('name')->get(),
                'workflows' => ['Requested','Supplier Missing','Supplier Assigned','Payment Requested','On Hold','Delivered'],
                'payment_statuses' => ['Pending','Requested','Paid','Partially Paid','Partial Refunded','Refunded'],
                'delivery_methods' => ['Other','Tipper','Agitator','Pump','Ute'],
            ];
        }
        return response()->json($response);
    }


    public function show(Orders $order)
    {
        $order->load(['client:id,name', 'project:id,name', 'items.product:id,product_name']);
     
        $deliveryLat = $order->delivery_lat ?? null;   // adjust if different
        $deliveryLng = $order->delivery_long ?? null;
        // dd($order->project->id);
        $filters['projects'] = Projects::where('added_by', $order->client_id)->where('id', '!=', $order->project->id)->get();
        

        $payload = [
            'id' => $order->id,
            'po_number' => $order->po_number,
            'client' => optional($order->client)->name,
            'project' => optional($order->project)->name,
            'delivery_address' => $order->delivery_address,
            'delivery_lat'=>$order->delivery_lat,
            'delivery_long'=> $order->delivery_long,
            'delivery_date' => $order->delivery_date,
            'delivery_time' => $order->delivery_time,
            'workflow' => $order->workflow,
            'payment_status' => $order->payment_status,
            'supplier_cost' => round((float)$order->supplier_cost, 2),
            'customer_cost' => round((float)$order->customer_cost, 2),
            'admin_margin' => round((float)$order->admin_margin, 2),
            'gst_tax'=> round((float)$order->gst_tax),
            'subtotal'=>round((float)$order->subtotal),
            'discount'=>round((float)$order->discount),
            'fuel_levy'=>round((float)$order->fuel_levy),
            'other_charges'=>round((float)$order->other_charges),
            'items' => [],
            'filters'=>$filters
        ];

        // If not Supplier Missing, return items without eligibility calc
        if ($order->workflow !== 'Supplier Missing') {
            foreach ($order->items as $it) {
                $payload['items'][] = [
                    'id' => $it->id,
                    'product_id' => $it->product_id,
                    'product_name' => optional($it->product)->product_name,
                    'quantity' => $it->quantity,
                    'supplier' => $it->supplier,
                    'choosen_offer_id' => $it->choosen_offer_id ?? null,
                    'supplier_confirms' => $it->supplier_confirms,
                    'supplier_unit_cost' => $it->supplier_unit_cost,
                    'supplier_delivery_cost' => $it->supplier_delivery_cost,
                    'supplier_discount' => $it->supplier_discount,
                    'eligible_suppliers' => [],
                ];
            }
            return response()->json(['data' => $payload]);
        }

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
                $payload['items'][] = [
                    'id' => $it->id,
                    'product_id' => $it->product_id,
                    'product_name' => optional($it->product)->product_name,
                    'quantity' => $it->quantity,
                    'supplier' => $it->supplier()->select('id','name','profile_image','delivery_zones')->first(),
                    'choosen_offer_id' => $it->choosen_offer_id ?? null,
                    'supplier_confirms' => $it->supplier_confirms,
                    'supplier_unit_cost' => $it->supplier_unit_cost,
                    'supplier_delivery_cost' => $it->supplier_delivery_cost,
                    'supplier_discount' => $it->supplier_discount,
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
                    : $this->decodeZones($supplier->delivery_zones);
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

    public function adminUpdate(Request $request, Orders $order)
    {
        // only admins hit this via middleware
        $v = Validator::make($request->all(), [
            'project_id'      => ['sometimes','required','exists:projects,id'],
            'delivery_date'   => ['sometimes','required','date'],
            'delivery_method' => ['sometimes','required', Rule::in(Orders::DELIVERY_METHOD)],
            // "special instruction" lives on order as special_notes
            'special_notes'   => ['sometimes','nullable','string','max:1000'],
            'discount'        => ['sometimes','required','numeric','min:0'],
        ]);

        if ($v->fails()) {
            return response()->json(['error' => $v->errors()], 422);
        }

        $dirty = [];

        if ($request->has('project_id')) {
            $dirty['project_id'] = (int)$request->project_id;
        }
        if ($request->has('delivery_date')) {
            $dirty['delivery_date'] = $request->delivery_date;
        }
        if ($request->has('delivery_method')) {
            $dirty['delivery_method'] = $request->delivery_method;
        }
        if ($request->has('special_notes')) {
            $dirty['special_notes'] = $request->special_notes;
        }
        if ($request->has('discount')) {
            $dirty['discount'] = (float)$request->discount;

            // Recompute total with the new discount using existing breakdown
            $subtotal     = (float)$order->subtotal;
            $gst          = (float)$order->gst_tax;
            $fuelLevy     = (float)$order->fuel_levy;
            $otherCharges = (float)$order->other_charges;
            $dirty['total_price'] = $subtotal + $gst + $fuelLevy + $otherCharges - $dirty['discount'];
            // leave supplier_cost/admin_margin unchanged
        }

        // Apply updates
        $order->update($dirty);

        // If delivery_date changed, align supplier delivery dates on items
        if (array_key_exists('delivery_date', $dirty)) {
            $order->items()->update(['supplier_delivery_date' => $order->delivery_date]);
        }

        // Refresh minimal payload
        $order->load(['project:id,name','items:id,order_id,product_id,supplier_id,supplier_delivery_date']);

        return response()->json([
            'success' => true,
            'message' => 'Order updated by admin',
            'order'   => $order,
        ]);
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
}

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
use App\Models\OrderItemDelivery;

// use Pest\Configuration\Project;

class OrderAdminController extends Controller
{
    //


    // public function index(Request $request)
    // {
    //     $perPage   = (int) $request->get('per_page', 10);
    //     $search    = trim((string) $request->get('search', ''));
    //     $clientId  = $request->get('client_id');
    //     $projectId = $request->get('project_id');
    //     $supplierId= $request->get('supplier_id');
    //     $workflow  = $request->get('workflow');
    //     $payment   = $request->get('payment_status');
    //     $ddFrom    = $request->get('delivery_date_from');
    //     $ddTo      = $request->get('delivery_date_to');
    //     $method    = $request->get('delivery_method');
    //     $repeat    = $request->get('repeat_order') ?? null;
    //     $hasMissing= $request->get('has_missing_supplier');
    //     $confirms  = $request->get('supplier_confirms'); // "true"/"false" or null
    //     $minTotal  = $request->get('min_total');
    //     $maxTotal  = $request->get('max_total');
    //     $sort      = $request->get('sort', 'created_at');
    //     $dir       = strtolower($request->get('dir', 'desc')) === 'asc' ? 'asc' : 'desc';
    //     $details   = filter_var($request->get('details', false), FILTER_VALIDATE_BOOLEAN);

    //     if (!is_null($confirms)) {
    //         $confirms = $confirms === "true";
    //     }

    //     // Allowed sort columns (updated)
    //     $sortMap = [
    //         'po_number'      => 'po_number',
    //         'delivery_date'  => 'delivery_date',
    //         'created_at'     => 'created_at',
    //         'updated_at'     => 'updated_at',
    //         'total_price'    => 'total_price',          // use this instead of customer_cost/total
    //         'profit_amount'  => 'profit_amount',        // actual profit amount column
    //         'profit_before_tax'    => 'profit_before_tax',
    //         'profit_margin_percent'=> 'profit_margin_percent',
    //         'items_count'    => DB::raw('items_count'),
    //     ];
    //     if (!array_key_exists($sort, $sortMap)) {
    //         $sort = 'created_at';
    //     }

    //     // Base query
    //     $query = Orders::query()
    //         ->with([
    //             'client:id,name',
    //             'project:id,name',
    //             'items:id,order_id,supplier_id,quantity,supplier_confirms'
    //         ])
    //         ->withCount([
    //             'items as items_count',
    //             'items as unassigned_items_count' => function ($q) {
    //                 $q->whereNull('supplier_id');
    //             },
    //         ])
    //         ->withCount(['items as suppliers_count' => function ($q) {
    //             $q->whereNotNull('supplier_id')->select(DB::raw('COUNT(DISTINCT supplier_id)'));
    //         }])
    //         ->where('is_archived', false);

    //     // Text search
    //     if ($search !== '') {
    //         $query->where('po_number', 'like', "%{$search}%");
    //     }
    //     if ($clientId) {
    //         $query->where('client_id', $clientId);
    //     }
    //     if ($projectId) {
    //         $query->where('project_id', $projectId);
    //     }
    //     if ($workflow) {
    //         $query->where('workflow', $workflow);
    //     }
    //     if ($payment) {
    //         $query->where('payment_status', $payment);
    //     }
    //     if ($method) {
    //         $query->where('delivery_method', $method);
    //     }
    //     if ($ddFrom) {
    //         $query->whereDate('delivery_date', '>=', $ddFrom);
    //     }
    //     if ($ddTo) {
    //         $query->whereDate('delivery_date', '<=', $ddTo);
    //     }
    //     if (isset($repeat) && $repeat !== '') {
    //         $query->where('repeat_order', filter_var($repeat, FILTER_VALIDATE_BOOLEAN) ? 1 : 0);
    //     }
    //     if ($supplierId) {
    //         $query->whereHas('items', function ($q) use ($supplierId) {
    //             $q->where('supplier_id', $supplierId);
    //         });
    //     }
    //     if ($hasMissing) {
    //         $query->whereHas('items', function ($q) {
    //             $q->whereNull('supplier_id');
    //         });
    //     }
    //     if (!is_null($confirms)) {
    //         $query->whereHas('items', function ($q) use ($confirms) {
    //             $q->where('supplier_confirms', $confirms);
    //         });
    //     }

    //     // Totals filter now uses total_price only
    //     if ($minTotal !== null) {
    //         $query->where('total_price', '>=', $minTotal);
    //     }
    //     if ($maxTotal !== null) {
    //         $query->where('total_price', '<=', $maxTotal);
    //     }

    //     // Sorting
    //     if ($sort === 'items_count') {
    //         $query->orderBy('items_count', $dir);
    //     } else {
    //         $query->orderBy($sortMap[$sort], $dir);
    //     }

    //     $paginator = $query->paginate($perPage);

    //     // Transform rows
    //     $data = $paginator->getCollection()->map(function (Orders $o) {
    //         $total  = (float)($o->total_price ?? 0);
    //         $profit = (float)($o->profit_amount ?? $o->profit_amount ?? 0);

    //         $orderInfo = null;
    //         if ($o->workflow === 'Supplier Missing' && $o->unassigned_items_count > 0) {
    //             $orderInfo = 'Supplier missing for ' . $o->unassigned_items_count . ' items';
    //         } elseif ($o->workflow === 'Supplier Assigned') {
    //             $orderInfo = 'Waiting for suppliers to confirm';
    //         } elseif ($o->workflow === 'Payment Requested') {
    //             $orderInfo = 'Awaiting client payment';
    //         }
          
    //         //claude
    //         return [
    //             'id'                       => $o->id,
    //             'po_number'                => $o->po_number,
    //             'client'                   => optional($o->client)->name,
    //             'project'                  => optional($o->project)->name,
    //             'delivery_date'            => $o->delivery_date,
    //             'delivery_time'            => $o->delivery_time,
    //             'delivery_method'          => $o->delivery_method,
    //             'workflow'                 => $o->workflow,
    //             'payment_status'           => $o->payment_status,
    //             'order_process'            => $o->order_process,
    //             'items_count'              => $o->items_count,
    //             'unassigned_items_count'   => $o->unassigned_items_count,
    //             'suppliers_count'          => $o->suppliers_count ?? 0,
                
    //             // NEW: Supplier costs
    //             'supplier_item_cost'       => round((float)($o->supplier_item_cost ?? 0), 2),
    //             'supplier_delivery_cost'   => round((float)($o->supplier_delivery_cost ?? 0), 2),
    //             'supplier_total'           => round((float)($o->supplier_item_cost ?? 0) + (float)($o->supplier_delivery_cost ?? 0), 2),
                
    //             // NEW: Customer costs
    //             'customer_item_cost'       => round((float)($o->customer_item_cost ?? 0), 2),
    //             'customer_delivery_cost'   => round((float)($o->customer_delivery_cost ?? 0), 2),
                
    //             'total_price'              => round($total, 2),
    //             'profit_amount'            => round($profit, 2),
    //             'profit_margin_percent'    => round((float)($o->profit_margin_percent ?? 0), 4),
                
    //             // NEW: Other charges
    //             'gst_tax'                  => round((float)($o->gst_tax ?? 0), 2),
    //             'discount'                 => round((float)($o->discount ?? 0), 2),
    //             'other_charges'            => round((float)($o->other_charges ?? 0), 2),
                
    //             'order_info'               => $orderInfo,
    //             'repeat_order'             => $o->repeat_order,
    //             'created_at'               => $o->created_at,
    //             'updated_at'               => $o->updated_at,
    //         ];
    //     });

    //     // Metrics
    //     $base = Orders::query()->where('is_archived',0);
    //     $metrics = [
    //         'total_orders_count'     => (clone $base)->count(),
    //         'supplier_missing_count' => (clone $base)->where('workflow', 'Supplier Missing')->count(),
    //         'supplier_assigned_count'=> (clone $base)->where('workflow', 'Supplier Assigned')->count(),
    //         'awaiting_payment_count' => (clone $base)->where('workflow', 'Payment Requested')->count(),
    //         'delivered_count'        => (clone $base)->where('workflow', 'Delivered')->count(),
    //     ];

    //     $response = [
    //         'data' => $data,
    //         'pagination' => [
    //             'per_page'       => $paginator->perPage(),
    //             'current_page'   => $paginator->currentPage(),
    //             'total_pages'    => $paginator->lastPage(),
    //             'total_items'    => $paginator->total(),
    //             'has_more_pages' => $paginator->hasMorePages(),
    //         ],
    //         'metrics' => $metrics,
    //     ];

    //     if ($details) {
    //         $response['filters'] = [
    //             'clients'          => User::query()->where('role', 'client')->select('id','name','profile_image')->orderBy('name')->get(),
    //             'suppliers'        => User::where('role','supplier')->select('id','name','profile_image')->orderBy('name')->get(),
    //             'projects'         => Projects::query()->select('id','name')->orderBy('name')->get(),
    //             'workflows'        => ['Requested','Supplier Missing','Supplier Assigned','Payment Requested','On Hold','Delivered'],
    //             'payment_statuses' => ['Pending','Requested','Paid','Partially Paid','Partial Refunded','Refunded'],
    //             'delivery_methods' => ['Other','Tipper','Agitator','Pump','Ute'],
    //         ];
    //     }

    //     return response()->json($response);
    // }
    public function index(Request $request)
    {
        // ==================== CONSTANTS ====================
        $ITEM_MARGIN     = 1.50; // 50% margin on items (multiplier)
        $DELIVERY_MARGIN = 1.10; // 10% margin on delivery (multiplier)
        $GST_RATE        = 0.10; // 10% GST

        // ==================== PARSE FILTERS ====================
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
        $confirms  = $request->get('supplier_confirms');
        $minTotal  = $request->get('min_total');
        $maxTotal  = $request->get('max_total');
        $sort      = $request->get('sort', 'created_at');
        $dir       = strtolower($request->get('dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        $details   = filter_var($request->get('details', false), FILTER_VALIDATE_BOOLEAN);

        if (!is_null($confirms)) {
            $confirms = $confirms === "true";
        }

        // Allowed sort columns
        $sortMap = [
            'po_number'            => 'po_number',
            'delivery_date'        => 'delivery_date',
            'created_at'           => 'created_at',
            'updated_at'           => 'updated_at',
            'total_price'          => 'calc_total_price',     // computed column alias
            'profit_amount'        => 'calc_profit',          // computed column alias
            'items_count'          => DB::raw('items_count'),
        ];
        if (!array_key_exists($sort, $sortMap)) {
            $sort = 'created_at';
        }

        // ==================== BASE QUERY WITH COMPUTED COLUMNS ====================
        $query = Orders::query()
            ->with([
                'client:id,name',
                'project:id,name',
            ])
            ->withCount([
                'items as items_count',
                'items as unassigned_items_count' => function ($q) {
                    $q->whereNull('supplier_id');
                },
            ])
            // Distinct supplier count
            ->withCount(['items as suppliers_count' => function ($q) {
                $q->whereNotNull('supplier_id')->select(DB::raw('COUNT(DISTINCT supplier_id)'));
            }])

            // ===== INVOICE COUNTS =====
            ->withCount('invoices as invoices_count')
            ->addSelect([
                'invoiced_amount' => DB::table('invoices')
                    ->selectRaw('COALESCE(SUM(total_amount), 0)')
                    ->whereColumn('invoices.order_id', 'orders.id')
                    ->whereNotIn('status', ['Cancelled', 'Void']),
            ])

            // ===== SUPPLIER COSTS (from order_items) =====
            ->addSelect([
                'calc_supplier_item_cost' => DB::table('order_items')
                    ->selectRaw('COALESCE(SUM(supplier_unit_cost * quantity), 0)')
                    ->whereColumn('order_items.order_id', 'orders.id'),
            ])
            ->addSelect([
                'calc_supplier_discount' => DB::table('order_items')
                    ->selectRaw('COALESCE(SUM(COALESCE(supplier_discount, 0)), 0)')
                    ->whereColumn('order_items.order_id', 'orders.id'),
            ])
            ->addSelect([
                'calc_supplier_delivery_cost' => DB::table('order_item_deliveries')
                    ->join('order_items', 'order_item_deliveries.order_item_id', '=', 'order_items.id')
                    ->selectRaw('COALESCE(SUM(order_item_deliveries.delivery_cost), 0)')
                    ->whereColumn('order_items.order_id', 'orders.id'),
            ])

            // ===== CUSTOMER COSTS (with margins) =====
            ->addSelect([
                'calc_customer_item_cost' => DB::table('order_items')
                    ->selectRaw("COALESCE(SUM(supplier_unit_cost * quantity * {$ITEM_MARGIN}), 0)")
                    ->whereColumn('order_items.order_id', 'orders.id'),
            ])
            ->addSelect([
                'calc_customer_delivery_cost' => DB::table('order_item_deliveries')
                    ->join('order_items', 'order_item_deliveries.order_item_id', '=', 'order_items.id')
                    ->selectRaw("COALESCE(SUM(order_item_deliveries.delivery_cost * {$DELIVERY_MARGIN}), 0)")
                    ->whereColumn('order_items.order_id', 'orders.id'),
            ])

            ->where('is_archived', false);

        // ==================== FILTERS ====================
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

        // Total filter uses computed value — apply via having or raw where
        // Since addSelect creates aliases, we filter in PHP after fetch or use subquery wrapping
        // For simplicity, we keep the old column filter as fallback
        if ($minTotal !== null) {
            $query->where('total_price', '>=', $minTotal);
        }
        if ($maxTotal !== null) {
            $query->where('total_price', '<=', $maxTotal);
        }

        // ==================== SORTING ====================
        if ($sort === 'items_count') {
            $query->orderBy('items_count', $dir);
        } elseif ($sort === 'total_price') {
            $query->orderBy('calc_total_price', $dir);
        } elseif ($sort === 'profit_amount') {
            $query->orderBy('calc_profit', $dir);
        } else {
            $query->orderBy($sortMap[$sort], $dir);
        }

        $paginator = $query->paginate($perPage);

        // ==================== TRANSFORM ROWS ====================
        $data = $paginator->getCollection()->map(function (Orders $o) use ($GST_RATE) {
            // Read computed subquery values
            $supplierItemCost     = round((float)($o->calc_supplier_item_cost ?? 0), 2);
            $supplierDiscount     = round((float)($o->calc_supplier_discount ?? 0), 2);
            $supplierDeliveryCost = round((float)($o->calc_supplier_delivery_cost ?? 0), 2);
            $supplierTotal        = round($supplierItemCost - $supplierDiscount + $supplierDeliveryCost, 2);

            $customerItemCost     = round((float)($o->calc_customer_item_cost ?? 0), 2);
            $customerDeliveryCost = round((float)($o->calc_customer_delivery_cost ?? 0), 2);
            $customerSubtotal     = round($customerItemCost + $customerDeliveryCost, 2);

            $gst          = round($customerSubtotal * $GST_RATE, 2);
            $discount     = round((float)($o->discount ?? 0), 2);
            $otherCharges = round((float)($o->other_charges ?? 0), 2);
            $totalPrice   = round($customerSubtotal + $gst - $discount + $otherCharges, 2);

            $profit       = round($customerSubtotal - $supplierTotal, 2);
            $marginPct    = $supplierTotal > 0 ? round($profit / $supplierTotal, 4) : 0;

            // Order info text
            $orderInfo = null;
            if ($o->workflow === 'Supplier Missing' && $o->unassigned_items_count > 0) {
                $orderInfo = 'Supplier missing for ' . $o->unassigned_items_count . ' items';
            } elseif ($o->workflow === 'Supplier Assigned') {
                $orderInfo = 'Waiting for suppliers to confirm';
            } elseif ($o->workflow === 'Payment Requested') {
                $orderInfo = 'Awaiting client payment';
            }

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

                // Supplier costs (computed from items + deliveries)
                'supplier_item_cost'       => $supplierItemCost,
                'supplier_discount'        => $supplierDiscount,
                'supplier_delivery_cost'   => $supplierDeliveryCost,
                'supplier_total'           => $supplierTotal,

                // Customer costs (with margins applied)
                'customer_item_cost'       => $customerItemCost,
                'customer_delivery_cost'   => $customerDeliveryCost,

                // Totals
                'total_price'              => $totalPrice,
                'gst_tax'                  => $gst,
                'discount'                 => $discount,
                'other_charges'            => $otherCharges,

                // Profit
                'profit_amount'            => $profit,
                'profit_margin_percent'    => $marginPct,

                // Invoices
                'invoices_count'           => $o->invoices_count ?? 0,
                'invoiced_amount'          => round((float)($o->invoiced_amount ?? 0), 2),

                'order_info'               => $orderInfo,
                'repeat_order'             => $o->repeat_order,
                'created_at'               => $o->created_at,
                'updated_at'               => $o->updated_at,
            ];
        });

        // ==================== METRICS ====================
        $base = Orders::query()->where('is_archived', 0);
        $metrics = [
            'total_orders_count'     => (clone $base)->count(),
            'supplier_missing_count' => (clone $base)->where('workflow', 'Supplier Missing')->count(),
            'supplier_assigned_count'=> (clone $base)->where('workflow', 'Supplier Assigned')->count(),
            'awaiting_payment_count' => (clone $base)->where('workflow', 'Payment Requested')->count(),
            'delivered_count'        => (clone $base)->where('workflow', 'Delivered')->count(),
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
            $response['filters'] = [
                'clients'          => User::query()->where('role', 'client')->select('id', 'name', 'profile_image')->orderBy('name')->get(),
                'suppliers'        => User::where('role', 'supplier')->select('id', 'name', 'profile_image')->orderBy('name')->get(),
                'projects'         => Projects::query()->select('id', 'name')->orderBy('name')->get(),
                'workflows'        => ['Requested', 'Supplier Missing', 'Supplier Assigned', 'Payment Requested', 'On Hold', 'Delivered'],
                'payment_statuses' => ['Pending', 'Requested', 'Paid', 'Partially Paid', 'Partial Refunded', 'Refunded'],
                'delivery_methods' => ['Other', 'Tipper', 'Agitator', 'Pump', 'Ute'],
            ];
        }

        return response()->json($response);
    }



    public function show(Orders $order)
    {
        // Load what we need (add deliveries + supplier)
        $order->load([
            'client:id,name',
            'project:id,name',
            'items.product:id,product_name',
            'items.supplier:id,name,profile_image,delivery_zones',
            'items.deliveries', // NEW: split deliveries
        ]);

        $deliveryLat = $order->delivery_lat ?? null;
        $deliveryLng = $order->delivery_long ?? null;

        $filters['projects'] = Projects::where('added_by', $order->client_id)
            ->where('id', '!=', $order->project?->id)
            ->get();

        $logs = ActionLog::where('order_id', $order->id)->orderBy('created_at', 'desc')->get();

        // ---------- NEW: pre-load offers for eligible suppliers (for ALL workflows) ----------
        $canComputeDistance = is_numeric($deliveryLat) && is_numeric($deliveryLng);
        $masterIds = $order->items->pluck('product_id')->filter()->unique()->values();

        $offersByProduct = SupplierOffers::query()
            ->whereIn('master_product_id', $masterIds)
            ->with(['supplier:id,name,role,delivery_zones,profile_image'])
            ->get()
            ->groupBy('master_product_id');

        // ---------- payload ----------
        $payload = [
            'id' => $order->id,
            'po_number' => $order->po_number,
            'client' => optional($order->client)->name,
            'project' => optional($order->project)->name,

            'delivery_address' => $order->delivery_address,
            'delivery_lat' => $order->delivery_lat,
            'delivery_long' => $order->delivery_long,
            'delivery_date' => $order->delivery_date,
            'delivery_time' => $order->delivery_time,
            'delivery_window' => $order->delivery_window,
            'delivery_method' => $order->delivery_method,

            'load_size' => $order->load_size,
            'special_equipment' => $order->special_equipment,
            'special_notes' => $order->special_notes,

            // NEW: contact fields
            'contact_person_name' => $order->contact_person_name,
            'contact_person_number' => $order->contact_person_number,

            'repeat_order' => (int) $order->repeat_order,
            'order_process' => $order->order_process,

            'workflow' => $order->workflow,
            'payment_status' => $order->payment_status,
            'order_status' => $order->order_status,

            'gst_tax' => round((float) $order->gst_tax),
            'discount' => round((float) $order->discount),
            'other_charges' => round((float) $order->other_charges),
            'total_price' => round((float) $order->total_price),
            'customer_item_cost' => round((float) $order->customer_item_cost),
            'customer_delivery_cost' => round((float) $order->customer_delivery_cost),
            'supplier_item_cost' => round((float) $order->supplier_item_cost),
            'supplier_delivery_cost' => round((float) $order->supplier_delivery_cost),
            'profit_margin_percent' => (float) $order->profit_margin_percent,
            'profit_amount' => round((float) $order->profit_amount),

            'items' => [],
            'filters' => $filters,
            'logs' => $logs,
        ];
        $a=1;
        foreach ($order->items as $it) {
        
            // ---- Build eligible suppliers for this product (ALWAYS), excluding assigned supplier if exists ----
            $eligible = [];
            $productOffers = $offersByProduct->get($it->product_id, collect());
        
            foreach ($productOffers as $offer) {
                $supplier = $offer->supplier;
                
                if (!$supplier) continue;
                if (strtolower((string) $supplier->role) !== 'supplier') continue;
                if (empty($supplier->delivery_zones)) continue;

                // EXCLUDE the already assigned supplier (your new requirement)
                // if (!is_null($it->supplier_id) && (int) $supplier->id === (int) $it->supplier_id) {
                    
                //     continue;
                // }

                $zones = is_array($supplier->delivery_zones)
                    ? $supplier->delivery_zones
                    : json_decode($supplier->delivery_zones, true);

                if (empty($zones)) continue;

                $distance = null;
                if ($canComputeDistance) {
                    $distance = $this->nearestZoneDistanceKm($zones, (float) $deliveryLat, (float) $deliveryLng);
                }

                $eligible[] = [
                    'supplier_id' => (int) $supplier->id,
                    'name' => (string) $supplier->name,
                    'offer_id' => (int) $offer->id,
                    'selected'=>(!is_null($it->supplier_id) && (int) $supplier->id === (int) $it->supplier_id) ? true : false,
                    'unit_cost' => (float) $offer->price,
                    'distance' => is_null($distance) ? null : round($distance, 3),
                ];
            
            }

            usort($eligible, function ($a, $b) {
                if ($a['distance'] === null && $b['distance'] === null) return 0;
                if ($a['distance'] === null) return 1;
                if ($b['distance'] === null) return -1;
                return $a['distance'] <=> $b['distance'];
            });

            // ---- Deliveries (split slots) - always include ----
            $deliveries = $it->relationLoaded('deliveries')
                ? $it->deliveries->sortBy(fn($d) => $d->delivery_date . ' ' . ($d->delivery_time ?? '00:00:00'))->values()
                : [];

            // ---- Item payload ----
            if (!is_null($it->supplier_id)) {
                $payload['items'][] = [
                    'id' => $it->id,
                    'product_id' => $it->product_id,
                    'product_name' => optional($it->product)->product_name,
                    'quantity' => $it->quantity,

                    'supplier' => $it->supplier
                        ? $it->supplier()->select('id', 'name', 'profile_image', 'delivery_zones')->first()
                        : null,

                    'choosen_offer_id' => $it->choosen_offer_id ?? null,
                    'supplier_confirms' => $it->supplier_confirms,
                    'supplier_unit_cost' => $it->supplier_unit_cost,
                    'delivery_cost' => $it->delivery_cost,
                    'supplier_discount' => $it->supplier_discount,
                    'delivery_type' => $it->delivery_type,
                    'is_quoted' => (int) $it->is_quoted,
                    'is_paid' => (int) $it->is_paid,
                    'quoted_price' => $it->quoted_price,

                    // NEW: split deliveries
                    'deliveries' => $deliveries,

                    // CHANGED: now return eligible suppliers excluding assigned supplier
                    'eligible_suppliers' => $eligible,
                ];
            } else {
                // Unassigned item (Supplier Missing) - show eligible suppliers
                $payload['items'][] = [
                    'id' => $it->id,
                    'product_id' => $it->product_id,
                    'product_name' => optional($it->product)->product_name,
                    'quantity' => $it->quantity,
                    'supplier_id' => $it->supplier_id,

                    // NEW: split deliveries
                    'deliveries' => $deliveries,

                    'eligible_suppliers' => $eligible,
                ];
            }
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
                'delivery_type'          => null,
                'supplier_confirms'      => 0,
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
                // dd('ues');
                $order->workflow = 'Supplier Assigned';
                $order->gst_tax = 0.00;
                $order->discount = 0.00;
                $order->total_price = 0.00;
                $order->customer_item_cost = 0.00;
                $order->customer_delivery_cost = 0.00;
                $order->supplier_item_cost = 0.00;
                $order->supplier_delivery_cost = 0.00;
                $order->profit_amount = 0.00;
                $order->profit_margin_percent = 0.00;

                $order->save();
            }

            // recalc customer totals
            $order->load('items');
            // OrderPricingService::recalcCustomer($order, null, null, true);

            return response()->json([
                'message' => 'Supplier assigned.',
                'order'=> $order,
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
                    'delivery_type'          => $item->delivery_type,          // null
              
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
            // 'supplier_delivery_date' => 'sometimes|required|date',
            'supplier_confirms' => 'sometimes|required|boolean',
            'supplier_notes' => 'sometimes|nullable|string|max:500',
            'quantity' => 'sometimes|nullable|numeric|min:1'
        ]);

        if ($v->fails()) {
            return response()->json(['error' => $v->errors()], 422);
        }

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
            
          
            
            if ($request->has('supplier_discount')) {
                $updateData['supplier_discount'] = $request->supplier_discount;
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
     

            // // If supplier confirmed the order, trigger workflow check
            // if ($request->has('supplier_confirms') && $request->supplier_confirms) {
            //     $this->workflow($orderItem->order);
            // }

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
            case 'Supplier Missing':
             
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

                    if($item->supplier_confirms){
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

                // $order->workflow = 'Payment Requested';
                // $order->order_status = "Confirmed";
                // dd($customer_item_cost, $customer_delivery_cost, $supplier_item_cost, $supplier_delivery_cost, $profit_margin_percent, $profit_before_tax);
                // dd($order);
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

    private function pickNearestOfferInZone($offersForProduct, float $lat, float $lng): array
    {
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

            if (!is_array($zones)) {
                continue;
            }

            foreach ($zones as $z) {
                if (!isset($z['lat'], $z['long'], $z['radius'])) {
                    continue;
                }
                $distKm = $this->haversineKm($lat, $lng, (float) $z['lat'], (float) $z['long']);

                if ($distKm <= (float) $z['radius']) {
                    if ($distKm < $bestDist) {
                        $bestDist  = $distKm;
                        $bestOffer = $offer;
                    }
                }
            }
        }

        return [$bestOffer, $bestOffer ? $bestDist : INF];
    }


            
    public function editOrderAdmin(Request $request, Orders $order)
    {
        $order->load(['items.deliveries']);

        // ── Validation ──────────────────────────────────────────────
        $v = Validator::make($request->all(), [
            // Order-level fields (admin gets more than client)
            'order'                           => ['nullable', 'array'],
            'order.contact_person_name'       => ['nullable', 'string', 'max:255'],
            'order.contact_person_number'     => ['nullable', 'string', 'max:50'],
            'order.site_instructions'         => ['nullable', 'string'],
            'order.delivery_date'             => ['nullable', 'date'],
            'order.delivery_method'           => ['nullable', 'string', 'in:Other,Tipper,Agitator,Pump,Ute'],
            'order.special_notes'             => ['nullable', 'string', 'max:1000'],
            'order.discount'                  => ['nullable', 'numeric', 'min:0'],

            // Items to add
            'items_add'                              => ['nullable', 'array'],
            'items_add.*.product_id'                 => ['required', 'integer', 'exists:master_products,id'],
            'items_add.*.quantity'                    => ['required', 'numeric', 'min:0.01'],
            'items_add.*.deliveries'                 => ['nullable', 'array'],
            'items_add.*.deliveries.*.id'            => ['nullable', 'integer'],
            'items_add.*.deliveries.*.quantity'       => ['required_with:items_add.*.deliveries', 'numeric', 'min:0.01'],
            'items_add.*.deliveries.*.delivery_date' => ['required_with:items_add.*.deliveries', 'date'],
            'items_add.*.deliveries.*.delivery_time' => ['nullable', 'date_format:H:i'],
            'items_add.*.deliveries.*.delivery_cost' => ['nullable', 'numeric', 'min:0'],
            'items_add.*.deliveries.*.truck_type'   => ['nullable', 'string'],

            // Items to update
            'items_update'                              => ['nullable', 'array'],
            'items_update.*.order_item_id'              => ['required', 'integer'],
            'items_update.*.quantity'                    => ['required', 'numeric', 'min:0.01'],
            'items_update.*.deliveries'                 => ['nullable', 'array'],
            'items_update.*.deliveries.*.id'            => ['nullable', 'integer'],
            'items_update.*.deliveries.*.quantity'       => ['required_with:items_update.*.deliveries', 'numeric', 'min:0.01'],
            'items_update.*.deliveries.*.delivery_date' => ['required_with:items_update.*.deliveries', 'date'],
            'items_update.*.deliveries.*.delivery_time' => ['nullable', 'date_format:H:i'],
            'items_update.*.deliveries.*.delivery_cost' => ['nullable', 'numeric', 'min:0'],
            'items_update.*.deliveries.*.truck_type'   => ['nullable', 'string'],

            // Items to remove
            'items_remove'   => ['nullable', 'array'],
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
        $adminUser = Auth::user();

        return DB::transaction(function () use ($order, $payload, $adminUser) {

            $order->refresh()->load(['items.deliveries']);
            $itemsById = $order->items->keyBy('id');
            $logDetails = []; // collect log messages

            // ─────────────────────────────────────────────────────────
            // 1) UPDATE ORDER-LEVEL FIELDS
            // ─────────────────────────────────────────────────────────
            if (!empty($payload['order'])) {
                $allowedFields = [
                    'contact_person_name',
                    'contact_person_number',
                    'site_instructions',
                    'delivery_date',
                    'delivery_method',
                    'special_notes',
                    'discount',
                ];

                $patch = [];
                $changedFields = [];

                foreach ($allowedFields as $field) {
                    if (array_key_exists($field, $payload['order'])) {
                        $patch[$field] = $payload['order'][$field];
                        $changedFields[] = $field;
                    }
                }

                if (!empty($patch)) {
                    $order->fill($patch);
                    $order->save();

                    $logDetails[] = 'Updated fields: ' . implode(', ', $changedFields);

                    // If discount changed, recalculate pricing
                    if (array_key_exists('discount', $patch)) {
                        // Will be recalculated at end if needed via OrderPricingService
                        // or you can trigger it here if the order is already in Payment Requested state
                        if (in_array($order->workflow, ['Payment Requested'])) {
                            try {
                                OrderPricingService::recalcCustomer($order, null, null, true);
                            } catch (\Throwable $e) {
                                // silently continue — pricing can be recalced later
                            }
                        }
                    }

                    ActionLog::create([
                        'action'   => 'Admin Edited Order Fields',
                        'details'  => "Admin {$adminUser->contact_name} updated Order #{$order->id}: " . implode(', ', $changedFields),
                        'order_id' => $order->id,
                        'user_id'  => $adminUser->id,
                    ]);
                }
            }

            // ─────────────────────────────────────────────────────────
            // 2) REMOVE ITEMS (only if no delivered deliveries)
            // ─────────────────────────────────────────────────────────
            if (!empty($payload['items_remove'])) {
                $removedNames = [];

                foreach ($payload['items_remove'] as $removeItemId) {
                    /** @var OrderItem|null $item */
                    $item = $itemsById->get($removeItemId);

                    if (!$item) {
                        return response()->json([
                            'success' => false,
                            'message' => "Item {$removeItemId} does not belong to this order.",
                        ], 422);
                    }

                    $hasDelivered = $item->deliveries->where('status', 'delivered')->isNotEmpty();
                    if ($hasDelivered) {
                        return response()->json([
                            'success' => false,
                            'message' => "Cannot remove item {$removeItemId} because it has delivered deliveries.",
                        ], 422);
                    }

                    // Track product name for log
                    $productName = optional($item->product)->product_name ?? "Product #{$item->product_id}";
                    $removedNames[] = $productName;

                    // Delete non-delivered deliveries, then item
                    OrderItemDelivery::where('order_item_id', $item->id)
                        ->where(function ($q) {
                            $q->whereNull('status')->orWhere('status', '!=', 'delivered');
                        })
                        ->delete();

                    $item->delete();
                }

                ActionLog::create([
                    'action'   => 'Admin Removed Order Items',
                    'details'  => "Admin {$adminUser->contact_name} removed items from Order #{$order->id}: " . implode(', ', $removedNames),
                    'order_id' => $order->id,
                    'user_id'  => $adminUser->id,
                ]);
            }

            // Refresh after deletions
            $order->refresh()->load(['items.deliveries']);
            $itemsById = $order->items->keyBy('id');

            // ─────────────────────────────────────────────────────────
            // 3) ADD NEW ITEMS (with auto-supplier assignment)
            // ─────────────────────────────────────────────────────────
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

            if (!empty($payload['items_add'])) {
                $addedNames = [];

                foreach ($payload['items_add'] as $add) {
                    $pid = (int) $add['product_id'];

                    // Pick nearest supplier (same logic as createOrder)
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
                        'choosen_offer_id'       => $chosenOffer ? $chosenOffer->id : null,
                        'supplier_confirms'      => 0,
                    ]);

                    // Track for log
                    $product = \App\Models\MasterProducts::find($pid);
                    $addedNames[] = ($product->product_name ?? "Product #{$pid}") . " (qty: {$add['quantity']})";

                    // Create delivery slots
                    $deliveries = $add['deliveries'] ?? [];
                    if (!empty($deliveries)) {
                        $sum = (float) collect($deliveries)->sum('quantity');

                        if (abs($sum - (float) $newItem->quantity) > 0.01) {
                            return response()->json([
                                'success' => false,
                                'message' => "Split deliveries total ({$sum}) must match item quantity ({$newItem->quantity}) for product_id {$pid}.",
                            ], 422);
                        }

                        foreach ($deliveries as $d) {
                            OrderItemDelivery::create([
                                'order_id'          => $order->id,
                                'order_item_id'     => $newItem->id,
                                // 'supplier_id'       => $supplierId,
                                'quantity'          => (float) $d['quantity'],
                                'delivery_date'     => $d['delivery_date'],
                                'delivery_time'     => $d['delivery_time'] ?? null,
                                'supplier_confirms' => 0,
                                'status'            => 'scheduled',
                                'delivery_cost'     => (float) ($d['delivery_cost'] ?? 0),
                                'truck_type'        => $d['truck_type'] ?? null,
                            ]);
                        }
                    }

                    if (!$supplierId) {
                        $anyMissingSupplierInOrder = true;
                    }
                }

                ActionLog::create([
                    'action'   => 'Admin Added Order Items',
                    'details'  => "Admin {$adminUser->contact_name} added items to Order #{$order->id}: " . implode('; ', $addedNames),
                    'order_id' => $order->id,
                    'user_id'  => $adminUser->id,
                ]);
            }

            // Refresh after additions
            $order->refresh()->load(['items.deliveries']);
            $itemsById = $order->items->keyBy('id');

            // ─────────────────────────────────────────────────────────
            // 4) UPDATE EXISTING ITEMS (quantity + delivery slot sync)
            // ─────────────────────────────────────────────────────────
            if (!empty($payload['items_update'])) {
                $updatedDetails = [];

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

                    $oldQty = (float) $item->quantity;
                    $item->quantity = $newQty;
                    $item->save();

                    $productName = optional($item->product)->product_name ?? "Item #{$item->id}";
                    $updatedDetails[] = "{$productName}: qty {$oldQty} → {$newQty}";

                    // Sync scheduled deliveries (delivered rows are read-only)
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
                            ->filter(fn($d) => $d->status !== 'delivered')
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
                                $row->status        = $row->status ?: 'scheduled';
                                if (array_key_exists('truck_type', $d)) {
                                    $row->truck_type = $d['truck_type'];
                                }
                                
                                if (array_key_exists('delivery_cost', $d)) {
                                    $row->delivery_cost = (float) $d['delivery_cost'];
                                }
                                $row->save();
                            } else {
                                // New delivery slot
                                OrderItemDelivery::create([
                                    'order_id'          => $order->id,
                                    'order_item_id'     => $item->id,
                                    // 'supplier_id'       => $item->supplier_id,
                                    'quantity'          => (float) $d['quantity'],
                                    'delivery_date'     => $d['delivery_date'],
                                    'delivery_time'     => $d['delivery_time'] ?? null,
                                    'supplier_confirms' => 0,
                                    'delivery_cost'=> $d['delivery_cost'],
                                    'truck_type'=> $d['truck_type'],
                                    'status'            => 'scheduled',
                                ]);
                            }
                        }

                        // Delete scheduled deliveries not present in request
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

                if (!empty($updatedDetails)) {
                    ActionLog::create([
                        'action'   => 'Admin Updated Order Items',
                        'details'  => "Admin {$adminUser->contact_name} updated items in Order #{$order->id}: " . implode('; ', $updatedDetails),
                        'order_id' => $order->id,
                        'user_id'  => $adminUser->id,
                    ]);
                }
            }

            // ─────────────────────────────────────────────────────────
            // 5) RECOMPUTE WORKFLOW & EARLIEST DELIVERY
            // ─────────────────────────────────────────────────────────
            $hasMissing = OrderItem::where('order_id', $order->id)->whereNull('supplier_id')->exists();
            if ($hasMissing || $anyMissingSupplierInOrder) {
                $order->workflow      = 'Supplier Missing';
                $order->order_process = 'Action Required';
            } else {
                // Only reset to Supplier Assigned if it was previously missing
                // Don't override if already in Payment Requested, Delivered, etc.
                if ($order->workflow === 'Supplier Missing') {
                    $order->workflow      = 'Supplier Assigned';
                    $order->order_process = 'Automated';
                }
            }

            // Recompute earliest delivery_date/time from all delivery slots
            $earliest = OrderItemDelivery::where('order_id', $order->id)
                ->orderBy('delivery_date')
                ->orderBy('delivery_time')
                ->first();

            if ($earliest) {
                $order->delivery_date = $earliest->delivery_date;
                $order->delivery_time = $earliest->delivery_time;
            }

            $order->save();

            // ─────────────────────────────────────────────────────────
            // 6) RESPONSE
            // ─────────────────────────────────────────────────────────
            $order->refresh()->load([
                'project',
                'items.product',
                'items.supplier',
                'items.deliveries',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Order updated successfully by admin.',
                'data'    => [
                    'order' => $order,
                    'items' => $order->items,
                ],
            ]);
        });
    }
}

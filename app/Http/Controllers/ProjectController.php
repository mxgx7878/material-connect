<?php

namespace App\Http\Controllers;

use App\Models\Projects as Project;
use App\Models\Orders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProjectController extends Controller
{
    /**
     * GET /client/projects
     *
     * Project listing for the client, including:
     * - total_orders (count of orders in project)
     * - total_order_amount (sum of order.total_price)
     * - basic filters (search, delivery date range, workflow)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        abort_unless($user && $user->role === 'client', 403, 'Forbidden');

        $perPage = (int) $request->get('per_page', 10);
        $search  = trim((string) $request->get('search', ''));
        $sort    = $request->get('sort', 'created_at');
        $dir     = strtolower($request->get('dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        // order filters
        $order_status = $request->get('order_status');                // e.g. "Supplier Missing", "Payment Requested", etc.
        $ddFrom   = $request->get('delivery_date_from');      // Y-m-d
        $ddTo     = $request->get('delivery_date_to');        // Y-m-d

        // Base project query for this client
        $query = Project::query()
            ->where('added_by', $user->id)->where('is_archived', 0);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('delivery_address', 'like', "%{$search}%")
                  ->orWhere('site_contact_name', 'like', "%{$search}%");
            });
        }

        // Sort by project fields
        if (!in_array($sort, ['name', 'created_at', 'updated_at'], true)) {
            $sort = 'created_at';
        }

        $paginator = $query->orderBy($sort, $dir)->paginate($perPage);

        // Attach per-project order stats with filters applied
        $userId = $user->id;

        $paginator->getCollection()->transform(function (Project $project) use ($userId, $order_status, $ddFrom, $ddTo) {
            $ordersQuery = $project->orders()
                ->where('client_id', $userId);

            if ($order_status) {
                $ordersQuery->where('order_status', $order_status);
            }
            if ($ddFrom) {
                $ordersQuery->whereDate('delivery_date', '>=', $ddFrom);
            }
            if ($ddTo) {
                $ordersQuery->whereDate('delivery_date', '<=', $ddTo);
            }

            $project->total_orders       = (int) $ordersQuery->count();
            $project->total_order_amount = (float) $ordersQuery->sum('total_price');

            return $project;
        });

        return response()->json([
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'last_page'    => $paginator->lastPage(),
            ],
        ]);
    }

    /**
     * GET /client/projects/{id}
     *
     * View a single project with:
     * - project details
     * - all orders within the project (with filters)
     * - items (products) with consistent pricing per project
     * - analytics
     *
     * NOTE for item pricing:
     *   - if item has `is_quoted == 1`, use `quoted_price` as the price
     *   - otherwise, use `supplier_unit_cost` as the price
     */
    public function projectDetails($id, Request $request)
    {
        $user = Auth::user();
        abort_unless($user && $user->role === 'client', 403, 'Forbidden');

        /** @var Project|null $project */
        $project = Project::where('id', $id)
            ->where('added_by', $user->id)
            ->first();

        if (!$project) {
            return response()->json(['error' => 'Project not found'], 404);
        }

        // filters for orders inside this project
        $order_status = $request->get('order_status');                // ex: "Supplier Missing"
        $ddFrom   = $request->get('delivery_date_from');      // Y-m-d
        $ddTo     = $request->get('delivery_date_to');        // Y-m-d

        $ordersQuery = $project->orders()
            ->where('client_id', $user->id)
            ->with([
                // FIX: include id + all required product columns
                'items.product:id,product_name,photo,product_type,specifications',
                'items.supplier:id,name',
            ])
            ->orderBy('delivery_date', 'desc')
            ->orderBy('created_at', 'desc');

        if ($order_status) {
            $ordersQuery->where('order_status', $order_status);
        }
        if ($ddFrom) {
            $ordersQuery->whereDate('delivery_date', '>=', $ddFrom);
        }
        if ($ddTo) {
            $ordersQuery->whereDate('delivery_date', '<=', $ddTo);
        }

        /** @var \Illuminate\Support\Collection|\App\Models\Orders[] $orders */
        $orders = $ordersQuery->get();

        // ---- Analytics ----
        $totalOrders = $orders->count();
        $totalAmount = (float) $orders->sum('total_price');

        $byOrderStatus = $orders
            ->groupBy('order_status')
            ->map(fn($group) => $group->count())
            ->toArray();

        $firstOrderDate = optional($orders->min('delivery_date'))->toDateString() ?? null;
        $lastOrderDate  = optional($orders->max('delivery_date'))->toDateString() ?? null;

        $analytics = [
            'total_orders'       => $totalOrders,
            'total_order_amount' => $totalAmount,
            'avg_order_value'    => $totalOrders > 0 ? round($totalAmount / $totalOrders, 2) : 0.0,
            'by_order_status'    => $byOrderStatus,
            'first_order_date'   => $firstOrderDate,
            'last_order_date'    => $lastOrderDate,
        ];

        // ---- Project products with consistent pricing ----
        $allItems = $orders->flatMap(function (Orders $order) {
            return $order->items;
        });

        $projectProducts = $allItems
        ->groupBy('product_id')
        ->map(function ($rows) {
            /** @var \App\Models\OrderItem $first */
            $first = $rows->first();
            $last = $rows->last(); // Get the most recent item for reordering

            // Core rule:
            // if item has quoted price => use quoted_price
            // else => use supplier_unit_cost
            if (!is_null($first->quoted_price) && (int) $first->is_quoted === 1) {
                $unitPrice = (float) $first->quoted_price;
            } else {
                $unitPrice = (float) $first->supplier_unit_cost;
            }

            $deliveryCost = (float) $first->supplier_delivery_cost;

            return [
                'product_id'              => $first->product_id,
                'product_name'            => optional($first->product)->product_name,
                'product_image'           => optional($first->product)->photo,
                'product_type'            => optional($first->product)->product_type,
                'product_specifications'  => optional($first->product)->specifications,
                'unit_price'              => $unitPrice * (1+0.50),
                'delivery_cost'           => $deliveryCost,
                'total_quantity'          => (float) $rows->sum('quantity'),
                'orders_count'            => (int) $rows->pluck('order_id')->unique()->count(),
                'first_order_id'          => $rows->min('order_id'),
                'last_order_id'           => $rows->max('order_id'),
                'last_order_item_id'      => $last->id, // NEW - For reordering with same pricing
            ];
        })
        ->values();

        // ---- Shape orders payload for frontend ----
        $ordersPayload = $orders->map(function (Orders $order) {
            return [
                'id'              => $order->id,
                'po_number'       => $order->po_number,
                'delivery_date'   => $order->delivery_date,
                'delivery_time'   => $order->delivery_time,
                'delivery_method' => $order->delivery_method,
                'order_status'    => $order->order_status,
                'payment_status'  => $order->payment_status,
                'total_price'     => (float) $order->total_price,
                'items'           => $order->items->map(function ($item) {
                    // Apply "quoted vs supplier_unit_cost" rule for display price
                    if (!is_null($item->quoted_price) && (int) $item->is_quoted === 1) {
                        $displayUnitPrice = (float) $item->quoted_price;
                    } else {
                        $displayUnitPrice = (float) $item->supplier_unit_cost;
                    }

                    return [
                        'id'                     => $item->id,
                        'product_id'             => $item->product_id,
                        'product_name'           => optional($item->product)->product_name,
                        'product_image'          => optional($item->product)->photo,
                        'product_type'           => optional($item->product)->product_type,
                        'product_specifications' => optional($item->product)->specifications,
                        'quantity'               => (float) $item->quantity,
                        'is_quoted'              => (int) $item->is_quoted,
                        'quoted_price'           => $item->quoted_price,
                        'supplier_unit_cost'     => $item->supplier_unit_cost,
                        'supplier_delivery_cost' => $item->supplier_delivery_cost,
                        'display_unit_price'     => $displayUnitPrice,
                        'supplier_name'          => optional($item->supplier)->name,
                        'custom_blend_mix'       => $item->custom_blend_mix,
                    ];
                }),
            ];
        });

        return response()->json([
            'project'          => $project,
            'analytics'        => $analytics,
            'orders'           => $ordersPayload,
            'project_products' => $projectProducts,
        ]);
    }

    public function show($id)
    {
        
        $project = Project::find($id);
        if (!$project) {
            return response()->json(['error' => 'Project not found'], 404);
        }
    
        
        $user = Auth::user();
        abort_unless($user && $user->role === 'client', 403, 'Forbidden');

        // compare against the FK column
        abort_unless((int) $project->added_by === (int) $user->id, 404, 'Project not found');
        

        $project->load('added_by.company');
        // dd($project);
        return response()->json($project, 200);
    }


    public function store(Request $request)
    {
        $user = Auth::user();
        abort_unless($user && $user->role === 'client', 403, 'Forbidden');

        $validator = Validator::make($request->all(), [
            'name'               => ['required','string','max:255'],
            'site_contact_name'  => ['nullable','string','max:255'],
            'site_contact_phone' => ['nullable','string','max:50'],
            'site_instructions'  => ['nullable','string'],
            'delivery_address' => 'required|string',
            'delivery_lat'     => 'required|numeric',
            'delivery_long'    => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $data = $validator->validated();

        $project = Project::create([
            ...$data,
            'added_by' => $user->id, // lock to this client
        ]);

        return response()->json($project->load('added_by.company'), 201);
    }

    public function update(Request $request, Project $project)
    {
        $user = Auth::user();
        abort_unless($user && $user->role === 'client', 403, 'Forbidden');
        abort_unless($project->added_by === $user->id, 404, 'Project not found');

        $validator = Validator::make($request->all(), [
            'name'               => ['sometimes','required','string','max:255'],
            'site_contact_name'  => ['nullable','string','max:255'],
            'site_contact_phone' => ['nullable','string','max:50'],
            'site_instructions'  => ['nullable','string'],
            'delivery_address' => 'required|string',
            'delivery_lat'     => 'required|numeric',
            'delivery_long'    => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $data = $validator->validated();

        // do NOT change added_by
        $project->update($data);

        return response()->json($project->fresh()->load('added_by.company'), 200);
    }


    public function destroy(Project $project)
    {
        $user = Auth::user();
        if($user->id == $project->added_by){
            $project->is_archived = 1;
            $project->archived_by = $user->id;
            $project->save();
            return response()->json(['message' => 'Project deleted successfully'], 200);
        }else{
            return response()->json(['error' => 'Unauthorized'], 403);
        }
    }
}

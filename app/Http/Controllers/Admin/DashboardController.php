<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Orders;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\MasterProducts;
use DateTimeZone;
use DateTime;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function summary(Request $r)
    {
        $from = $r->date('from') ?? now()->startOfMonth();
        $to   = $r->date('to')   ?? now();
        $gran = $r->get('granularity', 'day');
        $tz   = $r->get('tz', 'Australia/Sydney');

        // Calculate previous period for comparison
        $periodDays = $from->diffInDays($to);
        $prevFrom = (clone $from)->subDays($periodDays + 1);
        $prevTo = (clone $from)->subDay();

        // Base scope for current period - FIXED: Use created_at
        $orders = Orders::query()
            ->when($r->client_id, fn($q, $v) => $q->where('client_id', $v))
            ->when($r->project_id, fn($q, $v) => $q->where('project_id', $v))
            ->when($r->workflow, fn($q, $v) => $q->where('workflow', $v))
            ->when($r->payment_status, fn($q, $v) => $q->where('payment_status', $v))
            ->when($r->delivery_method, fn($q, $v) => $q->where('delivery_method', $v))
            ->whereBetween('created_at', [$from, $to]); // FIXED

        // Base scope for previous period (for comparison) - FIXED: Use created_at
        $prevOrders = Orders::query()
            ->when($r->client_id, fn($q, $v) => $q->where('client_id', $v))
            ->when($r->project_id, fn($q, $v) => $q->where('project_id', $v))
            ->when($r->workflow, fn($q, $v) => $q->where('workflow', $v))
            ->when($r->payment_status, fn($q, $v) => $q->where('payment_status', $v))
            ->when($r->delivery_method, fn($q, $v) => $q->where('delivery_method', $v))
            ->whereBetween('created_at', [$prevFrom, $prevTo]); // FIXED

        // KPIs - Current Period
        $ordersTotal = (int) (clone $orders)->count();
        $revenue = (float) (clone $orders)->whereIn('workflow',['Payment Requested','Delivered'])->sum('total_price');
        $awaitingPayment = (int) (clone $orders)->whereIn('payment_status', ['Pending', 'Requested'])->count();
        $supplierMissing = (int) (clone $orders)->where('workflow', 'Supplier Missing')->count();
        $completedOrders = (int) (clone $orders)->where('workflow', 'Delivered')->count();
        $avgOrderValue = $ordersTotal > 0 ? round($revenue / $ordersTotal, 2) : 0;
        $activeClients = (int) (clone $orders)->distinct('client_id')->count('client_id');
        
        // Get active suppliers from order items
        $orderIds = (clone $orders)->pluck('id');
        $activeSuppliers = OrderItem::whereIn('order_id', $orderIds)
            ->whereNotNull('supplier_id')
            ->distinct('supplier_id')
            ->count('supplier_id');

        // KPIs - Previous Period
        $ordersTotalPrev = (int) (clone $prevOrders)->count();
        $revenuePrev = (float) (clone $prevOrders)->sum('total_price');
        $completedOrdersPrev = (int) (clone $prevOrders)->where('workflow', 'Delivered')->count();
        $activeClientsPrev = (int) (clone $prevOrders)->distinct('client_id')->count('client_id');

        // Calculate percentage changes
        $ordersChange = $this->calculatePercentageChange($ordersTotal, $ordersTotalPrev);
        $revenueChange = $this->calculatePercentageChange($revenue, $revenuePrev);
        $completedChange = $this->calculatePercentageChange($completedOrders, $completedOrdersPrev);
        $clientsChange = $this->calculatePercentageChange($activeClients, $activeClientsPrev);

        // Total counts (not period-specific)
        $totalClients = User::where('role', 'client')->count();
        $totalSuppliers = User::where('role', 'supplier')->whereIn('status', ['approved', 'active'])->count();
        $pendingSuppliers = User::where('role', 'supplier')->where('status', 'pending')->count();

        // Performance metrics
        $cancelledOrders = (int) (clone $orders)->where('order_status', 'Cancelled')->count();
        $cancellationRate = $ordersTotal > 0 ? round(($cancelledOrders / $ordersTotal) * 100, 2) : 0;

        // Repeat client rate - FIXED: Use created_at
        $clientsWithMultipleOrders = Orders::query()
            ->whereBetween('created_at', [$from, $to]) // FIXED
            ->selectRaw('client_id, COUNT(*) as order_count')
            ->groupBy('client_id')
            ->having('order_count', '>', 1)
            ->count();
        $repeatClientRate = $activeClients > 0 ? round(($clientsWithMultipleOrders / $activeClients) * 100, 2) : 0;

        $kpis = [
            // Primary metrics
            'orders_total' => $ordersTotal,
            'orders_total_prev' => $ordersTotalPrev,
            'orders_change' => $ordersChange,
            
            'revenue' => $revenue,
            'revenue_prev' => $revenuePrev,
            'revenue_change' => $revenueChange,
            
            'avg_order_value' => $avgOrderValue,
            
            'completed_orders' => $completedOrders,
            'completed_orders_prev' => $completedOrdersPrev,
            'completed_change' => $completedChange,
            
            // Activity metrics
            'active_clients' => $activeClients,
            'active_clients_prev' => $activeClientsPrev,
            'active_clients_change' => $clientsChange,
            
            'active_suppliers' => $activeSuppliers,
            
            // Alerts & Actions
            'awaiting_payment' => $awaitingPayment,
            'supplier_missing' => $supplierMissing,
            'pending_supplier_approvals' => $pendingSuppliers,
            
            // Performance
            'cancellation_rate' => $cancellationRate,
            'repeat_client_rate' => $repeatClientRate,
            
            // System totals
            'total_clients' => $totalClients,
            'total_suppliers' => $totalSuppliers,
        ];

        // Which charts to build
        $want = collect(explode(',', $r->get('charts', 'date,supplier,product,price_bucket,status')))
            ->map(fn($x) => trim($x))->filter()->values();

        $charts = [];

        // 1) Date revenue & order count - FIXED: Use created_at in date formatting
        if ($want->contains('date')) {
            $bucket = match ($gran) {
                'week'  => "DATE_FORMAT(CONVERT_TZ(created_at, '+00:00', '{$this->offset($tz)}'), '%x-%v')", // FIXED
                'month' => "DATE_FORMAT(CONVERT_TZ(created_at, '+00:00', '{$this->offset($tz)}'), '%Y-%m')", // FIXED
                default => "DATE(CONVERT_TZ(created_at, '+00:00', '{$this->offset($tz)}'))", // FIXED
            };

            $rows = (clone $orders)
                ->selectRaw("$bucket AS bucket, COUNT(*) AS orders, SUM(total_price) AS revenue")
                ->groupBy('bucket')->orderBy('bucket')->get();

            $charts[] = [
                'id'       => 'time_revenue',
                'title'    => 'Revenue & Orders Over Time',
                'group_by' => 'date',
                'labels'   => $rows->pluck('bucket')->values(),
                'series'   => [
                    ['name' => 'Revenue', 'data' => $rows->pluck('revenue')->map(fn($v) => (float)$v)->values()],
                    ['name' => 'Orders', 'data' => $rows->pluck('orders')->map(fn($v) => (int)$v)->values()],
                ],
            ];
        }

        // 2) Order status distribution
        if ($want->contains('status')) {
            $statusDist = (clone $orders)
                ->selectRaw('workflow, COUNT(*) AS count')
                ->groupBy('workflow')
                ->orderByDesc('count')
                ->get();

            $charts[] = [
                'id'       => 'order_status',
                'title'    => 'Order Status Distribution',
                'group_by' => 'status',
                'type'     => 'donut',
                'labels'   => $statusDist->pluck('workflow')->values(),
                'series'   => $statusDist->pluck('count')->map(fn($v) => (int)$v)->values(),
            ];
        }

        // 3) Supplier revenue - FIXED: Use created_at
        if ($want->contains('supplier')) {
            // Get orders that have at least one supplier assigned
            $supplierRevenue = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereBetween('orders.created_at', [$from, $to])
            ->whereIn('orders.workflow', ['Payment Requested', 'Delivered'])
            ->whereNotNull('order_items.supplier_id')
            ->when($r->supplier_id, fn($q, $v) => $q->where('order_items.supplier_id', $v))
            ->selectRaw('
                order_items.supplier_id AS supplier_id,
                SUM(
                    (COALESCE(order_items.supplier_unit_cost,0) * COALESCE(order_items.quantity,0))
                    + COALESCE(order_items.supplier_delivery_cost,0)
                    - COALESCE(order_items.supplier_discount,0)
                ) AS revenue
            ')
            ->groupBy('order_items.supplier_id')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get()
            ->map(fn($r) => [
                'supplier_id' => (int) $r->supplier_id,
                'revenue'     => (float) $r->revenue,
            ])
            ->values();

            $supplierIds = $supplierRevenue->pluck('supplier_id');
            $supplierNames = User::whereIn('id', $supplierIds)->pluck('name', 'id');

            $charts[] = [
                'id'       => 'supplier_revenue',
                'title'    => 'Top Suppliers by Revenue',
                'group_by' => 'supplier',
                'labels'   => $supplierRevenue->map(fn($r) => $supplierNames[$r['supplier_id']] ?? '#'.$r['supplier_id'])->values(),
                'series'   => [[
                    'name' => 'Revenue',
                    'data' => $supplierRevenue->pluck('revenue')->map(fn($v) => round((float)$v, 2))->values(),
                ]],
            ];
        }

        // 4) Product mix - FIXED: Use created_at
        if ($want->contains('product')) {
            $productRevenue = OrderItem::query()
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->join('master_products', 'master_products.id', '=', 'order_items.product_id')
                ->whereBetween('orders.created_at', [$from, $to]) // FIXED
                ->when($r->product_id, fn($q, $v) => $q->where('order_items.product_id', $v))
                ->selectRaw('
                    order_items.product_id,
                    master_products.product_name,
                    COUNT(DISTINCT orders.id) AS order_count,
                    SUM(orders.total_price) / COUNT(DISTINCT orders.id) AS avg_revenue
                ')
                ->groupBy('order_items.product_id', 'master_products.product_name')
                ->orderByDesc('order_count')
                ->limit(10)
                ->get();

            $charts[] = [
                'id'       => 'product_revenue',
                'title'    => 'Top Products by Order Count',
                'group_by' => 'product',
                'labels'   => $productRevenue->pluck('product_name')->values(),
                'series'   => [[
                    'name' => 'Orders',
                    'data' => $productRevenue->pluck('order_count')->map(fn($v) => (int)$v)->values(),
                ]],
            ];
        }

        // 5) Price buckets
        if ($want->contains('price_bucket')) {
            $b = $this->parseBuckets($r->get('buckets', '0-500,500-1000,1000-2000,2000-5000,5000+'));
            $dist = $this->bucketizePrices($orders, $b);
            $charts[] = [
                'id'       => 'price_dist',
                'title'    => 'Order Value Distribution',
                'group_by' => 'price_bucket',
                'type'     => 'bar',
                'labels'   => array_keys($dist),
                'series'   => [['name' => 'Orders', 'data' => array_values($dist)]],
            ];
        }

        // TABLES

        // Top clients by spend
        $topClients = Orders::query()
            ->join('users as u', 'orders.client_id', '=', 'u.id')
            ->whereBetween('orders.created_at', [$from, $to]) // FIX: Specify orders.created_at
            ->selectRaw('
                orders.client_id,
                u.name AS client_name,
                u.email AS client_email,
                COUNT(orders.id) AS order_count,
                SUM(orders.total_price) AS total_spend
            ')
            ->groupBy('orders.client_id', 'u.name', 'u.email')
            ->orderByDesc('total_spend')
            ->limit(10)
            ->get()
            ->map(fn($r) => [
                'client_id' => $r->client_id,
                'client_name' => $r->client_name,
                'client_email' => $r->client_email,
                'order_count' => (int) $r->order_count,
                'total_spend' => (float) $r->total_spend,
            ])
            ->values();

        // Top suppliers by order count and revenue share - FIXED: Use created_at
        $topSuppliers = OrderItem::query()
        ->join('orders', 'orders.id', '=', 'order_items.order_id')
        ->join('users', 'users.id', '=', 'order_items.supplier_id')
        ->whereBetween('orders.created_at', [$from, $to])
        ->whereIn('orders.workflow', ['Payment Requested', 'Delivered']) // confirm exact values
        ->whereNotNull('order_items.supplier_id')
        ->selectRaw('
            order_items.supplier_id,
            users.name AS supplier_name,
            users.email AS supplier_email,
            COUNT(DISTINCT orders.id) AS order_count,
            SUM(
                (COALESCE(order_items.supplier_unit_cost,0) * COALESCE(order_items.quantity,0))
                + COALESCE(order_items.supplier_delivery_cost,0)
                - COALESCE(order_items.supplier_discount,0)
            ) AS total_revenue
        ')
        ->groupBy('order_items.supplier_id', 'users.name', 'users.email')
        ->orderByDesc('order_count')
        ->limit(10)
        ->get()
        ->map(fn($r) => [
            'supplier_id'    => $r->supplier_id,
            'supplier_name'  => $r->supplier_name,
            'supplier_email' => $r->supplier_email,
            'order_count'    => (int) $r->order_count,
            'revenue'        => (float) $r->total_revenue,
        ])
        ->values();

        // Recent activity feed (last 7 days, up to 15 items)
        $recentActivity = Orders::query()
            ->with(['client:id,name', 'project:id,name'])
            ->whereBetween('created_at', [now()->subDays(7), now()])
            ->orderBy('created_at', 'desc')
            ->limit(15)
            ->get()
            ->map(fn($order) => [
                'id' => $order->id,
                'type' => 'order_created',
                'order_id' => $order->id,
                'po_number' => $order->po_number,
                'client_name' => $order->client->name ?? 'Unknown Client',
                'project_name' => $order->project->name ?? 'N/A',
                'amount' => (float) $order->total_price,
                'workflow' => $order->workflow,
                'timestamp' => $order->created_at->toIso8601String(),
                'time_ago' => $order->created_at->diffForHumans(),
            ])
            ->values();

        // Prepare response filters
        $respFilters = [
            'from'            => $from instanceof Carbon ? $from->toDateString() : (string) $from,
            'to'              => $to instanceof Carbon ? $to->toDateString() : (string) $to,
            'granularity'     => $gran,
            'group_by'        => null,
            'metric'          => 'revenue',
            'client_id'       => $r->client_id,
            'project_id'      => $r->project_id,
            'supplier_id'     => $r->supplier_id,
            'product_id'      => $r->product_id,
            'workflow'        => $r->workflow,
            'payment_status'  => $r->payment_status,
            'delivery_method' => $r->delivery_method,
            'tz'              => $tz,
            'currency'        => 'AUD',
            'charts'          => $want->all(),
        ];

        // System alerts
        $alerts = [];
        if ($awaitingPayment > 0) {
            $alerts[] = [
                'type' => 'warning',
                'priority' => 'high',
                'message' => "{$awaitingPayment} order(s) awaiting payment",
                'action_url' => '/admin/orders?payment_status=Pending',
            ];
        }
        if ($supplierMissing > 0) {
            $alerts[] = [
                'type' => 'error',
                'priority' => 'high',
                'message' => "{$supplierMissing} order(s) missing supplier assignment",
                'action_url' => '/admin/orders?workflow=Supplier+Missing',
            ];
        }
        if ($pendingSuppliers > 0) {
            $alerts[] = [
                'type' => 'info',
                'priority' => 'medium',
                'message' => "{$pendingSuppliers} supplier(s) pending approval",
                'action_url' => '/admin/suppliers?status=pending',
            ];
        }

        return response()->json([
            'filters' => $respFilters,
            'kpis'    => $kpis,
            'charts'  => $charts,
            'tables'  => [
                'top_clients_by_spend' => $topClients,
                'top_suppliers_by_revenue' => $topSuppliers,
                'recent_activity' => $recentActivity,
            ],
            'alerts' => $alerts,
            'metadata' => [
                'generated_at' => now()->toIso8601String(),
                'data_freshness' => 'real-time',
                'total_records_analyzed' => $ordersTotal,
                'period_days' => $periodDays,
            ],
        ]);
    }

    /**
     * Calculate percentage change between current and previous values
     */
    protected function calculatePercentageChange($current, $previous): float
    {
        if ($previous == 0) {
            return $current > 0 ? 100.0 : 0.0;
        }
        return round((($current - $previous) / $previous) * 100, 2);
    }

    protected function parseBuckets(string $spec): array
    {
        $out = [];
        foreach (explode(',', $spec) as $raw) {
            $t = trim($raw);
            if ($t === '') continue;

            if (Str::endsWith($t, '+')) {
                $min = (float) rtrim($t, '+');
                $out[] = ['label' => "{$min}+", 'min' => $min, 'max' => null];
                continue;
            }

            if (preg_match('/^\s*([0-9]*\.?[0-9]+)\s*-\s*([0-9]*\.?[0-9]+)\s*$/', $t, $m)) {
                $min = (float) $m[1];
                $max = (float) $m[2];
                if ($max < $min) [$min, $max] = [$max, $min];
                $label = $min == (int)$min && $max == (int)$max ? "{$min}-{$max}" : "{$min}-{$max}";
                $out[] = ['label' => $label, 'min' => $min, 'max' => $max];
            }
        }
        usort($out, fn($a, $b) => ($a['min'] <=> $b['min']) ?: (($a['max'] ?? INF) <=> ($b['max'] ?? INF)));
        return $out;
    }

    protected function buildPriceBucketCase(array $buckets, string $column = 'total_price'): string
    {
        $cases = [];
        foreach ($buckets as $b) {
            $min = $b['min'];
            $max = $b['max'];
            $label = addslashes($b['label']);
            if (is_null($max)) {
                $cases[] = "WHEN {$column} >= {$min} THEN '{$label}'";
            } else {
                $cases[] = "WHEN {$column} >= {$min} AND {$column} < {$max} THEN '{$label}'";
            }
        }
        $cases[] = "ELSE 'Other'";
        return 'CASE ' . implode(' ', $cases) . ' END';
    }

    protected function bucketizePrices(\Illuminate\Database\Eloquent\Builder $orders, array $buckets): array
    {
        if (empty($buckets)) return [];

        $caseSql = $this->buildPriceBucketCase($buckets, 'total_price');

        $rows = (clone $orders)
            ->selectRaw("{$caseSql} AS bucket, COUNT(*) AS cnt")
            ->groupBy('bucket')
            ->pluck('cnt', 'bucket');

        $ordered = [];
        foreach ($buckets as $b) {
            $label = $b['label'];
            $ordered[$label] = (int) ($rows[$label] ?? 0);
        }
        if (isset($rows['Other'])) $ordered['Other'] = (int)$rows['Other'];

        return $ordered;
    }

    protected function offset(string $tz): string
    {
        try {
            $zone = new DateTimeZone($tz);
        } catch (\Exception $e) {
            $zone = new DateTimeZone('UTC');
        }
        $now = new DateTime('now', $zone);
        $offset = $zone->getOffset($now) / 3600;
        $sign = $offset >= 0 ? '+' : '-';
        $hours = str_pad(abs((int)$offset), 2, '0', STR_PAD_LEFT);
        $minutes = str_pad(abs(($offset * 60) % 60), 2, '0', STR_PAD_LEFT);
        return "{$sign}{$hours}:{$minutes}";
    }
}

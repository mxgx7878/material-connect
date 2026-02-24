<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Orders;
use App\Models\OrderItem;
use App\Models\Invoice;
use App\Models\User;
use Carbon\Carbon;
use DateTimeZone;
use DateTime;

class DashboardController extends Controller
{
    // ─────────────────────────────────────────────────────────────
    // GET /api/admin/dashboard/summary
    // ─────────────────────────────────────────────────────────────
    //
    // Query params:
    //   from            date  (default: start of current month)
    //   to              date  (default: today)
    //   granularity     day|week|month  (default: day)
    //   tz              IANA timezone string (default: Australia/Sydney)
    //   charts          comma-separated: date,status,supplier,product,price_bucket
    //   client_id       filter orders + invoices by client
    //   project_id      filter orders by project
    //   workflow        filter orders by workflow
    //   payment_status  filter orders by payment_status
    //   delivery_method filter orders by delivery_method
    //   supplier_id     filter supplier charts
    //   product_id      filter product charts
    // ─────────────────────────────────────────────────────────────
    public function summary(Request $r)
    {
        // ── Date range ──────────────────────────────────────────
        $from = $r->date('from') ?? now()->startOfMonth();
        $to   = $r->date('to')   ?? now()->endOfDay();
        $gran = $r->get('granularity', 'day');
        $tz   = $r->get('tz', 'Australia/Sydney');

        // Ensure $to covers the full day
        if ($to instanceof Carbon) {
            $to = $to->endOfDay();
        }

        // Previous period — same duration, immediately before $from
        $periodDays = (int) $from->diffInDays($to);
        $prevFrom   = (clone $from)->subDays($periodDays + 1)->startOfDay();
        $prevTo     = (clone $from)->subDay()->endOfDay();

        // ── Shared order filters (applied to both Orders + Invoice joins) ─
        $orderFilters = [
            'client_id'       => $r->client_id,
            'project_id'      => $r->project_id,
            'workflow'        => $r->workflow,
            'payment_status'  => $r->payment_status,
            'delivery_method' => $r->delivery_method,
        ];

        // ── Base order query builder (helper closure) ────────────
        // Returns a fresh query with shared filters but NO date constraint.
        // Callers add their own whereBetween so we can reuse for prev period.
        $baseOrders = function () use ($orderFilters) {
            return Orders::query()
                ->where('is_archived', false)
                ->when($orderFilters['client_id'],       fn($q, $v) => $q->where('client_id',       $v))
                ->when($orderFilters['project_id'],      fn($q, $v) => $q->where('project_id',      $v))
                ->when($orderFilters['workflow'],         fn($q, $v) => $q->where('workflow',         $v))
                ->when($orderFilters['payment_status'],  fn($q, $v) => $q->where('payment_status',  $v))
                ->when($orderFilters['delivery_method'], fn($q, $v) => $q->where('delivery_method', $v));
        };

        // ── Base invoice query builder ───────────────────────────
        // Invoices are scoped by issued_date and optionally client_id.
        // We exclude Cancelled and Void invoices from all monetary figures.
        $baseInvoices = function () use ($r) {
            return Invoice::query()
                ->whereNotIn('status', ['Cancelled', 'Void'])
                ->when($r->client_id, fn($q, $v) => $q->where('client_id', $v));
        };

        // ════════════════════════════════════════════════════════
        // ── CURRENT PERIOD — ORDER KPIs ──────────────────────────
        // ════════════════════════════════════════════════════════
        $orders = $baseOrders()->whereBetween('created_at', [$from, $to]);

        $ordersTotal      = (int) (clone $orders)->count();
        $awaitingPayment  = (int) (clone $orders)->whereIn('payment_status', ['Pending', 'Requested'])->count();
        $supplierMissing  = (int) (clone $orders)->where('workflow', 'Supplier Missing')->count();
        $completedOrders  = (int) (clone $orders)->where('workflow', 'Delivered')->count();
        $cancelledOrders  = (int) (clone $orders)->where('order_status', 'Cancelled')->count();
        $activeClients    = (int) (clone $orders)->distinct('client_id')->count('client_id');

        // Unique active suppliers from order items in this period
        $currentOrderIds  = (clone $orders)->pluck('id');
        $activeSuppliers  = OrderItem::whereIn('order_id', $currentOrderIds)
            ->whereNotNull('supplier_id')
            ->distinct('supplier_id')
            ->count('supplier_id');

        // ════════════════════════════════════════════════════════
        // ── CURRENT PERIOD — INVOICE KPIs ────────────────────────
        //
        // Revenue is now entirely invoice-driven.
        //   total_invoiced  = everything issued in period (ex Cancelled/Void)
        //   paid_amount     = only status='Paid'
        //   outstanding     = Sent + Partially Paid + Overdue
        //   overdue_count   = count where status='Overdue'
        //   invoice_count   = total invoices issued in period
        // ════════════════════════════════════════════════════════
        $invoices = $baseInvoices()->whereBetween('issued_date', [$from, $to]);

        $invoiceCount   = (int) (clone $invoices)->count();
        $totalInvoiced  = (float) (clone $invoices)->sum('total_amount');
        $paidAmount     = (float) (clone $invoices)->where('status', 'Paid')->sum('total_amount');
        $outstanding    = (float) (clone $invoices)->whereIn('status', ['Sent', 'Partially Paid', 'Overdue'])->sum('total_amount');
        $overdueCount   = (int) (clone $invoices)->where('status', 'Overdue')->count();
        $avgInvoiceValue = $invoiceCount > 0 ? round($totalInvoiced / $invoiceCount, 2) : 0;

        // Collection rate = what portion of invoiced has been paid
        $collectionRate = $totalInvoiced > 0 ? round(($paidAmount / $totalInvoiced) * 100, 2) : 0;

        // ════════════════════════════════════════════════════════
        // ── PREVIOUS PERIOD (comparison) ─────────────────────────
        // ════════════════════════════════════════════════════════
        $prevOrders   = $baseOrders()->whereBetween('created_at', [$prevFrom, $prevTo]);
        $prevInvoices = $baseInvoices()->whereBetween('issued_date', [$prevFrom, $prevTo]);

        $ordersTotalPrev    = (int) (clone $prevOrders)->count();
        $completedOrdersPrev= (int) (clone $prevOrders)->where('workflow', 'Delivered')->count();
        $activeClientsPrev  = (int) (clone $prevOrders)->distinct('client_id')->count('client_id');
        $totalInvoicedPrev  = (float) (clone $prevInvoices)->sum('total_amount');
        $paidAmountPrev     = (float) (clone $prevInvoices)->where('status', 'Paid')->sum('total_amount');

        // ── System-wide totals (not period-filtered) ─────────────
        $totalClients    = User::where('role', 'client')->count();
        $totalSuppliers  = User::where('role', 'supplier')->whereIn('status', ['approved', 'active'])->count();
        $pendingSuppliers= User::where('role', 'supplier')->where('status', 'pending')->count();

        // ── Performance ───────────────────────────────────────────
        $cancellationRate = $ordersTotal > 0
            ? round(($cancelledOrders / $ordersTotal) * 100, 2)
            : 0;

        $clientsWithMultipleOrders = Orders::where('is_archived', false)
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('client_id, COUNT(*) as order_count')
            ->groupBy('client_id')
            ->having('order_count', '>', 1)
            ->count();
        $repeatClientRate = $activeClients > 0
            ? round(($clientsWithMultipleOrders / $activeClients) * 100, 2)
            : 0;

        // ── KPI payload ───────────────────────────────────────────
        $kpis = [
            // Orders
            'orders_total'       => $ordersTotal,
            'orders_total_prev'  => $ordersTotalPrev,
            'orders_change'      => $this->pctChange($ordersTotal, $ordersTotalPrev),

            'completed_orders'       => $completedOrders,
            'completed_orders_prev'  => $completedOrdersPrev,
            'completed_change'       => $this->pctChange($completedOrders, $completedOrdersPrev),

            // Revenue — invoice-based
            'invoice_count'          => $invoiceCount,
            'total_invoiced'         => round($totalInvoiced, 2),
            'total_invoiced_prev'    => round($totalInvoicedPrev, 2),
            'invoiced_change'        => $this->pctChange($totalInvoiced, $totalInvoicedPrev),

            'paid_amount'            => round($paidAmount, 2),
            'paid_amount_prev'       => round($paidAmountPrev, 2),
            'paid_change'            => $this->pctChange($paidAmount, $paidAmountPrev),

            'outstanding_amount'     => round($outstanding, 2),
            'overdue_count'          => $overdueCount,
            'avg_invoice_value'      => $avgInvoiceValue,
            'collection_rate'        => $collectionRate,   // % of invoiced that is paid

            // Clients / Suppliers
            'active_clients'         => $activeClients,
            'active_clients_prev'    => $activeClientsPrev,
            'active_clients_change'  => $this->pctChange($activeClients, $activeClientsPrev),
            'active_suppliers'       => $activeSuppliers,

            // Alerts
            'awaiting_payment'       => $awaitingPayment,
            'supplier_missing'       => $supplierMissing,
            'pending_supplier_approvals' => $pendingSuppliers,

            // Performance
            'cancellation_rate'  => $cancellationRate,
            'repeat_client_rate' => $repeatClientRate,

            // System totals
            'total_clients'   => $totalClients,
            'total_suppliers' => $totalSuppliers,
        ];

        // ════════════════════════════════════════════════════════
        // ── CHARTS ───────────────────────────────────────────────
        // ════════════════════════════════════════════════════════
        $want   = collect(explode(',', $r->get('charts', 'date,status,supplier,product,price_bucket')))
            ->map(fn($x) => trim($x))->filter()->values();
        $charts = [];

        // 1) Revenue & invoice count over time (invoice issued_date based)
        if ($want->contains('date')) {
            $tzOffset = $this->offset($tz);
            $bucket = match ($gran) {
                'week'  => "DATE_FORMAT(CONVERT_TZ(issued_date, '+00:00', '{$tzOffset}'), '%x-%v')",
                'month' => "DATE_FORMAT(CONVERT_TZ(issued_date, '+00:00', '{$tzOffset}'), '%Y-%m')",
                default => "DATE(CONVERT_TZ(issued_date, '+00:00', '{$tzOffset}'))",
            };

            $rows = (clone $invoices)
                ->selectRaw("
                    {$bucket} AS bucket,
                    COUNT(*) AS invoice_count,
                    SUM(total_amount) AS total_invoiced,
                    SUM(CASE WHEN status = 'Paid' THEN total_amount ELSE 0 END) AS paid_amount
                ")
                ->groupBy('bucket')
                ->orderBy('bucket')
                ->get();

            $charts[] = [
                'id'       => 'time_revenue',
                'title'    => 'Invoiced & Paid Revenue Over Time',
                'group_by' => 'date',
                'labels'   => $rows->pluck('bucket')->values(),
                'series'   => [
                    ['name' => 'Total Invoiced', 'data' => $rows->pluck('total_invoiced')->map(fn($v) => round((float)$v, 2))->values()],
                    ['name' => 'Paid',           'data' => $rows->pluck('paid_amount')->map(fn($v) => round((float)$v, 2))->values()],
                    ['name' => 'Invoices',        'data' => $rows->pluck('invoice_count')->map(fn($v) => (int)$v)->values()],
                ],
            ];
        }

        // 2) Order workflow / status distribution
        if ($want->contains('status')) {
            $statusDist = (clone $orders)
                ->selectRaw('workflow, COUNT(*) AS count')
                ->groupBy('workflow')
                ->orderByDesc('count')
                ->get();

            $charts[] = [
                'id'       => 'order_status',
                'title'    => 'Order Workflow Distribution',
                'group_by' => 'status',
                'type'     => 'donut',
                'labels'   => $statusDist->pluck('workflow')->values(),
                'series'   => $statusDist->pluck('count')->map(fn($v) => (int)$v)->values(),
            ];

            // Invoice status distribution
            $invStatusDist = (clone $invoices)
                ->selectRaw('status, COUNT(*) AS count, SUM(total_amount) AS total')
                ->groupBy('status')
                ->orderByDesc('count')
                ->get();

            $charts[] = [
                'id'       => 'invoice_status',
                'title'    => 'Invoice Status Distribution',
                'group_by' => 'invoice_status',
                'type'     => 'donut',
                'labels'   => $invStatusDist->pluck('status')->values(),
                'series'   => $invStatusDist->pluck('count')->map(fn($v) => (int)$v)->values(),
                'amounts'  => $invStatusDist->pluck('total')->map(fn($v) => round((float)$v, 2))->values(),
            ];
        }

        // 3) Top suppliers by supplier cost (order_items cost)
        //    We keep this order-item based (supplier cost = what we pay suppliers)
        if ($want->contains('supplier')) {
            $supplierRevenue = OrderItem::query()
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->whereBetween('orders.created_at', [$from, $to])
                ->where('orders.is_archived', false)
                ->whereNotNull('order_items.supplier_id')
                ->when($r->supplier_id, fn($q, $v) => $q->where('order_items.supplier_id', $v))
                ->selectRaw('
                    order_items.supplier_id,
                    SUM(
                        (COALESCE(order_items.supplier_unit_cost, 0) * COALESCE(order_items.quantity, 0))
                        - COALESCE(order_items.supplier_discount, 0)
                    ) AS cost
                ')
                ->groupBy('order_items.supplier_id')
                ->orderByDesc('cost')
                ->limit(10)
                ->get();

            $supplierIds   = $supplierRevenue->pluck('supplier_id');
            $supplierNames = User::whereIn('id', $supplierIds)->pluck('name', 'id');

            $charts[] = [
                'id'       => 'supplier_cost',
                'title'    => 'Top Suppliers by Cost',
                'group_by' => 'supplier',
                'labels'   => $supplierRevenue->map(fn($row) => $supplierNames[$row->supplier_id] ?? '#'.$row->supplier_id)->values(),
                'series'   => [[
                    'name' => 'Supplier Cost',
                    'data' => $supplierRevenue->pluck('cost')->map(fn($v) => round((float)$v, 2))->values(),
                ]],
            ];
        }

        // 4) Top products by order count
        if ($want->contains('product')) {
            $productMix = OrderItem::query()
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->join('master_products', 'master_products.id', '=', 'order_items.product_id')
                ->whereBetween('orders.created_at', [$from, $to])
                ->where('orders.is_archived', false)
                ->when($r->product_id, fn($q, $v) => $q->where('order_items.product_id', $v))
                ->selectRaw('
                    order_items.product_id,
                    master_products.product_name,
                    COUNT(DISTINCT orders.id) AS order_count,
                    SUM(order_items.quantity) AS total_qty
                ')
                ->groupBy('order_items.product_id', 'master_products.product_name')
                ->orderByDesc('order_count')
                ->limit(10)
                ->get();

            $charts[] = [
                'id'       => 'product_mix',
                'title'    => 'Top Products by Order Count',
                'group_by' => 'product',
                'labels'   => $productMix->pluck('product_name')->values(),
                'series'   => [[
                    'name' => 'Orders',
                    'data' => $productMix->pluck('order_count')->map(fn($v) => (int)$v)->values(),
                ], [
                    'name' => 'Qty',
                    'data' => $productMix->pluck('total_qty')->map(fn($v) => round((float)$v, 2))->values(),
                ]],
            ];
        }

        // 5) Invoice value distribution (price buckets)
        if ($want->contains('price_bucket')) {
            $bucketDefs = $this->parseBuckets($r->get('buckets', '0-500,500-1000,1000-2000,2000-5000,5000+'));
            $dist = $this->bucketizeInvoices($invoices, $bucketDefs);
            $charts[] = [
                'id'       => 'invoice_value_dist',
                'title'    => 'Invoice Value Distribution',
                'group_by' => 'price_bucket',
                'type'     => 'bar',
                'labels'   => array_keys($dist),
                'series'   => [['name' => 'Invoices', 'data' => array_values($dist)]],
            ];
        }

        // ════════════════════════════════════════════════════════
        // ── TABLES ───────────────────────────────────────────────
        // ════════════════════════════════════════════════════════

        // Top clients by total invoiced (paid + outstanding)
        $topClients = Invoice::query()
            ->join('users', 'users.id', '=', 'invoices.client_id')
            ->whereNotIn('invoices.status', ['Cancelled', 'Void'])
            ->whereBetween('invoices.issued_date', [$from, $to])
            ->when($r->client_id, fn($q, $v) => $q->where('invoices.client_id', $v))
            ->selectRaw('
                invoices.client_id,
                users.name  AS client_name,
                users.email AS client_email,
                COUNT(invoices.id) AS invoice_count,
                SUM(invoices.total_amount) AS total_invoiced,
                SUM(CASE WHEN invoices.status = "Paid" THEN invoices.total_amount ELSE 0 END) AS paid_amount
            ')
            ->groupBy('invoices.client_id', 'users.name', 'users.email')
            ->orderByDesc('total_invoiced')
            ->limit(10)
            ->get()
            ->map(fn($row) => [
                'client_id'      => $row->client_id,
                'client_name'    => $row->client_name,
                'client_email'   => $row->client_email,
                'invoice_count'  => (int) $row->invoice_count,
                'total_invoiced' => round((float) $row->total_invoiced, 2),
                'paid_amount'    => round((float) $row->paid_amount, 2),
            ])
            ->values();

        // Top suppliers by their cost (order item level)
        $topSuppliers = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('users',  'users.id',  '=', 'order_items.supplier_id')
            ->whereBetween('orders.created_at', [$from, $to])
            ->where('orders.is_archived', false)
            ->whereNotNull('order_items.supplier_id')
            ->selectRaw('
                order_items.supplier_id,
                users.name  AS supplier_name,
                users.email AS supplier_email,
                COUNT(DISTINCT orders.id) AS order_count,
                SUM(
                    (COALESCE(order_items.supplier_unit_cost, 0) * COALESCE(order_items.quantity, 0))
                    - COALESCE(order_items.supplier_discount, 0)
                ) AS total_cost
            ')
            ->groupBy('order_items.supplier_id', 'users.name', 'users.email')
            ->orderByDesc('order_count')
            ->limit(10)
            ->get()
            ->map(fn($row) => [
                'supplier_id'    => $row->supplier_id,
                'supplier_name'  => $row->supplier_name,
                'supplier_email' => $row->supplier_email,
                'order_count'    => (int) $row->order_count,
                'total_cost'     => round((float) $row->total_cost, 2),
            ])
            ->values();

        // Recent invoices activity (last 10 invoices issued)
        $recentInvoices = Invoice::query()
            ->with(['order:id,po_number', 'client:id,name'])
            ->whereNotIn('status', ['Cancelled', 'Void'])
            ->orderByDesc('issued_date')
            ->limit(10)
            ->get()
            ->map(fn($inv) => [
                'invoice_id'     => $inv->id,
                'invoice_number' => $inv->invoice_number,
                'client_name'    => $inv->client->name   ?? 'Unknown',
                'po_number'      => $inv->order->po_number ?? 'N/A',
                'total_amount'   => round((float) $inv->total_amount, 2),
                'status'         => $inv->status,
                'issued_date'    => $inv->issued_date?->toDateString(),
                'due_date'       => $inv->due_date?->toDateString(),
                'paid_at'        => $inv->paid_at?->toIso8601String(),
                'time_ago'       => $inv->issued_date?->diffForHumans(),
            ])
            ->values();

        // ── Alerts ────────────────────────────────────────────────
        $alerts = [];

        if ($overdueCount > 0) {
            $alerts[] = [
                'type'       => 'error',
                'priority'   => 'high',
                'message'    => "{$overdueCount} invoice(s) are overdue",
                'action_url' => '/admin/invoices?status=Overdue',
            ];
        }
        if (round($outstanding, 2) > 0) {
            $alerts[] = [
                'type'       => 'warning',
                'priority'   => 'high',
                'message'    => '$'.number_format($outstanding, 2).' outstanding across invoices',
                'action_url' => '/admin/invoices?status=Sent,Partially+Paid,Overdue',
            ];
        }
        if ($awaitingPayment > 0) {
            $alerts[] = [
                'type'       => 'warning',
                'priority'   => 'medium',
                'message'    => "{$awaitingPayment} order(s) awaiting payment request",
                'action_url' => '/admin/orders?payment_status=Pending',
            ];
        }
        if ($supplierMissing > 0) {
            $alerts[] = [
                'type'       => 'error',
                'priority'   => 'high',
                'message'    => "{$supplierMissing} order(s) missing a supplier",
                'action_url' => '/admin/orders?workflow=Supplier+Missing',
            ];
        }
        if ($pendingSuppliers > 0) {
            $alerts[] = [
                'type'       => 'info',
                'priority'   => 'medium',
                'message'    => "{$pendingSuppliers} supplier(s) pending approval",
                'action_url' => '/admin/suppliers?status=pending',
            ];
        }

        // ── Response ──────────────────────────────────────────────
        return response()->json([
            'filters' => [
                'from'            => $from instanceof Carbon ? $from->toDateString() : (string) $from,
                'to'              => $to instanceof Carbon   ? $to->toDateString()   : (string) $to,
                'granularity'     => $gran,
                'tz'              => $tz,
                'currency'        => 'AUD',
                'client_id'       => $r->client_id,
                'project_id'      => $r->project_id,
                'supplier_id'     => $r->supplier_id,
                'product_id'      => $r->product_id,
                'workflow'        => $r->workflow,
                'payment_status'  => $r->payment_status,
                'delivery_method' => $r->delivery_method,
                'charts'          => $want->all(),
            ],
            'kpis'   => $kpis,
            'charts' => $charts,
            'tables' => [
                'top_clients_by_invoiced'  => $topClients,
                'top_suppliers_by_cost'    => $topSuppliers,
                'recent_invoices'          => $recentInvoices,
            ],
            'alerts'   => $alerts,
            'metadata' => [
                'generated_at'            => now()->toIso8601String(),
                'period_days'             => $periodDays,
                'total_orders_analyzed'   => $ordersTotal,
                'total_invoices_analyzed' => $invoiceCount,
            ],
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────

    /**
     * Percentage change from previous to current.
     * Returns +100.0 when previous is 0 and current > 0,
     * returns 0.0 when both are 0.
     */
    protected function pctChange(float $current, float $previous): float
    {
        if ($previous == 0) {
            return $current > 0 ? 100.0 : 0.0;
        }
        return round((($current - $previous) / $previous) * 100, 2);
    }

    /**
     * Convert IANA timezone name to a MySQL-compatible UTC offset string.
     * e.g. "Australia/Sydney" → "+10:00" or "+11:00" during DST.
     */
    protected function offset(string $tz): string
    {
        try {
            $dt     = new DateTime('now', new DateTimeZone($tz));
            $secs   = $dt->getOffset();
            $sign   = $secs >= 0 ? '+' : '-';
            $secs   = abs($secs);
            $h      = str_pad((int) ($secs / 3600), 2, '0', STR_PAD_LEFT);
            $m      = str_pad((int) (($secs % 3600) / 60), 2, '0', STR_PAD_LEFT);
            return "{$sign}{$h}:{$m}";
        } catch (\Exception $e) {
            return '+00:00';
        }
    }

    /**
     * Parse a bucket definition string like "0-500,500-1000,5000+"
     * into an ordered array of ['min' => x, 'max' => y|null, 'label' => '...'].
     */
    protected function parseBuckets(string $raw): array
    {
        $buckets = [];
        foreach (explode(',', $raw) as $part) {
            $part = trim($part);
            if (str_ends_with($part, '+')) {
                $min = (float) rtrim($part, '+');
                $buckets[$part] = ['min' => $min, 'max' => null];
            } elseif (str_contains($part, '-')) {
                [$a, $b] = explode('-', $part, 2);
                $buckets[$part] = ['min' => (float)$a, 'max' => (float)$b];
            }
        }
        return $buckets;
    }

    /**
     * Count invoices per price bucket.
     * $query should already be a scoped invoice query builder (cloned internally).
     */
    protected function bucketizeInvoices($query, array $buckets): array
    {
        $dist = [];
        foreach ($buckets as $label => $range) {
            $q = clone $query;
            if ($range['max'] === null) {
                $q->where('total_amount', '>=', $range['min']);
            } else {
                $q->whereBetween('total_amount', [$range['min'], $range['max']]);
            }
            $dist[$label] = (int) $q->count();
        }
        return $dist;
    }
}
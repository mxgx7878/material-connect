<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MasterProducts;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * PublicController
 * ----------------
 * All auth-free endpoints the public marketing website consumes. Registered
 * OUTSIDE any auth:sanctum group (see routes below).
 *
 *  - Only genuinely listable products are exposed: approved, and offered by at
 *    least one active supplier with In Stock / Limited availability.
 *  - NO price, supplier identity, cost, margin or offer data is ever returned to
 *    the public site — pricing is site-specific and lives in the portal.
 *  - Service areas are coarsened to region labels only.
 */
class PublicController extends Controller
{
    /** Product types that should never surface on the public catalogue. */
    private const EXCLUDED_TYPES = ['pavers', 'paver', 'bricks', 'brick', 'blocks', 'block'];

    /** Offer availability states that count as "available". */
    private const AVAILABLE_STATUSES = ['In Stock', 'Limited'];

    /**
     * GET /api/public/products
     * Public product catalogue (names, types, photos, categories). Never prices.
     * Returns { data, meta }. Query params: search, product_type, category, per_page, page.
     */
    public function products(Request $request)
    {
        $perPage = min((int) $request->get('per_page', 60), 200);
        $page    = (int) $request->get('page', 1);

        $query = $this->listableProducts(MasterProducts::query());

        if ($search = trim((string) $request->get('search', ''))) {
            $query->where('product_name', 'like', "%{$search}%");
        }
        if ($request->filled('product_type')) {
            $query->where('product_type', $request->get('product_type'));
        }
        if ($request->filled('category')) {
            $query->where('category', $request->get('category'));
        }

        $query->with('category')->orderBy('product_name', 'asc');

        // Select ONLY public-safe columns. `category` (FK) is needed for the relation.
        $products = $query->paginate(
            $perPage,
            ['id', 'product_name', 'product_type', 'slug', 'photo', 'specifications', 'unit_of_measure', 'category'],
            'page',
            $page
        );

        return response()->json([
            'data' => $products->items(),
            'meta' => [
                'current_page' => $products->currentPage(),
                'per_page'     => $products->perPage(),
                'total'        => $products->total(),
                'last_page'    => $products->lastPage(),
            ],
        ], 200);
    }

    /**
     * GET /api/public/product-types
     * Distinct, Title-Cased types that actually have listable products. Cached briefly.
     */
    public function productTypes()
    {
        $types = Cache::remember('public:product-types', now()->addMinutes(10), function () {
            return $this->listableProducts(MasterProducts::query())
                ->whereNotNull('product_type')
                ->where('product_type', '!=', '')
                ->distinct()
                ->orderBy('product_type')
                ->pluck('product_type')
                ->map(fn ($t) => Str::title(trim($t)))
                ->unique()
                ->values()
                ->map(fn ($t) => ['product_type' => $t]);
        });

        return response()->json(['data' => $types], 200);
    }

    /**
     * GET /api/public/service-areas
     * Anonymised supplier coverage: region labels only. No coords, radii, identity or counts.
     */
    public function serviceAreas(Request $request)
    {
        $result = Cache::remember('public:service-areas', now()->addMinutes(15), function () {
            $suppliers = User::query()
                ->where('role', 'supplier')
                ->where('status', 'active')
                ->whereNotNull('delivery_zones')
                ->where('delivery_zones', '!=', '[]')
                ->pluck('delivery_zones');

            $areas = collect();

            foreach ($suppliers as $raw) {
                $zones = is_string($raw) ? json_decode($raw, true) : $raw;
                if (!is_array($zones)) {
                    continue;
                }

                foreach ($zones as $z) {
                    $label = trim((string) ($z['address'] ?? ''));
                    if ($label === '') {
                        continue;
                    }

                    // Coarsen to a region label: keep suburb/region + state, drop street detail.
                    $parts  = array_map('trim', explode(',', $label));
                    $region = $parts[count($parts) >= 2 ? count($parts) - 2 : 0] ?? $label;
                    $areas->push($region);
                }
            }

            return $areas
                ->filter()
                ->unique(fn ($v) => mb_strtolower($v))
                ->sort()
                ->values();
        });

        return response()->json(['service_areas' => $result], 200);
    }

    /**
     * Shared "publicly listable" scope — one place so products & product-types can't drift.
     */
    private function listableProducts($query)
    {
        return $query
            ->where('is_approved', true)
            ->whereHas('supplierOffers', function ($q) {
                $q->where('status', 'Approved')
                  ->whereIn('availability_status', self::AVAILABLE_STATUSES);
            })
            ->whereRaw(
                'LOWER(product_type) NOT IN (' . implode(',', array_fill(0, count(self::EXCLUDED_TYPES), '?')) . ')',
                self::EXCLUDED_TYPES
            );
    }
}
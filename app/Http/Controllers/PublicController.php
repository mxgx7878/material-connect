<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MasterProducts;
use App\Models\User;
use App\Models\Inquiry;
use App\Models\SupplierOffers;
use App\Services\PricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * PublicController
 * ----------------
 * All auth-free endpoints the public marketing website consumes.
 *
 * Location logic is IDENTICAL to OrderController (portal):
 *  - No lat/long  → every product that has an approved, In Stock / Limited offer.
 *  - lat/long set → only products offered by suppliers whose delivery_zones
 *                   ({lat, long, radius, address}) cover that point (haversine km).
 *
 * Accepted location params (either pair works):
 *  - lat / lng
 *  - delivery_lat / delivery_long   (same names the portal uses)
 *
 * Customer price only (offer × PricingService margin). Raw supplier cost,
 * supplier identity and zone coordinates are never returned.
 */
class PublicController extends Controller
{
    /** Product types that should never surface on the public catalogue (same as portal). */
    private const EXCLUDED_TYPES = ['pavers', 'paver', 'bricks', 'brick', 'blocks', 'block'];

    /** Offer availability states that count as "available". */
    private const AVAILABLE_STATUSES = ['In Stock', 'Limited'];

    /**
     * GET /api/public/products
     * Mirrors OrderController@getClientProducts.
     */
    public function products(Request $request)
    {
        $perPage = min((int) $request->get('per_page', 60), 200);
        $page    = (int) $request->get('page', 1);

        [$hasLocation, $lat, $lng] = $this->locationFromRequest($request);

        // ---- Address diya ho: sirf zone wale suppliers (portal jaisa) ----
        $supplierIdsInZone = null;
        if ($hasLocation) {
            $supplierIdsInZone = $this->supplierIdsInZone($lat, $lng);

            // No supplier covers this location → empty (frontend shows "Let us source it")
            if (empty($supplierIdsInZone)) {
                return $this->emptyPage($page, $perPage);
            }
        }

        // ---- Approved + available offers per product (portal wala aggregate) ----
        $offersAgg = SupplierOffers::query()
            ->select('supplier_offers.master_product_id')
            ->selectRaw('COUNT(DISTINCT supplier_offers.supplier_id) AS suppliers_count')
            ->join('users as suppliers', 'suppliers.id', '=', 'supplier_offers.supplier_id')
            ->where('supplier_offers.status', 'Approved')
            ->whereIn('supplier_offers.availability_status', self::AVAILABLE_STATUSES);

        if ($supplierIdsInZone !== null) {
            $offersAgg->whereIn('supplier_offers.supplier_id', $supplierIdsInZone);
        }

        $offersAgg->groupBy('supplier_offers.master_product_id');

        // ---- Main query: sirf woh products jin par offer hai ----
        $query = MasterProducts::query()
            ->joinSub($offersAgg, 'oa', fn ($join) => $join->on('oa.master_product_id', '=', 'master_products.id'))
            ->select(
                // Public-safe columns only. `category` (FK) is needed for the relation.
                'master_products.id',
                'master_products.product_name',
                'master_products.product_type',
                'master_products.slug',
                'master_products.photo',
                'master_products.specifications',
                'master_products.unit_of_measure',
                'master_products.category',
                'oa.suppliers_count'
            );

        if ($search = trim((string) $request->get('search', ''))) {
            $query->where('master_products.product_name', 'like', "%{$search}%");
        }
        if ($request->filled('product_type')) {
            $query->where('master_products.product_type', $request->get('product_type'));
        }
        if ($request->filled('category')) {
            $query->where('master_products.category', $request->get('category'));
        }
        // ids=1,2,3 — used by the public order page to load the materials a
        // customer added from /materials ("Add to order") or kept in their cart.
        if ($request->filled('ids')) {
            $ids = collect(explode(',', (string) $request->get('ids')))
                ->map(fn ($id) => (int) trim($id))
                ->filter(fn ($id) => $id > 0)
                ->unique()
                ->take(50)
                ->values()
                ->all();
            $query->whereIn('master_products.id', $ids ?: [0]);
        }

        // Exclude certain product types (same as portal)
        $query->whereNotIn('master_products.product_type', self::EXCLUDED_TYPES);

        // Sort (same as portal)
        $query->orderBy('master_products.product_name', 'asc');

        $products = $query->with('category')->paginate($perPage, ['*'], 'page', $page);

        // Customer prices for this page only (margin applied here, never raw cost).
        // Zone set → prices only from suppliers covering the site.
        $prices = $this->customerPrices(
            $products->getCollection()->pluck('id')->all(),
            $supplierIdsInZone
        );

        $products->getCollection()->transform(function ($p) use ($hasLocation, $prices) {
            $p->price_min       = $prices[$p->id]['min'] ?? null;
            $p->price_max       = $prices[$p->id]['max'] ?? null;
            $p->suppliers_count = (int) $p->suppliers_count;
            // Location set = deliverable (pre-filtered); no location = unknown
            $p->is_available    = $hasLocation ? true : null;
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
        ], 200);
    }

    /**
     * GET /api/public/product-types
     * Distinct, Title-Cased types that actually have listable products.
     * Location diya ho to sirf us area ke types; warna cached global list.
     */
    public function productTypes(Request $request)
    {
        [$hasLocation, $lat, $lng] = $this->locationFromRequest($request);

        $build = function (?array $supplierIds) {
            return $this->listableProducts(MasterProducts::query(), $supplierIds)
                ->whereNotNull('product_type')
                ->where('product_type', '!=', '')
                ->distinct()
                ->orderBy('product_type')
                ->pluck('product_type')
                ->map(fn ($t) => Str::title(trim($t)))
                ->unique()
                ->values()
                ->map(fn ($t) => ['product_type' => $t]);
        };

        if ($hasLocation) {
            $supplierIds = $this->supplierIdsInZone($lat, $lng);
            $types = empty($supplierIds) ? collect() : $build($supplierIds);
        } else {
            $types = Cache::remember('public:product-types', now()->addMinutes(10), fn () => $build(null));
        }

        return response()->json(['data' => $types], 200);
    }

    /**
     * GET /api/public/service-areas
     * - lat/long diya ho: sirf woh areas jin ka zone is address ko cover karta hai + is_covered
     * - na diya ho: unique area names (koi coords/radius/supplier identity nahi)
     */
    public function serviceAreas(Request $request)
    {
        [$hasLocation, $lat, $lng] = $this->locationFromRequest($request);

        $rows = User::query()
            ->where('role', 'supplier')
            ->where('status', 'active')
            ->whereNotNull('delivery_zones')
            ->where('delivery_zones', '!=', '[]')
            ->pluck('delivery_zones');

        $labels  = [];
        $covered = false;

        foreach ($rows as $raw) {
            foreach ($this->decodeZones($raw) as $z) {
                if (!isset($z['lat'], $z['long'], $z['radius'])) continue;

                if ($hasLocation) {
                    $inZone = $this->haversineKm($lat, $lng, (float) $z['lat'], (float) $z['long'])
                              <= (float) $z['radius'];
                    if (!$inZone) continue;
                    $covered = true;
                }

                $label = $this->zoneLabel($z);
                if ($label) {
                    $labels[strtolower($label)] = $label;
                }
            }
        }

        ksort($labels);

        return response()->json([
            'service_areas' => array_values($labels),          // names only
            'is_covered'    => $hasLocation ? $covered : null, // address diya ho to yes/no
        ], 200);
    }

    /**
     * POST /api/public/inquiries
     * One endpoint for every public form (rfq, sourcing, supplier, software, contact,
     * product_inquiry). Saves to `inquiries`. No email — read them in the admin list.
     */
    public function storeInquiry(Request $request)
    {
        
       
        // Honeypot: bots fill this hidden field. Silently accept + drop.
        if ($request->filled('company_url')) {
            return response()->json(['success' => true], 200);
        }

        $type = (string) $request->input('type');
        $allowed = ['rfq', 'sourcing', 'supplier', 'software', 'contact', 'product_inquiry','newsletter'];
        if (!in_array($type, $allowed, true)) {
            return response()->json(['success' => false, 'message' => 'Invalid inquiry type.'], 422);
        }
    
        $base = [
            'email'      => ['required', 'email', 'max:190'],
            'name'       => ['nullable', 'string', 'max:150'],
            'phone'      => ['nullable', 'string', 'max:50'],
            'company'    => ['nullable', 'string', 'max:200'],
            'product_id' => ['nullable', 'integer'],
            'suburb'     => ['nullable', 'string', 'max:120'],
            'postcode'   => ['nullable', 'string', 'max:12'],
            'lat'        => ['nullable', 'numeric'],
            'lng'        => ['nullable', 'numeric'],
            'files'      => ['nullable', 'array'],
            'files.*'    => ['string', 'max:2048'],
        ];
        

        $perType = match ($type) {
            'rfq'             => ['name' => ['required', 'string', 'max:150'], 'materials' => ['required', 'string']],
            'sourcing'        => ['name' => ['required', 'string', 'max:150'], 'outcome'   => ['required', 'string']],
            'supplier'        => ['legal_name' => ['required', 'string', 'max:200']],
            'software'        => ['company' => ['required', 'string', 'max:200']],
            'contact'         => ['email' => ['required', 'string', 'max:150'], 'message' => ['required', 'string']],
            'product_inquiry' => ['product_id' => ['required', 'integer']],
            'newsletter'      => ['email'=>['required','string','max:150']],
            default           => [],
        };

        $request->validate(array_merge($base, $perType));

        // First-class columns; everything else goes into payload JSON.
        $cols = ['type', 'name', 'company', 'email', 'phone', 'contact_method', 'subject', 'message',
                 'product_id', 'suburb', 'postcode', 'lat', 'lng', 'files', 'source', 'company_url'];
        $payload = $request->except($cols);

        $inquiry = Inquiry::create([
            'type'           => $type,
            'name'           => $request->input('name'),
            'company'        => $request->input('company') ?? $request->input('legal_name'),
            'email'          => $request->input('email'),
            'phone'          => $request->input('phone'),
            'contact_method' => $request->input('contact_method'),
            'subject'        => $request->input('subject'),
            'message'        => $request->input('message')
                                ?? $request->input('outcome')
                                ?? $request->input('materials'),
            'product_id'     => $request->input('product_id'),
            'suburb'         => $request->input('suburb'),
            'postcode'       => $request->input('postcode'),
            'lat'            => $request->input('lat'),
            'lng'            => $request->input('lng'),
            'payload'        => $payload,
            'files'          => $request->input('files', []),
            'source'         => $request->input('source'),
            'status'         => 'new',
            'ip_address'     => $request->ip(),
            'user_agent'     => substr((string) $request->userAgent(), 0, 255),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Thanks — your enquiry has been received.',
            'id'      => $inquiry->id,
        ], 201);
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    /**
     * Reads location from either lat/lng or delivery_lat/delivery_long.
     * @return array{0: bool, 1: float|null, 2: float|null}
     */
    private function locationFromRequest(Request $request): array
    {
        $lat = $request->get('lat', $request->get('delivery_lat'));
        $lng = $request->get('lng', $request->get('delivery_long', $request->get('long')));

        if (!is_numeric($lat) || !is_numeric($lng)) {
            return [false, null, null];
        }

        return [true, (float) $lat, (float) $lng];
    }

    /**
     * Customer price range per product = approved, available offers × (1 + margin).
     * $supplierIds = null → any supplier; array → only suppliers covering the site.
     *
     * @return array<int, array{min: float, max: float}>
     */
    private function customerPrices(array $productIds, ?array $supplierIds): array
    {
        if (empty($productIds)) {
            return [];
        }

        $query = SupplierOffers::query()
            ->selectRaw('master_product_id, MIN(price) AS price_min, MAX(price) AS price_max')
            ->whereIn('master_product_id', $productIds)
            ->where('status', 'Approved')
            ->whereIn('availability_status', self::AVAILABLE_STATUSES);

        if ($supplierIds !== null) {
            $query->whereIn('supplier_id', $supplierIds ?: [0]);
        }

        $factor = 1 + PricingService::ITEM_MARGIN;

        return $query->groupBy('master_product_id')->get()
            ->mapWithKeys(fn ($row) => [(int) $row->master_product_id => [
                'min' => round((float) $row->price_min * $factor, 2),
                'max' => round((float) $row->price_max * $factor, 2),
            ]])
            ->all();
    }

    /**
     * Products with at least one approved, available offer (optionally from given suppliers).
     * Used by productTypes so the type list matches the product list.
     */
    private function listableProducts($query, ?array $supplierIds = null)
    {
        return $query
            ->whereHas('supplierOffers', function ($q) use ($supplierIds) {
                $q->where('status', 'Approved')
                  ->whereIn('availability_status', self::AVAILABLE_STATUSES);
                if ($supplierIds !== null) {
                    $q->whereIn('supplier_id', $supplierIds ?: [0]);
                }
            })
            ->whereRaw(
                'LOWER(product_type) NOT IN (' . implode(',', array_fill(0, count(self::EXCLUDED_TYPES), '?')) . ')',
                self::EXCLUDED_TYPES
            );
    }

    /** Supplier ids whose delivery_zones cover the point (same rule as OrderController). */
    private function supplierIdsInZone(float $lat, float $lng): array
    {
        return User::query()
            ->where('role', 'supplier')
            ->where('status', 'active')
            ->whereNotNull('delivery_zones')
            ->get(['id', 'delivery_zones'])
            ->filter(function ($s) use ($lat, $lng) {
                foreach ($this->decodeZones($s->delivery_zones) as $z) {
                    if (!isset($z['lat'], $z['long'], $z['radius'])) continue;
                    if ($this->haversineKm($lat, $lng, (float) $z['lat'], (float) $z['long']) <= (float) $z['radius']) {
                        return true;
                    }
                }
                return false;
            })
            ->pluck('id')
            ->values()
            ->all();
    }

    /** delivery_zones JSON/array → array of zones. */
    private function decodeZones($raw): array
    {
        $zones = is_string($raw) ? json_decode($raw, true) : $raw;
        return is_array($zones) ? array_filter($zones, 'is_array') : [];
    }

    /**
     * Public label for a zone. Zones are {lat, long, radius, address}.
     * The street part (anything with a number, e.g. "12 Smith St") is dropped
     * so a supplier's yard address is never exposed — only suburb/state remains.
     */
    private function zoneLabel(array $z): ?string
    {
        $address = trim((string) ($z['address'] ?? ''));
        if ($address === '') return null;

        $parts = array_values(array_filter(array_map('trim', explode(',', $address))));

        // Drop leading street segments ("12 Smith St", "Unit 4/20 King Rd")
        while (count($parts) > 1 && preg_match('/\d/', $parts[0]) && !preg_match('/^[A-Za-z\s]+\s+(NSW|VIC|QLD|WA|SA|TAS|ACT|NT)\b/i', $parts[0])) {
            array_shift($parts);
        }

        // Drop trailing country
        if (count($parts) > 1 && strcasecmp(end($parts), 'Australia') === 0) {
            array_pop($parts);
        }

        $label = implode(', ', $parts);
        // "Parramatta NSW 2150" → "Parramatta NSW"
        $label = trim(preg_replace('/\s+\d{4}$/', '', $label));

        return $label !== '' ? $label : null;
    }

    /** Haversine distance in km (same as OrderController). */
    private function haversineKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $R = 6371.0088;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2)
           + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
        return $R * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    private function emptyPage(int $page, int $perPage)
    {
        return response()->json([
            'data' => [],
            'meta' => ['current_page' => $page, 'per_page' => $perPage, 'total' => 0, 'last_page' => 1],
        ], 200);
    }
}
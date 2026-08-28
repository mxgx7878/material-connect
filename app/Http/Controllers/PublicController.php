<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MasterProducts;
use App\Models\User;
use App\Models\Inquiry;
use App\Models\SupplierOffers;
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
    
        // ---- Location-based availability (same concept as OrderController@getClientProducts) ----
        $hasLocation = $request->filled('lat') && $request->filled('lng');
    
        if ($hasLocation) {
            $supplierIds = $this->supplierIdsInZone((float) $request->get('lat'), (float) $request->get('lng'));
    
            $availableProductIds = empty($supplierIds) ? [] : SupplierOffers::query()
                ->where('status', 'Approved')
                ->whereIn('availability_status', ['In Stock', 'Limited'])
                ->whereIn('supplier_id', $supplierIds)
                ->distinct()
                ->pluck('master_product_id')
                ->all();
    
            // No supplier / offer covers this location → empty (frontend shows "Let us source it")
            if (empty($availableProductIds)) {
                return response()->json([
                    'data' => [],
                    'meta' => ['current_page' => 1, 'per_page' => $perPage, 'total' => 0, 'last_page' => 1],
                ], 200);
            }
    
            $query->whereIn('id', $availableProductIds);
        }
        // ---------------------------------------------------------------------------------------
    
        $query->with('category')->orderBy('product_name', 'asc');
    
        // Select ONLY public-safe columns. `category` (FK) is needed for the relation.
        $products = $query->paginate(
            $perPage,
            ['id', 'product_name', 'product_type', 'slug', 'photo', 'specifications', 'unit_of_measure', 'category'],
            'page',
            $page
        );
    
        // Flag availability — NO price/supplier data exposed. (Same as portal: dynamic attribute.)
        $products->getCollection()->transform(function ($p) use ($hasLocation) {
            $p->is_available = $hasLocation ? true : null; // location set = deliverable; else unknown
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
        $allowed = ['rfq','sourcing','supplier','software','contact','product_inquiry'];
        if (!in_array($type, $allowed, true)) {
            return response()->json(['success' => false, 'message' => 'Invalid inquiry type.'], 422);
        }
    
        $base = [
            'email'      => ['required','email','max:190'],
            'name'       => ['nullable','string','max:150'],
            'phone'      => ['nullable','string','max:50'],
            'company'    => ['nullable','string','max:200'],
            'product_id' => ['nullable','integer'],
            'suburb'     => ['nullable','string','max:120'],
            'postcode'   => ['nullable','string','max:12'],
            'lat'        => ['nullable','numeric'],
            'lng'        => ['nullable','numeric'],
            'files'      => ['nullable','array'],
            'files.*'    => ['string','max:2048'],
        ];
    
        $perType = match ($type) {
            'rfq'             => ['name' => ['required','string','max:150'], 'materials' => ['required','string']],
            'sourcing'        => ['name' => ['required','string','max:150'], 'outcome'   => ['required','string']],
            'supplier'        => ['legal_name' => ['required','string','max:200']],
            'software'        => ['company' => ['required','string','max:200']],
            'contact'         => ['name' => ['required','string','max:150'], 'message' => ['required','string']],
            'product_inquiry' => ['product_id' => ['required','integer']],
            default           => [],
        };
    
        $request->validate(array_merge($base, $perType));
    
        // First-class columns; everything else goes into payload JSON.
        $cols = ['type','name','company','email','phone','contact_method','subject','message',
                 'product_id','suburb','postcode','lat','lng','files','source','company_url'];
        $payload = $request->except($cols);
    
        $inquiry = Inquiry::create([
            'type'           => $type,
            'name'           => $request->input('name'),
            'company'        => $request->input('company') ?? $request->input('legal_name'),
            'email'          => $request->input('email'),
            'phone'          => $request->input('phone'),
            'contact_method' => $request->input('contact_method'),
            'subject'        => $request->input('subject'),
            // pull the "main text" from whichever field the form used
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
    
    
    
    
    /** Haversine distance in km (same as OrderController). */
    private function haversineKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $R = 6371.0088;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat/2) * sin($dLat/2)
           + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
        return $R * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
    
    /** Supplier ids whose delivery_zones cover the point (same rule as the portal). */
    private function supplierIdsInZone(float $lat, float $lng): array
    {
        return User::query()
            ->where('role', 'supplier')
            ->where('status', 'active')
            ->whereNotNull('delivery_zones')
            ->get(['id', 'delivery_zones'])
            ->filter(function ($s) use ($lat, $lng) {
                $zones = is_string($s->delivery_zones)
                    ? json_decode($s->delivery_zones, true)
                    : $s->delivery_zones;
                if (!is_array($zones)) return false;
                foreach ($zones as $z) {
                    if (!isset($z['lat'], $z['long'], $z['radius'])) continue;
                    if ($this->haversineKm($lat, $lng, (float)$z['lat'], (float)$z['long']) <= (float)$z['radius']) {
                        return true;
                    }
                }
                return false;
            })
            ->pluck('id')
            ->all();
        }
}
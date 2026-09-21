<?php

namespace App\Http\Controllers;

use App\Models\ActionLog;
use App\Models\Company;
use App\Models\MasterProducts;
use App\Models\OrderItemDelivery;
use App\Models\Orders;
use App\Models\Projects;
use App\Models\SupplierOffers;
use App\Models\Surcharge;
use App\Models\User;
use App\Notifications\OrderCreatedNotification;
use App\Notifications\RegistrationSuccessfulNotification;
use App\Services\OrderStatusService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * PublicOrderController
 * ---------------------
 * Order flow for the PUBLIC website (materialconnect.com.au/order).
 *
 * Deliberately separate from the portal: OrderController@createOrder is not
 * touched or called. This controller writes the same order / order_items /
 * order_item_deliveries rows, so admins and suppliers see a public-site order
 * exactly like a portal order.
 *
 * The one addition: the customer does not need an account first. placeOrder()
 * receives the account details together with the order and, in ONE transaction,
 *   - existing client  → checks the password
 *   - new email        → creates the client account
 * and marks the account verified (setting / entering the password IS the
 * verification on the public site). If anything fails, nothing is saved —
 * no half-created accounts.
 *
 * Endpoints (routes/api.php → public/shop/*, all throttled):
 *   GET  surcharges     active surcharge rates for the review estimate
 *   POST check-email    new | existing | not_client | closed
 *   POST verify-login   checks an existing client's password early (no token)
 *   POST check-po       is this PO number free?
 *   POST order          account + order in one call
 */
class PublicOrderController extends Controller
{
    /* =====================================================================
     |  Small public lookups
     * ===================================================================== */

    /** GET /api/public/shop/surcharges */
    public function surcharges()
    {
        $rows = Surcharge::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['id', 'name', 'billing_code', 'amount', 'amount_type', 'description']);

        return response()->json(['data' => $rows], 200);
    }

    /** POST /api/public/shop/check-email  { email } */
    public function checkEmail(Request $request)
    {
        $v = Validator::make($request->all(), ['email' => 'required|email|max:190']);
        if ($v->fails()) {
            return response()->json(['error' => $v->errors()], 422);
        }

        $user = $this->findUser($request->email);

        return response()->json($this->accountStatus($user), 200);
    }

    /**
     * POST /api/public/shop/verify-login  { email, password }
     * Lets the site confirm an existing customer's password on the "Your details"
     * step instead of at the very end. Returns their projects for the review step.
     */
    public function verifyLogin(Request $request)
    {
        $v = Validator::make($request->all(), [
            'email'    => 'required|email|max:190',
            'password' => 'required|string',
        ]);
        if ($v->fails()) {
            return response()->json(['error' => $v->errors()], 422);
        }

        $user = $this->findUser($request->email);
        $error = $this->loginError($user, (string) $request->password);
        if ($error) {
            return response()->json(['error' => $error['fields']], $error['status']);
        }

        return response()->json([
            'user'     => $this->publicUser($user),
            'projects' => $this->projectsFor($user),
        ], 200);
    }

    /** POST /api/public/shop/check-po  { po_number } */
    public function checkPo(Request $request)
    {
        $po = trim((string) $request->input('po_number', ''));

        return response()->json([
            'po_number' => $po,
            'available' => $po === '' || ! Orders::where('po_number', $po)->exists(),
        ], 200);
    }

    /* =====================================================================
     |  Place order (account + order in one transaction)
     * ===================================================================== */

    /** POST /api/public/shop/order */
    public function placeOrder(Request $request)
    {
        $v = Validator::make($request->all(), $this->orderRules(), [
            'account.password.min' => 'Your password needs at least 8 characters.',
        ]);
        if ($v->fails()) {
            return response()->json(['error' => $v->errors()], 422);
        }

        if ($qtyError = $this->slotTotalsError($request->items)) {
            return response()->json(['error' => $qtyError], 422);
        }

        $account = $request->input('account');
        $existing = $this->findUser($account['email']);

        // Existing account → the password must match before anything is written.
        if ($existing) {
            $error = $this->loginError($existing, (string) $account['password']);
            if ($error) {
                return response()->json(['error' => $error['fields']], $error['status']);
            }
        } elseif (! $this->hasNewAccountFields($account)) {
            return response()->json(['error' => [
                'account.name' => ['Please enter your name and phone number to create your account.'],
            ]], 422);
        }

        // A project can only be one of the customer's own.
        if ($request->filled('project_id')
            && (! $existing || ! Projects::where('id', $request->project_id)->where('added_by', $existing->id)->exists())) {
            return response()->json(['error' => ['project_id' => ['Please choose one of your own projects.']]], 422);
        }

        [$user, $order, $isNewAccount] = DB::transaction(function () use ($request, $account, $existing) {
            $isNew = ! $existing;
            $user  = $existing ?: $this->createCustomer($account);

            if (! $user->hasVerifiedEmail()) {
                $user->markEmailAsVerified();
            }

            $order = $this->createOrderRecords($user, $request);

            return [$user, $order, $isNew];
        });

        $this->sendEmails($user, $order, $isNewAccount);

        return response()->json([
            'message'         => 'Order placed.',
            'account_created' => $isNewAccount,
            'order'           => [
                'id'        => $order->id,
                'po_number' => $order->po_number,
            ],
            'user'            => $this->publicUser($user),
        ], 201);
    }

    /* =====================================================================
     |  Helpers — account
     * ===================================================================== */

    private function findUser(string $email): ?User
    {
        return User::whereRaw('LOWER(email) = ?', [Str::lower(trim($email))])->first();
    }

    /** status for check-email: new | existing | not_client | closed */
    private function accountStatus(?User $user): array
    {
        if (! $user) {
            return ['status' => 'new'];
        }
        if ($user->role !== 'client') {
            return ['status' => 'not_client', 'message' => 'This email is linked to a business account that cannot place orders. Please use a different email address.'];
        }
        if ($user->isDeleted) {
            return ['status' => 'closed', 'message' => 'This account has been closed. Please contact our support team.'];
        }
        return ['status' => 'existing'];
    }

    /** null when the user may order with this password, otherwise the error to return. */
    private function loginError(?User $user, string $password): ?array
    {
        if (! $user || $user->role !== 'client' || $user->isDeleted) {
            return ['status' => 404, 'fields' => ['account.email' => ['We could not find an active customer account for this email.']]];
        }
        if (! Hash::check($password, $user->password)) {
            return ['status' => 401, 'fields' => ['account.password' => ['That password is not correct. Please try again.']]];
        }
        return null;
    }

    private function hasNewAccountFields(array $account): bool
    {
        return trim((string) ($account['name'] ?? '')) !== ''
            && trim((string) ($account['contact_number'] ?? '')) !== '';
    }

    /** Same account the portal's registerClient creates (minus the profile photo). */
    private function createCustomer(array $account): User
    {
        $companyName = trim((string) ($account['company_name'] ?? '')) ?: $account['name'];
        $company = Company::firstOrCreate(['name' => Str::lower($companyName)]);

        do {
            $clientPublicId = 'MC-' . rand(100, 999);
        } while (User::where('client_public_id', $clientPublicId)->exists());

        return User::create([
            'name'             => $account['name'],
            'email'            => Str::lower(trim($account['email'])),
            'password'         => Hash::make($account['password']),
            'role'             => 'client',
            'company_id'       => $company->id,
            'contact_name'     => $account['name'],
            'contact_number'   => $account['contact_number'],
            'client_public_id' => $clientPublicId,
            'isDeleted'        => false,
        ]);
    }

    private function publicUser(User $user): array
    {
        return [
            'id'             => $user->id,
            'name'           => $user->name,
            'email'          => $user->email,
            'contact_number' => $user->contact_number,
        ];
    }

    private function projectsFor(User $user): array
    {
        return Projects::where('added_by', $user->id)
            ->where('is_archived', 0)
            ->orderBy('name')
            ->get(['id', 'name', 'delivery_address'])
            ->toArray();
    }

    /* =====================================================================
     |  Helpers — order
     * ===================================================================== */

    /** Same fields and limits as the portal order, plus the account block. */
    private function orderRules(): array
    {
        return [
            'account'                => 'required|array',
            'account.email'          => 'required|email|max:190',
            'account.password'       => 'required|string|min:8|max:100',
            'account.name'           => 'nullable|string|max:255',
            'account.contact_number' => 'nullable|string|max:30',
            'account.company_name'   => 'nullable|string|max:255',

            'po_number'             => 'nullable|string|max:50|unique:orders,po_number',
            'project_id'            => 'nullable|integer',
            'delivery_address'      => 'required|string',
            'delivery_lat'          => 'required|numeric',
            'delivery_long'         => 'required|numeric',
            'contact_person_name'   => 'required|string|max:100',
            'contact_person_number' => 'required|string|max:30',
            'testing_fee'           => 'nullable|boolean',

            'items'                    => 'required|array|min:1',
            'items.*.product_id'       => 'required|exists:master_products,id',
            'items.*.quantity'         => 'required|numeric|min:0.01',
            'items.*.custom_blend_mix' => 'nullable|string',

            'items.*.delivery_slots'                         => 'required|array|min:1',
            'items.*.delivery_slots.*.quantity'              => 'required|numeric|min:0.01',
            'items.*.delivery_slots.*.delivery_date'         => 'required|date',
            'items.*.delivery_slots.*.delivery_time'         => 'required|date_format:H:i',
            'items.*.delivery_slots.*.truck_type'            => 'nullable|string|max:50',
            'items.*.delivery_slots.*.load_size'             => 'nullable|string|max:50',
            'items.*.delivery_slots.*.time_interval'         => 'nullable|string|max:50',
            'items.*.delivery_slots.*.accelerator_type'      => 'nullable|in:low,medium,high',
            'items.*.delivery_slots.*.retarder_type'         => 'nullable|in:low,medium,high',
            'items.*.delivery_slots.*.aggregate_size'        => 'nullable|in:10mm,7mm',
            'items.*.delivery_slots.*.slump_value'           => 'nullable|numeric|min:60|max:200',
            'items.*.delivery_slots.*.oxide_fibre'           => 'nullable|boolean',
            'items.*.delivery_slots.*.paver_delivery'        => 'nullable|boolean',
            'items.*.delivery_slots.*.omc_conditioning'      => 'nullable|boolean',
            'items.*.delivery_slots.*.additional_stabiliser' => 'nullable|boolean',
        ];
    }

    /** Each item's quantity must equal the sum of its delivery slots. */
    private function slotTotalsError(array $items): ?array
    {
        foreach ($items as $idx => $item) {
            $slotSum = collect($item['delivery_slots'])->sum(fn ($s) => (float) ($s['quantity'] ?? 0));
            if (abs($slotSum - (float) $item['quantity']) > 0.01) {
                return ["items.$idx.quantity" => ["Item quantity must equal the total of its deliveries (expected {$slotSum})."]];
            }
        }
        return null;
    }

    /** Writes order + items + deliveries. Runs inside placeOrder's transaction. */
    private function createOrderRecords(User $user, Request $request): Orders
    {
        $order = Orders::create([
            'po_number'              => $request->po_number ?: null,
            'client_id'              => $user->id,
            'project_id'             => $request->project_id ?: null,
            'delivery_address'       => $request->delivery_address,
            'delivery_lat'           => $request->delivery_lat,
            'delivery_long'          => $request->delivery_long,
            'delivery_date'          => null,
            'delivery_time'          => null,
            'contact_person_name'    => $request->contact_person_name,
            'contact_person_number'  => $request->contact_person_number,
            'other_charges'          => 0,
            'gst_tax'                => 0,
            'discount'               => 0,
            'total_price'            => 0,
            'customer_item_cost'     => 0,
            'customer_delivery_cost' => 0,
            'supplier_item_cost'     => 0,
            'supplier_delivery_cost' => 0,
            'payment_status'         => 'Pending',
            'order_status'           => OrderStatusService::INITIAL,
            'generate_invoice'       => 0,
            'repeat_order'           => 1,
            'requires_testing'       => (bool) $request->testing_fee,
        ]);

        $offers = $this->offersFor(collect($request->items)->pluck('product_id')->unique()->values()->all());
        $earliest = null;

        foreach ($request->items as $row) {
            $offer = $this->nearestOfferInZone(
                $offers->get((int) $row['product_id']) ?? collect(),
                (float) $order->delivery_lat,
                (float) $order->delivery_long
            );

            $item = $order->items()->create([
                'product_id'             => (int) $row['product_id'],
                'quantity'               => (float) $row['quantity'],
                'supplier_id'            => $offer ? (int) $offer->supplier_id : null,
                'custom_blend_mix'       => $row['custom_blend_mix'] ?? null,
                'supplier_unit_cost'     => (float) ($offer->unit_cost ?? $offer->price ?? 0),
                'supplier_delivery_cost' => (float) ($offer->delivery_cost ?? 0),
                'supplier_delivery_date' => null,
                'choosen_offer_id'       => $offer ? $offer->id : null,
                'supplier_confirms'      => 0,
            ]);

            foreach ($row['delivery_slots'] as $slot) {
                $this->createDelivery($order, $item, $slot);

                $key = $slot['delivery_date'] . ' ' . $slot['delivery_time'];
                if ($earliest === null || $key < $earliest) {
                    $earliest = $key;
                }
            }
        }

        if ($earliest !== null) {
            [$order->delivery_date, $order->delivery_time] = explode(' ', $earliest);
        }
        $order->save();

        ActionLog::create([
            'action'   => 'Order Created',
            'details'  => "Order ID {$order->id} created from the public website by Client " . ($user->contact_name ?: $user->name),
            'order_id' => $order->id,
            'user_id'  => $user->id,
        ]);

        return $order;
    }

    private function createDelivery(Orders $order, $item, array $slot): void
    {
        OrderItemDelivery::create([
            'order_id'              => $order->id,
            'order_item_id'         => $item->id,
            'supplier_id'           => $item->supplier_id,
            'quantity'              => (float) $slot['quantity'],
            'delivery_date'         => $slot['delivery_date'],
            'delivery_time'         => $slot['delivery_time'],
            'truck_type'            => $slot['truck_type'] ?? null,
            'load_size'             => $slot['load_size'] ?? null,
            'time_interval'         => $slot['time_interval'] ?? null,
            'accelerator_type'      => $slot['accelerator_type'] ?? null,
            'retarder_type'         => $slot['retarder_type'] ?? null,
            'aggregate_size'        => $slot['aggregate_size'] ?? null,
            'slump_value'           => $slot['slump_value'] ?? null,
            'oxide_fibre'           => $slot['oxide_fibre'] ?? null,
            'paver_delivery'        => $slot['paver_delivery'] ?? null,
            'omc_conditioning'      => $slot['omc_conditioning'] ?? null,
            'additional_stabiliser' => $slot['additional_stabiliser'] ?? null,
            'supplier_confirms'     => 0,
        ]);
    }

    /** Approved, available offers for the products, grouped by product id. */
    private function offersFor(array $productIds)
    {
        return SupplierOffers::with(['supplier:id,delivery_zones'])
            ->whereIn('master_product_id', $productIds)
            ->where('status', 'Approved')
            ->whereIn('availability_status', ['In Stock', 'Limited'])
            ->get()
            ->groupBy('master_product_id');
    }

    /** Nearest offer whose supplier has a delivery zone covering the site (same rule as the portal). */
    private function nearestOfferInZone($offers, float $lat, float $lng): ?SupplierOffers
    {
        $best = null;
        $bestDist = PHP_FLOAT_MAX;

        foreach ($offers as $offer) {
            $zones = $offer->supplier?->delivery_zones;
            $zones = is_string($zones) ? json_decode($zones, true) : $zones;
            if (! is_array($zones)) {
                continue;
            }

            foreach ($zones as $z) {
                if (! isset($z['lat'], $z['long'], $z['radius'])) {
                    continue;
                }
                $dist = $this->haversineKm($lat, $lng, (float) $z['lat'], (float) $z['long']);
                if ($dist <= (float) $z['radius'] && $dist < $bestDist) {
                    $bestDist = $dist;
                    $best = $offer;
                }
            }
        }

        return $best;
    }

    private function haversineKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $r = 6371.0088;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;

        return $r * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    /* =====================================================================
     |  Helpers — emails (after commit; a mail failure never loses an order)
     * ===================================================================== */

    private function sendEmails(User $user, Orders $order, bool $isNewAccount): void
    {
        if ($isNewAccount) {
            try {
                $user->notify(new RegistrationSuccessfulNotification('client'));
            } catch (\Throwable $e) {
                Log::warning('Public order welcome email failed', ['user_id' => $user->id, 'error' => $e->getMessage()]);
            }
        }

        try {
            $order->load(['items.product', 'items.supplier', 'items.deliveries']);

            $suppliers = User::whereIn('id', $order->items->pluck('supplier_id')->filter()->unique())
                ->where('role', 'supplier')
                ->where('isDeleted', 0)
                ->get();

            $recipients = User::where('role', 'admin')->where('isDeleted', 0)->get()
                ->merge($suppliers)
                ->push($user)
                ->unique('id')
                ->values();

            Notification::send($recipients, new OrderCreatedNotification($order, $user->contact_name ?: $user->name));
        } catch (\Throwable $e) {
            Log::warning('Public order-created email failed', ['order_id' => $order->id, 'error' => $e->getMessage()]);
        }
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SupplierOffers;
use App\Models\MasterProducts;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SupplierController extends Controller
{   

    /**
     * Add a product to the supplier's inventory.
     */

    public function deliveryZonesManagement(Request $request)
    {
        // Logic for managing delivery zones
        $validator = Validator::make($request->all(), [
            'zones' => 'sometimes|array',
            'zones.*.lat' => 'sometimes|numeric',
            'zones.*.long' => 'sometimes|numeric',
            'zones.*.radius' => 'sometimes|numeric',
            'zones.*.address' => 'sometimes|string|max:255',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        /** @var User $user */
        $user = Auth::user();
        abort_unless($user && $user->role === 'supplier', 403, 'Forbidden');
        if( !$request->zones || $request->zones === null || $request->zones === '' || count($request->zones) === 0){
            $user->delivery_zones = null;
            $user->save();
            return response()->json(['message' => 'Delivery zones cleared', 'delivery_zones' => []], 200);
        }
        $user->delivery_zones = json_encode($request->zones);
        $user->save();
        return response()->json(['message' => 'Delivery zones updated successfully', 'delivery_zones' => $user->delivery_zones], 200);
    }

    public function getDeliveryZones()
    {
        // Get the authenticated user
        $user = Auth::user();

        // Ensure the user exists and is a supplier
        abort_unless($user && $user->role === 'supplier', 403, 'Forbidden');

        // Check if delivery_zones is not set, null, or empty
        if (!$user->delivery_zones || $user->delivery_zones === null || $user->delivery_zones === '') {
            return response()->json(['delivery_zones' => []], 200);
        }

        // Decode the delivery_zones if it is a JSON string
        $decodedZones = json_decode($user->delivery_zones, true);

        // If json_decode fails and the result is null, handle that case
        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json(['error' => 'Invalid JSON format in delivery_zones'], 400);
        }

        // Return the decoded delivery_zones
        return response()->json(['delivery_zones' => $decodedZones], 200);
    }


    /**
     * Get Master Product Inventory.
     */
    public function getMasterProductInventory(Request $request)
    {
        $perPage = (int) $request->get('per_page', 10);
        $page = (int) $request->get('page', 1); 
        $query = MasterProducts::with(['added_by', 'approved_by', 'category'])
            ->where('is_approved', true); // Only approved products

        // Get all master_product_ids for the authenticated supplier
        $excludedProductIds = SupplierOffers::where('supplier_id', Auth::id())
            ->pluck('master_product_id')
            ->toArray();

        // Exclude products where master_product_id matches the excluded products
        $query->whereNotIn('id', $excludedProductIds);

        // Apply Category filter
        if ($request->filled('category_id')) {
            $query->where('category', $request->get('category_id'));
        }

        // Product Type filter
        if ($request->filled('product_type')) {
            $query->where('product_type', $request->get('product_type'));
        }

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where('product_name', 'like', "%{$search}%");
        }

        // Paginate after filters
        $products = $query->paginate($perPage, ['*'], 'page', $page);

        // Append supplier offers count
        $products->getCollection()->transform(function ($product) {
            $product->supplierOffersCount = SupplierOffers::where('master_product_id', $product->id)->count();
            $product->suppliers = SupplierOffers::with('supplier')->where('master_product_id', $product->id)->get();
            $product->suppliers->transform(function ($supplier) {
                $supplier->isMe = Auth::id() == $supplier->supplier_id;
                return $supplier;
            });
            return $product;
        });

        return response()->json($products, 200);
    }

    /**
     * Get Supplier Products.
     */
    public function getSupplierProducts2(Request $request)
    {
        $perPage = (int) $request->get('per_page', 10);
        $page = (int) $request->get('page', 1);
        $supplierId = Auth::id();

        // Get supplier's offers with master product details in one query
        $query = SupplierOffers::with([
            'masterProduct.category'
        ])
        ->where('supplier_id', $supplierId)
        ->whereHas('masterProduct', function ($q) {
            $q->where('is_approved', true);
        });

        // Product Type filter (on master product)
        if ($request->filled('product_type')) {
            $query->whereHas('masterProduct', function ($q) use ($request) {
                $q->where('product_type', $request->get('product_type'));
            });
        }

        // Search filter (on master product name)
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->whereHas('masterProduct', function ($q) use ($search) {
                $q->where('product_name', 'like', "%{$search}%");
            });
        }

        // Status filter (active/inactive offers)
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        if (in_array($sortBy, ['price', 'created_at', 'updated_at'])) {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Paginate
        $offers = $query->paginate($perPage, ['*'], 'page', $page);

        // Transform response
        $offers->getCollection()->transform(function ($offer) {
            return [
                'offer_id' => $offer->id,
                'master_product_id' => $offer->master_product_id,
                'offer_status' => $offer->status,
                'availability_status' => $offer->availability_status,
                
                // Product details
                'product_name' => $offer->masterProduct->product_name,
                'product_type' => $offer->masterProduct->product_type,
                'unit' => $offer->masterProduct->unit,
                'specifications' => $offer->masterProduct->specifications,
                'image_url' => $offer->masterProduct->image_url,
                'category' => is_object($offer->masterProduct->category) ? [
                    'id' => $offer->masterProduct->category->id,
                    'name' => $offer->masterProduct->category->name,
                ] : null,
                
                // Offer details (supplier's own data)
                'price' => (float) $offer->price,
                
                
                // Timestamps
                'created_at' => $offer->created_at,
                'updated_at' => $offer->updated_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $offers->items(),
            'current_page' => $offers->currentPage(),
            'last_page' => $offers->lastPage(),
            'per_page' => $offers->perPage(),
            'total' => $offers->total(),
            'from' => $offers->firstItem(),
            'to' => $offers->lastItem(),
        ], 200);
    }


    /**
     * Add a product to the supplier's inventory.
     */
    public function addProductToInventory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'master_product_id' => 'required|exists:master_products,id',
            'price' => 'required|numeric',
            'availability_status' => 'required|in:In Stock,Out of Stock,Limited',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Create supplier offer for the product
        $supplierOffer = SupplierOffers::updateOrCreate([
            'supplier_id' => Auth::id(), // Authenticated supplier
            'master_product_id' => $request->master_product_id,
            'price' => $request->price,
            'availability_status' => $request->availability_status,
            'status' => 'Pending', // Default status, awaiting approval if needed
        ]);

        return response()->json(['message' => 'Product added to inventory successfully', 'supplier_offer' => $supplierOffer], 201);
    }

    /**
     * Request a new product to be added to the master catalog.
     */
    public function requestNewProduct(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_name' => 'required|string|max:255',
            'product_type' => 'required|string|max:255',
            'category_id' => 'nullable|exists:category,id', // Optional category
            'specifications' => 'nullable|string',
            'unit_of_measure' => 'nullable|string|max:100',
            'price' => 'required|numeric',
            'availability_status' => 'required|in:In Stock,Out of Stock,Limited',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
      

        // Create a new product request (admin will approve or reject)
        $newProduct = MasterProducts::create([
            'product_name' => $request->product_name,
            'product_type' => $request->product_type,
            'category' => $request->category_id,
            'specifications' => $request->specifications,
            'unit_of_measure' => $request->unit_of_measure,
            'slug' => Str::slug($request->product_name) . '-' . Str::random(6),
            'is_approved' => false, // Pending approval by admin
            'added_by' => Auth::id(), // Supplier is adding the product
        ]);
        $supplierOffer = SupplierOffers::updateOrCreate([
            'supplier_id' => Auth::id(), // Authenticated supplier
            'master_product_id' => $newProduct->id,
            'price' => $request->price,
            'availability_status' => $request->availability_status,
            'status' => 'Pending', // Default status, awaiting approval if needed
        ]);

        return response()->json(['message' => 'Product request submitted successfully', 'new_product' => $newProduct], 201);
    }

    /**
     * View the supplier's products list.
     */
    public function getSupplierProducts(Request $request)
    {
        // Fetch supplier's products from SupplierOffers
        $supplierOffers = SupplierOffers::with(['masterProduct', 'masterProduct.category','supplier'])
            ->where('supplier_id', Auth::id())
            ->paginate(10); // Pagination if needed

        return response()->json($supplierOffers);
    }

    /**
     * Edit product pricing in the supplier's inventory.
     */
    public function updateProductPricing(Request $request, $offerId)
    {
        $validator = Validator::make($request->all(), [
            'price' => 'required|numeric',
            'availability_status' => 'required|in:In Stock,Out of Stock,Limited',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Find the existing supplier offer
        $supplierOffer = SupplierOffers::find($offerId);

        if (!$supplierOffer || $supplierOffer->supplier_id != Auth::id()) {
            return response()->json(['error' => 'Offer not found or you are not authorized to edit this offer'], 404);
        }

        // Update the product pricing and availability
        $supplierOffer->update([
            'price' => $request->price,
            'availability_status' => $request->availability_status,
        ]);

        return response()->json(['message' => 'Product pricing updated successfully', 'supplier_offer' => $supplierOffer], 200);
    }

    /**
     * Delete product from the supplier's inventory.
     */
    public function deleteProductFromInventory($offerId)
    {
        // Find the existing supplier offer
        $supplierOffer = SupplierOffers::find($offerId);
        if (!$supplierOffer || $supplierOffer->supplier_id != Auth::id()) {
            return response()->json(['error' => 'Offer not found or you are not authorized to delete this offer'], 404);
        }
        $supplierOffer->delete();
        return response()->json(['message' => 'Product removed from inventory successfully'], 200);
    }

    /**
     * Get Status of Supplier Offers.
     */
    public function getSupplierOfferStatus()
    {
        $offers = SupplierOffers::where('supplier_id', Auth::id())->get();
        return response()->json($offers, 200);
    }

}

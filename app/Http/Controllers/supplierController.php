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
        $user = Auth::user();
        abort_unless($user && $user->role === 'supplier', 403, 'Forbidden');
        if( !$request->zones || $request->zones === null || $request->zones === '' || count($request->zones) === 0){
            $user->delivery_zones = null;
            $user->save();
            return response()->json(['message' => 'Delivery zones cleared', 'delivery_zones' => []], 200);
        }
        $user->delivery_zones = json_encode($request->zones);
        $user->save();
        return response()->json(['message' => 'Delivery zones updated successfully', 'delivery_zones' => json_decode($user->delivery_zones,true)], 200);
    }

    public function getDeliveryZones()
    {
        $user = Auth::user();
        abort_unless($user && $user->role === 'supplier', 403, 'Forbidden');
        if( !$user->delivery_zones || $user->delivery_zones === null || $user->delivery_zones === ''){
            return response()->json(['delivery_zones' => []], 200);
        }
        return response()->json(['delivery_zones' => json_decode($user->delivery_zones,true)], 200);
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
            'delivery_zones' => 'required|array', // This should be an array of delivery zones
            'delivery_zones.*.lat' => 'required|numeric', // Latitude of the delivery zone
            'delivery_zones.*.long' => 'required|numeric', // Longitude of the delivery zone
            'delivery_zones.*.radius' => 'required|numeric', // Radius of the delivery zone
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
            'delivery_zones' => json_encode($request->delivery_zones), // Save delivery zones as JSON
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

        return response()->json(['message' => 'Product request submitted successfully', 'new_product' => $newProduct], 201);
    }

    /**
     * View the supplier's products list.
     */
    public function getSupplierProducts(Request $request)
    {
        // Fetch supplier's products from SupplierOffers
        $supplierOffers = SupplierOffers::with(['masterProduct', 'masterProduct.category'])
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
}

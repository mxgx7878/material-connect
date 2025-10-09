<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\MasterProducts;
use App\Models\SupplierOffers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class MasterProductsController extends Controller
{
    /**
     * List all master products.
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);  // Default 10 items per page
        $page = $request->get('page', 1);  // Default page number is 1

        $products = MasterProducts::with(['added_by', 'approved_by', 'category'])->paginate($perPage);
        $products->each(function ($product) {
            
            $product->supplierOffersCount = SupplierOffers::where('master_product_id', $product->id)->count();

        });

        return response()->json($products, 200);
    }

    /**
     * Show a specific master product.
     */
    public function show($id)
    {
        $product = MasterProducts::with(['added_by', 'approved_by', 'category'])->find($id);
        $product->supplierOffers = SupplierOffers::with('supplier')->where('master_product_id', $product->id)->get();

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        return response()->json($product, 200);
    }

    /**
     * Create a new master product.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_name' => 'required|string|max:255|unique:master_products,product_name',
            'product_type' => 'required|string|max:255',
            'specifications' => 'nullable|string',
            'unit_of_measure' => 'nullable|string|max:100',
            'tech_doc' => 'nullable|file|mimes:pdf,docx,xlsx',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_approved' => 'boolean',
            'category' => 'nullable|string|max:255|exists:category,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $techDocPath = null;
        if ($request->hasFile('tech_doc') && $request->file('tech_doc')->isValid()) {
            $techDocPath = $request->file('tech_doc')->store('tech_docs', 'public');
        }

        $photoPath = null;
        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            $photoPath = $request->file('photo')->store('product_photos', 'public');
        }

        $approverId = null;
        if ($request->is_approved) {
            $approverId = Auth::id();
        }

        $product = MasterProducts::create([
            'product_name' => $request->product_name,
            'product_type' => $request->product_type,
            'specifications' => $request->specifications,
            'unit_of_measure' => $request->unit_of_measure,
            'tech_doc' => $techDocPath,
            'photo' => $photoPath,
            'added_by' => Auth::id(),
            'is_approved' => $request->is_approved ?? false,
            'approved_by' => $approverId,
            'slug' => Str::slug($request->product_name) . '-' . Str::random(6),
            'category' => $request->category,
        ]);

        return response()->json(['message' => 'Product created successfully', 'product' => $product], 201);
    }

    /**
     * Update a master product.
     */
    public function update(Request $request, $id)
    {
        $product = MasterProducts::find($id);

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'product_name' => 'sometimes|required|string|max:255',
            'product_type' => 'sometimes|required|string|max:255',
            'specifications' => 'nullable|string',
            'unit_of_measure' => 'nullable|string|max:100',
            'tech_doc' => 'nullable|file|mimes:pdf,docx,xlsx',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_approved' => 'boolean',
            'category' => 'nullable|string|max:255|exists:category,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        if ($request->hasFile('tech_doc') && $request->file('tech_doc')->isValid()) {
            if ($product->tech_doc) {
                Storage::delete('public/' . $product->tech_doc);
            }
            $product->tech_doc = $request->file('tech_doc')->store('tech_docs', 'public');
        }

        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            if ($product->photo) {
                Storage::delete('public/' . $product->photo);
            }
            $product->photo = $request->file('photo')->store('product_photos', 'public');
        }

        if ($request->has('is_approved')) {
            if ($request->is_approved && !$product->is_approved) {
                $product->approved_by = Auth::id();
            } elseif (!$request->is_approved) {
                $product->approved_by = null;
            }
            $product->is_approved = $request->is_approved;
        }

        $product->update([
            'product_name' => $request->product_name ?? $product->product_name,
            'product_type' => $request->product_type ?? $product->product_type,
            'specifications' => $request->specifications ?? $product->specifications,
            'unit_of_measure' => $request->unit_of_measure ?? $product->unit_of_measure,
            'slug' => $request->product_name ? Str::slug($request->product_name) . '-' . Str::random(6) : $product->slug,
            'is_approved' => $product->is_approved,
            'approved_by' => $product->approved_by,
            'category' => $request->category ?? $product->category,
            'tech_doc' => $product->tech_doc,
            'photo' => $product->photo,
        ]);

        return response()->json(['message' => 'Product updated successfully', 'product' => $product], 200);
    }

    /**
     * Delete a master product.
     */
    public function destroy($id)
    {
        $product = MasterProducts::find($id);

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        if ($product->tech_doc) {
            Storage::delete('public/' . $product->tech_doc);
        }

        if ($product->photo) {
            Storage::delete('public/' . $product->photo);
        }

        $product->delete();

        return response()->json(['message' => 'Product deleted successfully'], 200);
    }
}

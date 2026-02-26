<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Company;
use App\Models\Category;
use App\Models\MasterProducts;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class UserManagement extends Controller
{
    // List Users with Multiple Filters and Pagination
    public function index(Request $request)
    {
        $query = User::query();

        // Filters
        if ($request->has('role') && in_array($request->role, ['admin', 'client', 'supplier','accountant','support'])) {
          
            $query->where('role', $request->role);
        }

        if ($request->has('isDeleted')) {
            // Ensure the value is treated as a boolean (either true or false)
            $isDeleted = filter_var($request->isDeleted, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

            // If valid, apply the filter
            if ($isDeleted !== null) {
                $query->where('isDeleted', $isDeleted);
            }
        }

        if ($request->has('company_id') && is_numeric($request->company_id)) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->has('contact_name')) {
            $query->where(function ($q) use ($request) {
                $q->where('contact_name', 'like', '%' . $request->contact_name . '%')
                ->orWhere('name', 'like', '%' . $request->contact_name . '%')
                ->orWhere('email', 'like', '%' . $request->contact_name . '%');
            });
        }

        if ($request->has('location')) {
            $query->where('location', 'like', '%' . $request->location . '%');
        }

        if ($request->has('lat') && $request->has('long')) {
            $query->where('lat', $request->lat)->where('long', $request->long);
        }

        // Get the 'per_page' value from the request, default to 10 if not provided
        $perPage = $request->get('per_page', 10); // Default to 10 if no value is provided

        $query->with('company');

        // Pagination
        $users = $query->paginate($perPage);

        return response()->json($users);
    }

    
    // Add User
    public function store(Request $request)
    {
        // Manual validation handling (same as in registerSupplier)
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|string|in:admin,client,supplier,accountant,support',
            'company_name' => 'nullable|string|max:255', // Make sure the company name is optional
            'contact_name' => 'nullable|string|max:255',
            'contact_number' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'lat' => 'nullable|numeric',
            'long' => 'nullable|numeric',
            'delivery_radius' => 'nullable|numeric',
            'shipping_address' => 'nullable|string|max:255',
            'billing_address' => 'nullable|string|max:255',
            'client_public_id' => 'nullable|string|unique:users,client_public_id',
            'profile_image' => 'nullable|image|mimes:jpg,png,jpeg,gif',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Handle Company ID if it's for client or supplier
        $company_id = null;
        if ($request->role !== 'admin' && $request->has('company_name')) {
            // Look for the company by name, or create it if not found
            $company = Company::firstOrCreate([
                'name' => Str::lower($request->company_name),  // Company name is case-insensitive
            ]);

            // Assign the found or created company ID
            $company_id = $company->id;
        }

        // Handle Profile Image
        $imageUrl = null;
        if ($request->hasFile('profile_image') && $request->file('profile_image')->isValid()) {
            // Store the file under 'profile_images' directory in the 'public' disk
            $path = $request->file('profile_image')->store('profile_images', 'public');
            $imageUrl = 'storage/' . $path; // Relative path to the image
        }

        // Create the User
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'company_id' => $company_id,  // Assign company_id (can be null or an existing company ID)
            'contact_name' => $request->contact_name,
            'contact_number' => $request->contact_number,
            'location' => $request->location,
            'lat' => $request->lat,
            'long' => $request->long,
            'delivery_radius' => $request->delivery_radius,
            'shipping_address' => $request->shipping_address,
            'billing_address' => $request->billing_address,
            'client_public_id' => $request->client_public_id ?? 'MC-' . rand(100, 999),
            'profile_image' => $imageUrl,
            'isDeleted' => false,
            'notes' => $request->notes,
        ]);

        // Return the created user along with a success message and profile image URL
        return response()->json([
            'message' => 'User registered successfully.',
            'user' => $user,
            'role' => $user->role,
            'profile_image_url' => $imageUrl, // Return the image URL in response
        ], 201);
    }

    // Show User Details
    public function show($id)
    {
        $user = User::with('company')->find($id);
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
        return response()->json($user);
    }

    // Update User
    public function update(Request $request, $id)
    {
        // Find the user by ID
        $user = User::find($id);
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Validate the request
        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'email' => 'email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:6',
            'role' => 'string|in:admin,client,supplier,accountant,support',
            'contact_name' => 'nullable|string|max:255',
            'contact_number' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'lat' => 'nullable|numeric',
            'long' => 'nullable|numeric',
            'delivery_radius' => 'nullable|numeric',
            'shipping_address' => 'nullable|string|max:255',
            'billing_address' => 'nullable|string|max:255',
            'profile_image' => 'nullable|image|mimes:jpg,png,jpeg,gif,webp',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Handle Profile Image
        $profileImagePath = $user->profile_image; // Existing profile image path

        if ($request->hasFile('profile_image')) {
            // Check if there's an old image and delete it before uploading the new one
            if ($profileImagePath) {
                $oldImagePath = str_replace('storage/', '', $profileImagePath); // Get the relative path
                $oldImagePath = 'storage/app/public/' . $oldImagePath;
                if (Storage::exists($oldImagePath)) {
                    Storage::delete($oldImagePath); // Delete the old image
                }
            }

            // Store the new profile image
            $profileImagePath = 'storage/'.$request->file('profile_image')->store('profile_images', 'public');
        }

        // Update the User with the new data
        $user->update([
            'name' => $request->name ?? $user->name,
            'email' => $request->email ?? $user->email,
            'password' => $request->password ? Hash::make($request->password) : $user->password,
            'role' => $request->role ?? $user->role,
            'contact_name' => $request->contact_name ?? $user->contact_name,
            'contact_number' => $request->contact_number ?? $user->contact_number,
            'location' => $request->location ?? $user->location,
            'lat' => $request->lat ?? $user->lat,
            'long' => $request->long ?? $user->long,
            'delivery_radius' => $request->delivery_radius ?? $user->delivery_radius,
            'shipping_address' => $request->shipping_address ?? $user->shipping_address,
            'billing_address' => $request->billing_address ?? $user->billing_address,
            'profile_image' => $profileImagePath ? $profileImagePath : $user->profile_image, // Store the relative path
            'notes' => $request->notes ?? $user->notes,
        ]);

        // Return the updated user information as a JSON response
        return response()->json($user->load('company'));
    }

    // Soft Delete User (Mark isDeleted as true)
    public function destroy($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Mark the user as deleted
        if($user->isDeleted){
            $user->update(['isDeleted' => false]);
            return response()->json(['message' => 'User restored successfully']);
        }
        $user->update(['isDeleted' => true]);

        return response()->json(['message' => 'User marked as deleted successfully']);
    }

    // Check if Company Exists
    public function checkCompany(Request $request)
    {
        $companyName = Str::lower($request->company_name);
        $company = Company::where('name', $companyName)->first();

        if ($company) {
            return response()->json(['exists' => true, 'company_id' => $company->id]);
        } else {
            return response()->json(['exists' => false]);
        }
    }


    public function addCategory(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:120|unique:category,name',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $category = Category::create([
            'name' => $request->name,
        ]);
        return response()->json(['message' => 'Category added successfully', 'category' => $category], 201);
    }
    public function listCategories(Request $request)
    {
        // Default pagination parameters
        $perPage = $request->get('per_page', 10);  // Default 10 items per page
        $page = $request->get('page', 1);
        // Fetch categories with pagination
        $categories = Category::paginate($perPage);
        return response()->json($categories, 200);
    }

    public function listProductTypes(Request $request)
    {
        $productTypes = MasterProducts::select('product_type')->distinct()->get();
        foreach ($productTypes as $type) {
            $type->product_type = Str::title($type->product_type); // Convert to Title Case
        }
        return response()->json($productTypes, 200);
    }

    public function deleteCategory($id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json(['error' => 'Category not found'], 404);
        }
        $category->delete();
        return response()->json(['message' => 'Category deleted successfully'], 200);
    }

    public function updateCategory(Request $request, $id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json(['error' => 'Category not found'], 404);
        }

        $data = $request->validate([
            'name' => 'required|string|max:120|unique:category,name,' . $id,
        ]);

        $category->update([
            'name' => $data['name'],
        ]);

        return response()->json(['message' => 'Category updated successfully', 'category' => $category], 200);
    }

    public function getCategory($id)
    {
      
        $category = Category::find($id);
        if (!$category) {
            return response()->json(['error' => 'Category not found'], 404);
        }
        return response()->json($category, 200);
    }

    public function getCompanies()
    {
        $companies = Company::all();
        return response()->json($companies);
    }

    // public function getSuppliersWithDeliveryZones(Request $request)
    // {
    //     $perPage = (int) $request->get('per_page', 10);
    //     $search = $request->get('search');

    //     $suppliers = User::where('role', 'supplier')
    //         ->whereNotNull('delivery_zones')
    //         ->where('delivery_zones', '!=', '[]')
    //         ->when($search, function ($query) use ($search) {
    //             return $query->where('name', 'like', '%' . $search . '%');
    //         })
    //         ->select('id', 'name', 'email', 'profile_image', 'delivery_zones')
    //         ->paginate($perPage);

    //     // Decode JSON for each supplier in paginated result
    //     // $suppliers->getCollection()->transform(function ($supplier) {
    //     //     $supplier->delivery_zones = json_decode($supplier->delivery_zones, true);
    //     //     return $supplier;
    //     // });

    //     return response()->json($suppliers);
    // }

    public function getSuppliersWithDeliveryZones(Request $request)
    {
        $perPage = (int) $request->get('per_page', 10);
        $search = $request->get('search');

        // Query to get the suppliers with delivery zones
        $suppliers = User::where('role', 'supplier')
            ->whereNotNull('delivery_zones')
            ->where('delivery_zones', '!=', '[]')
            ->when($search, function ($query) use ($search) {
                return $query->where('name', 'like', '%' . $search . '%');
            })
            ->select('id', 'name', 'email', 'profile_image', 'delivery_zones')
            ->paginate($perPage);

        // Decode delivery zones for each supplier in the paginated result
        $suppliers->getCollection()->transform(function ($supplier) {
            $supplier->delivery_zones = json_decode($supplier->delivery_zones, true);
            return $supplier;
        });

        // Return the paginated result as JSON
        return response()->json($suppliers);
    }


}

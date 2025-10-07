<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Validator;
use Illuminate\Support\Facades\Storage;

class UserManagement extends Controller
{
    // List Users with Multiple Filters and Pagination
    public function index(Request $request)
    {
        $query = User::query();

        // Filters
        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        if ($request->has('isDeleted')) {
            $query->where('isDeleted', $request->isDeleted);
        }

        if ($request->has('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->has('contact_name')) {
            $query->where('contact_name', 'like', '%' . $request->contact_name . '%');
        }

        if ($request->has('location')) {
            $query->where('location', 'like', '%' . $request->location . '%');
        }

        if ($request->has('lat') && $request->has('long')) {
            $query->where('lat', $request->lat)->where('long', $request->long);
        }

        // Get the 'per_page' value from the request, default to 10 if not provided
        $perPage = $request->get('per_page', 10); // Default to 10 if no value is provided

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
            'role' => 'required|string|in:admin,client,supplier',
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
        $user = User::find($id);
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
            'role' => 'string|in:admin,client,supplier',
            'contact_name' => 'nullable|string|max:255',
            'contact_number' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'lat' => 'nullable|numeric',
            'long' => 'nullable|numeric',
            'delivery_radius' => 'nullable|numeric',
            'shipping_address' => 'nullable|string|max:255',
            'billing_address' => 'nullable|string|max:255',
            'profile_image' => 'nullable|image|mimes:jpg,png,jpeg,gif,webp',
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
        ]);

        // Return the updated user information as a JSON response
        return response()->json($user);
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
}

<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\HasApiTokens;

class ApiAuthController extends Controller
{
    // Register Client
    public function registerClient(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'contact_name' => 'required|string|max:255',
            'contact_number' => 'required|string|max:255',
            'shipping_address' => 'required|string|max:255',
            'billing_address' => 'required|string|max:255',
            'profile_image' => 'nullable|image|max:2048', // Optional profile image
            'company_name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $imageUrl = null;
        // Handle profile image
        if ($request->hasFile('profile_image') && $request->file('profile_image')->isValid()) {
            // Store the file under 'profile_images' directory in the 'public' disk
            $path = $request->file('profile_image')->store('profile_images', 'public');
            $imageUrl = 'storage/' . $path; // Relative path to the image
        } 

        // Check if company exists, or create new
        $company = Company::firstOrCreate([
            'name' => Str::lower($request->company_name),
        ]);

        // Generate unique client ID
        do {
            $clientPublicId = 'MC-' . rand(100, 999);
        } while (User::where('client_public_id', $clientPublicId)->exists());

        // Register client
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'client',
            'company_id' => $company->id,
            'contact_name' => $request->contact_name,
            'contact_number' => $request->contact_number,
            'shipping_address' => $request->shipping_address,
            'billing_address' => $request->billing_address,
            'client_public_id' => $clientPublicId,
            'isDeleted' => false,
            'profile_image' => $imageUrl, // Store the relative path
        ]);

        // Create Sanctum Token for the user
        $token = $user->createToken('ClientApp')->plainTextToken;


        return response()->json([
            'message' => 'Client registered successfully.',
            'token' => $token,
            'user' => $user,
            'role' => $user->role,
            'profile_image_url' => $imageUrl, // Return the image URL in response
        ], 201);
    }


    // Register Supplier
    public function registerSupplier(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'contact_name' => 'required|string|max:255',
            'contact_number' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'lat' => 'required|numeric',
            'long' => 'required|numeric',
            'delivery_radius' => 'required|numeric',
            'company_name' => 'required|string|max:255',
            'profile_image' => 'nullable|image|max:2048', // Optional profile image
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Check if company exists, or create new
        $company = Company::firstOrCreate([
            'name' => Str::lower($request->company_name), // Assuming the company name is based on contact name
        ]);

        $imageUrl = null;
        // Handle profile image
        if ($request->hasFile('profile_image') && $request->file('profile_image')->isValid()) {
            // Store the file under 'profile_images' directory in the 'public' disk
            $path = $request->file('profile_image')->store('profile_images', 'public');
            $imageUrl = 'storage/' . $path; // Relative path to the image
        }

        // Register supplier (auto-role assignment)
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'supplier', // Set role as supplier for registration
            'company_id' => $company->id,
            'contact_name' => $request->contact_name,
            'contact_number' => $request->contact_number,
            'location' => $request->location,
            'lat' => $request->lat,
            'long' => $request->long,
            'delivery_radius' => $request->delivery_radius,
            'isDeleted' => false,
            'profile_image' => $imageUrl,
        ]);

        // Create Sanctum Token for the user
        $token = $user->createToken('SupplierApp')->plainTextToken;

        return response()->json([
            'message' => 'Supplier registered successfully.',
            'token' => $token,
            'user' => $user,
            'role' => $user->role,
            'profile_image_url' => $imageUrl, // Return the image URL in response
        ], 201);
    }
    
    // Login Function
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Check credentials
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();

            // Create a new token for the user
            $token = $user->createToken('UserApp')->plainTextToken;

            return response()->json([
                'message' => 'Login successful.',
                'token' => $token,
                'user' => $user,
                'role' => $user->role,
            ], 200);
        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }


    public function checkAuth(Request $request)
    {
        // Check if the user is authenticated using Sanctum or session authentication
        if (Auth::check()) {
            // The user is authenticated
            $user = Auth::user(); // Get the authenticated user

            // Return the user details along with the role
            return response()->json([
                'message' => 'User is authenticated',
                'user' => $user,
                'role' => $user->role,
            ], 200); // 200 OK status code
        } else {
            // The user is not authenticated
            return response()->json([
                'message' => 'User is not authenticated',
            ], 401); // Unauthorized status code
        }
    }
}

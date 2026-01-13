<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

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
            'lat' => 'nullable|numeric',
            'long' => 'nullable|numeric',
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
            'lat' => $request->lat,
            'long' => $request->long,
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
            'location' => 'nullable|string|max:255',
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

        if (!User::where('email', $request->email)->exists()) {
            return response()->json(['error' => 'Email not registered.'], 404);
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

        return response()->json(['error' => 'Password is wrong'], 401);
    }


    public function checkAuth(Request $request)
    {
        // Check if the user is authenticated using Sanctum or session authentication
        if (Auth::check()) {
            // The user is authenticated
            $user = Auth::user()->load('company'); // Get the authenticated user
            if($user->role === 'supplier' && $user->delivery_zones && $user->delivery_zones !== 'null' && $user->delivery_zones !== null ) {
                $user->delivery_zones = $user->delivery_zones;
            }
    
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


    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($request->boolean('all')) {
            $user->tokens()->delete();                 // revoke all tokens
        } else {
            $user->currentAccessToken()?->delete();    // revoke current token
        }
        return response()->json(['message' => 'Logged out'], 200);
    }

    /**
     * Update profile (no password). Handles client and supplier fields.
     * Accepts:
     *  - Common: name, email, contact_name, contact_number, profile_image
     *  - Client: shipping_address, billing_address, lat, long, company_name
     *  - Supplier: location, delivery_radius, company_name
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'name'            => 'sometimes|string|max:255',
            'email'           => 'sometimes|email|unique:users,email,' . $user->id,
            'contact_name'    => 'sometimes|nullable|string|max:255',
            'contact_number'  => 'sometimes|nullable|string|max:255',
            'profile_image'   => 'sometimes|file|image|max:4096',

            // client fields
            'shipping_address'=> 'sometimes|nullable|string|max:255',
            'billing_address' => 'sometimes|nullable|string|max:255',
            'lat'             => 'sometimes|nullable|numeric',
            'long'            => 'sometimes|nullable|numeric',

            // supplier fields
            'location'        => 'sometimes|nullable|string|max:255',
            'delivery_radius' => 'sometimes|nullable|numeric',

            // both roles may set or change company
            'company_name'    => 'sometimes|nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // company
        $companyId = $user->company_id;
        if ($request->filled('company_name')) {
            $company = Company::firstOrCreate(['name' => Str::lower($request->company_name)]);
            $companyId = $company->id;
        }

        // profile image
        $profileImagePath = $user->profile_image;
        if ($request->hasFile('profile_image') && $request->file('profile_image')->isValid()) {
            if ($profileImagePath) {
                $old = str_replace('storage/', '', $profileImagePath);
                Storage::disk('public')->delete($old);
            }
            $stored = $request->file('profile_image')->store('profile_images', 'public');
            $profileImagePath = 'storage/' . $stored;
        }

        // payload
        $data = [
            'name'            => $request->input('name', $user->name),
            'email'           => $request->input('email', $user->email),
            'contact_name'    => $request->input('contact_name', $user->contact_name),
            'contact_number'  => $request->input('contact_number', $user->contact_number),
            'profile_image'   => $profileImagePath,
            'company_id'      => $companyId,
        ];

        if ($user->role === 'client') {
            $data += [
                'shipping_address'=> $request->input('shipping_address', $user->shipping_address),
                'billing_address' => $request->input('billing_address', $user->billing_address),
                'lat'             => $request->input('lat', $user->lat),
                'long'            => $request->input('long', $user->long),
            ];
        }

        if ($user->role === 'supplier') {
            $data += [
                'location'        => $request->input('location', $user->location),
                'delivery_radius' => $request->input('delivery_radius', $user->delivery_radius),
            ];
        }

        $user->update($data);

        return response()->json([
            'message' => 'Profile updated',
            'user'    => $user->fresh((['company'])),
        ], 200);
    }

    /**
     * Change password.
     * Required: current_password, new_password, new_password_confirmation
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password'        => 'required|string',
            'new_password'            => 'required|string|min:6|confirmed',
            // field name must be new_password_confirmation
        ], [
            'new_password.confirmed' => 'New password confirmation does not match.',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['error' => ['current_password' => ['Current password is incorrect.']]], 400);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        // Optional: rotate token on password change
        $request->user()->tokens()->delete();
        $newToken = $user->createToken('UserApp')->plainTextToken;

        return response()->json([
            'message' => 'Password updated',
            'token'   => $newToken,
        ], 200);
    }
    
    
    public function dataHanlde(Request $request)
    {
        // Loop through each user from the request
        foreach ($request->all() as $user) {
            // Generate client_public_id based on the count of users in the database
            $client_public_id = 'MC-' . str_pad(User::count() + 440, 3, '0', STR_PAD_LEFT);
    
            // Prepare user data to insert into the users table
            $userData = [
                'name' => $user['name'],
                'contact_name' => $user['contact_name'],
                'email' => isset($user['email']) ? $user['email'] : null, // Use email if provided, otherwise set to null
                'password' => Hash::make('password'), // Use Hash::make to securely hash the password
                'status' => 'active',
                'client_public_id' => $client_public_id,
                'role' => 'supplier',
                'company_id' => $user['company_id'], // Use the company_id from the request
            ];
    
            // Create a new user record
            User::create($userData);
        }
    
        return response()->json(['message' => 'Users have been successfully added!']);
    }


public function deliveryZonesManagementWithLatLong(Request $request)
{
    // Google Maps API Key
    $googleMapsApiKey = "AIzaSyAUjFL6wmCy8ETAqV1bhFRUEySaUAAX2_k"; // Store your Google Maps API Key in .env file

    // Validate the request
    $validator = Validator::make($request->all(), [
        'zones' => 'sometimes|array',
        'zones.*.user_id' => 'required|exists:users,id', // Ensure the user_id exists in users table
        'zones.*.address' => 'sometimes|string|max:255',
        'zones.*.radius' => 'sometimes|numeric',
    ]);

    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()], 400);
    }

    // Check if zones are provided, otherwise clear the delivery zones
    if (!$request->zones || count($request->zones) === 0) {
        return response()->json(['message' => 'No zones provided'], 400);
    }

    // Initialize an array to collect zones by user_id
    $userZones = [];

    // Loop through each zone and get latitude and longitude using Google Maps API
    foreach ($request->zones as $zone) {
        // Google Maps Geocoding API request
        $response = Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
            'address' => $zone['address'],
            'key' => $googleMapsApiKey,
        ]);

        // Check if the response is successful
        if ($response->successful()) {
            $result = $response->json()['results'][0] ?? null;

            if ($result) {
                // Extract latitude and longitude from the response
                $lat = $result['geometry']['location']['lat'];
                $long = $result['geometry']['location']['lng'];

                // Create the zone data with lat, long, and radius
                $zoneData = [
                    'address' => $zone['address'],
                    'lat' => $lat,
                    'long' => $long,
                    'radius' => $zone['radius'],
                ];

                // Append the zone to the user's zones collection
                if (!isset($userZones[$zone['user_id']])) {
                    $userZones[$zone['user_id']] = [];
                }
                $userZones[$zone['user_id']][] = $zoneData;
            }
        }
    }

    // Now update each user with their corresponding zones
    foreach ($userZones as $userId => $zones) {
        // Find the user by user_id
        $user = DB::table('users')->where('id', $userId)->first();

        if (!$user) {
            return response()->json(['error' => 'Supplier not found for user_id ' . $userId], 404);
        }

        // Check if the user already has zones, and merge new zones if needed
        $existingZones = json_decode($user->delivery_zones, true) ?? [];

        // Merge the existing zones with the new ones
        $mergedZones = array_merge($existingZones, $zones);

        // Update the delivery_zones for the user
        DB::table('users')
            ->where('id', $userId)
            ->update(['delivery_zones' => json_encode($mergedZones)]);
    }

    return response()->json(['message' => 'Delivery zones updated successfully']);
}

}

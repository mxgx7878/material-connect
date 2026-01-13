<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\MasterProducts;
use App\Models\SupplierOffers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Company;
use Illuminate\Support\Facades\Http;

class MasterProductsController extends Controller
{
    /**
     * List all master products.
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 10);
        $page = (int) $request->get('page', 1);

        $query = MasterProducts::with(['added_by', 'approved_by', 'category']);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where('product_name', 'like', "%{$search}%");
        }

        if ($request->filled('category')) {
            $query->where('category', $request->get('category'));
        }

        if ($request->filled('is_approved')) {
            $query->where('is_approved', $request->get('is_approved'));
        }

        // Paginate after filters
        $products = $query->paginate($perPage, ['*'], 'page', $page);

        // Append supplier offers count
        $products->getCollection()->transform(function ($product) {
            $product->supplierOffersCount = SupplierOffers::where('master_product_id', $product->id)->count();
            return $product;
        });

        return response()->json($products, 200);
    }


    /**
     * Show a specific master product.
     */
    public function show($id)
    {
        // Find the product with eager-loaded relationships
        $product = MasterProducts::with(['added_by', 'approved_by', 'category'])->find($id);

        // Check if the product exists
        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        // Load supplier offers and eager load the supplier relationship
        $product->supplierOffers = SupplierOffers::with('supplier')->where('master_product_id', $product->id)->get();

        // Decode delivery_zones manually in case the accessor isn't being triggered
        $product->supplierOffers->each(function ($offer) {
            if ($offer->supplier && isset($offer->supplier->delivery_zones)) {
                // Manually decode the delivery_zones if it's still a string
                $offer->supplier->delivery_zones = json_decode($offer->supplier->delivery_zones, true);
            }
        });

        // Return the response with the product and decoded supplier offer details
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

    public function approveRejectMasterProduct($id)
    {
        $product = MasterProducts::find($id);

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        if($product->is_approved) {
            // If already approved, reject it
            $product->is_approved = false;
            $product->approved_by = null;
        } else {
            // If not approved, approve it
            $product->is_approved = true;
            $product->approved_by = Auth::id();
        }
        $product->save();

        return response()->json(['message' => 'Product approval status updated', 'is_approved' => $product->is_approved], 200);
        
    }

    public function approveRejectSupplierOffer(Request $request , $id)
    {
        $offer = SupplierOffers::find($id);

        if (!$offer) {
            return response()->json(['error' => 'Supplier offer not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:Approved,Rejected,Pending',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        $offer->status = $request->status;
        $offer->save();

        return response()->json(['message' => 'Supplier offer approval status updated', 'status' => $offer->status], 200);
        
    }


    public function importMasterProducts()
    {
        $filePath = base_path('Products-2.csv'); // CSV in Laravel root
        
        if (!file_exists($filePath)) {
            dd("CSV file not found.");
        }

        $handle = fopen($filePath, 'r');
       
        // Skip header row
        $header = fgetcsv($handle);

        while (($row = fgetcsv($handle)) !== false) {

            // CSV Columns Example:
            // 0 => Product Name
            // 1 => Product Type
            // 2 => Unit of Measure
            // 3 => Delivery Method

            $productName     = trim($row[0] ?? null);
            $productType     = trim($row[1] ?? null);
            $unitMeasure     = trim($row[2] ?? null);
            $deliveryMethod  = trim($row[3] ?? null);
       

            if (!$productName) {
                continue; // skip empty rows
            }

            MasterProducts::updateOrCreate(
                ['product_name' => $productName], // unique key
                [
                    'product_type'     => $productType,
                    'unit_of_measure'  => $unitMeasure,
                    'delivery_method'  => $deliveryMethod,

                    'category'         => 1,
                    'is_approved'      => 1,
                    'approved_by'      => 1,
                    'added_by'         => 1,

                    'slug'             => Str::slug($productName),

                    // other fields set null as requested
                    'specifications'   => null,
                    'tech_doc'         => null,
                    'photo'            => null,
                ]
            );
        }

        fclose($handle);

        return "Master products imported successfully.";
    }

    public function importOffers()
    {
        $filePath = base_path('supplier-offers.csv'); // CSV in Laravel root
        
        if (!file_exists($filePath)) {
            dd("CSV file not found.");
        }

        $handle = fopen($filePath, 'r');
        
        // Skip header row
        $header = fgetcsv($handle);

        while (($row = fgetcsv($handle)) !== false) {

            // CSV Columns Example:
            // 0 => Supplier ID
            // 1 => Product ID
            // 2 => Price


            $supplierId = trim($row[0] ?? null);
            $productId = trim($row[1] ?? null);
            $price = trim($row[2] ?? null);

            // Remove the dollar sign and format the price to two decimal places
            $price = str_replace('$', '', $price);
            $price = number_format((float)$price, 2, '.', ''); // Ensure 2 decimal places

            if (!$supplierId || !$productId) {
                continue; // skip empty rows
            }

            SupplierOffers::updateOrCreate(
                [
                    'supplier_id' => $supplierId,
                    'master_product_id' => $productId,
                    'price' => $price,
                    'availability_status' => 'In Stock',
                    'status' => 'Approved',
                ]
            );
        }

        fclose($handle);

        return "Supplier offers imported successfully.";
    }


    public function importUsers()
    {
        $filePath = base_path('suppliers.csv'); // CSV in Laravel root
        
        if (!file_exists($filePath)) {
            dd("CSV file not found.");
        }

        $handle = fopen($filePath, 'r');
        
        // Skip header row
        $header = fgetcsv($handle);

        while (($row = fgetcsv($handle)) !== false) {

            // CSV Columns Example:
            // 0 => Name
            // 1 => Email
            // 2 => Company Name
            // 3 => Contact Number

            $name     = trim($row[0] ?? null);
            $email    = trim($row[1] ?? null);
            $company_name = trim($row[2] ?? null);
            $contact_number     = trim($row[3] ?? null);


            if (!$name || !$email || !$company_name || !$contact_number) {
                continue; // skip empty rows
            }

            $company = Company::firstOrCreate(
                ['name' => $company_name]
            );
            $client_public_id = 'MC-' . str_pad(User::count() + 440, 3, '0', STR_PAD_LEFT);
            User::updateOrCreate(
                ['email' => $email], // unique key
                [
                    'name'     => $company_name,
                    'password' => Hash::make('password'),
                    'role'     => 'supplier',
                    'company_id' => $company->id,
                    'contact_number' => $contact_number,
                    'company_name' => $company_name,
                    'contact_name' => $name,
                    'status' => 'Active',
                    'client_public_id' => $client_public_id,
                ]
            );
        }

        fclose($handle);

        return "Users imported successfully.";
    }



    public function updateDeliveryZonesFromCSV()
    {

        set_time_limit(300);
        // Path to the CSV file in the root directory
        $filePath = base_path('zones.csv'); // Use base_path() to get the project root directory

        // Read the CSV file into an array
        $csvData = array_map('str_getcsv', file($filePath));
        $header = array_shift($csvData); // Extract the header to get column names
        $suppliers = [];

        // Loop through the CSV data and group by supplier name
        foreach ($csvData as $row) {
            $supplierName = $row[0]; // Assuming 'Supplier Name' is in the first column
            $address = $row[1]; // Assuming 'Address' is in the second column
            $radius = $row[2]; // Assuming 'Radius' is in the third column
            
            // Store data grouped by supplier name
            if (!isset($suppliers[$supplierName])) {
                $suppliers[$supplierName] = [];
            }
            
            $suppliers[$supplierName][] = [
                'address' => $address,
                'radius' => $radius
            ];
        }

        // Google Maps API Key
        $googleMapsApiKey = "AIzaSyAUjFL6wmCy8ETAqV1bhFRUEySaUAAX2_k"; // Ensure you set this in your .env file

        // Loop through each supplier, get lat/lng for each address, and update users
        foreach ($suppliers as $supplierName => $zones) {
            // Find the user by supplier name
            $user = User::where('name', $supplierName)->first();
            
            if ($user) {
                $deliveryZones = [];

                foreach ($zones as $zone) {
                    // Fetch lat/long using Google Maps API
                    $response = Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
                        'address' => $zone['address'],
                        'key' => $googleMapsApiKey,
                    ]);

                    $data = $response->json();

                    if (isset($data['results'][0]['geometry']['location'])) {
                        $lat = $data['results'][0]['geometry']['location']['lat'];
                        $long = $data['results'][0]['geometry']['location']['lng'];
                        
                        // Add the data to delivery zones
                        $deliveryZones[] = [
                            'address' => $zone['address'],
                            'lat' => $lat,
                            'long' => $long,
                            'radius' => $zone['radius'],
                        ];
                    }
                }
                // dd($deliveryZones);
                // Encode the delivery zones and save to the user's delivery_zones field
                $user->delivery_zones = json_encode($deliveryZones);
                $user->save();
            }
        }

        return response()->json(['status' => 'Zones updated successfully']);
    }


    public function importOffersWithProducts()
{
    $filePath = base_path('offers.csv'); // CSV in Laravel root
    
    if (!file_exists($filePath)) {
        dd("CSV file not found.");
    }

    $handle = fopen($filePath, 'r');
    
    // Skip header row
    $header = fgetcsv($handle);

    $suppliers = [];

    // Read through the file and group products by supplier name
    while (($row = fgetcsv($handle)) !== false) {

        // CSV Columns Example (based on your clarification):
        // 0 => Supplier Name
        // 1 => Product Name
        // 2 => Product Type
        // 3 => Price
        // 4 => Unit of Measure
        // 5 => Delivery Method

        $supplierName    = trim($row[0] ?? null);
        $productName     = trim($row[1] ?? null);
        $productType     = trim($row[2] ?? null);
        $price           = trim($row[3] ?? null);
        $unitMeasure     = trim($row[4] ?? null);
        $deliveryMethod  = trim($row[5] ?? null);

        if (!$supplierName || !$productName || !$price) {
            continue; // skip empty rows
        }

        // Remove any non-numeric characters (such as currency symbols) from the price
        $price = preg_replace('/[^\d.]/', '', $price); // Remove any non-numeric characters except decimal
        $price = number_format((float)$price, 2, '.', ''); // Ensure two decimal places

        // Group by Supplier Name
        if (!isset($suppliers[$supplierName])) {
            $suppliers[$supplierName] = [];
        }

        $suppliers[$supplierName][] = [
            'product_name'   => $productName,
            'product_type'   => $productType,
            'price'          => $price,
            'unit_measure'   => $unitMeasure,
            'delivery_method'=> $deliveryMethod
        ];
    }

    fclose($handle);

    // Now process each supplier's products
    foreach ($suppliers as $supplierName => $products) {
        // Find the supplier ID by name
        $supplier = User::where('name', $supplierName)->first();

        if ($supplier) {
            foreach ($products as $product) {
                // Check if the product already exists in the MasterProducts table
                $masterProduct = MasterProducts::where('product_name', $product['product_name'])->first();

                if (!$masterProduct) {
                    // If the product doesn't exist, add it to the MasterProducts table
                    $masterProduct = MasterProducts::create([
                        'product_name'   => $product['product_name'],
                        'product_type'   => $product['product_type'],
                        'unit_of_measure'=> $product['unit_measure'],
                        'delivery_method'=> $product['delivery_method'],
                        'category'       => 1,
                        'is_approved'    => 1,
                        'approved_by'    => 1,
                        'added_by'       => 1,
                        'slug'           => Str::slug($product['product_name']),
                    ]);
                }

                // Now, create the offer for the supplier and product
                SupplierOffers::updateOrCreate(
                    [
                        'supplier_id' => $supplier->id,
                        'master_product_id' => $masterProduct->id,
                        'price' => $product['price'], // Correctly formatted price
                        'availability_status' => 'In Stock',
                        'status' => 'Approved',
                    ]
                );
            }
        }
    }

    return "Offers with products imported successfully.";
}


}

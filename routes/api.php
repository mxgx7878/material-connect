<?php

// API Routes
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
//Controller Imports
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserManagement;
use App\Http\Controllers\ApiAuthController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\Admin\MasterProductsController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\OrderController;
//Middleware Imports
use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\isSupplier;
use App\Http\Middleware\isClient;



// Auth Routes - No middleware on login routes
Route::post('register/client', [ApiAuthController::class, 'registerClient']);
Route::post('register/supplier', [ApiAuthController::class, 'registerSupplier']);
Route::post('login', [ApiAuthController::class, 'login']);


//General Authenticated Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [ApiAuthController::class, 'logout']);
    Route::get('user', [ApiAuthController::class, 'checkAuth']);

    // profile + password
    Route::post('profile', [ApiAuthController::class, 'updateProfile']);
    Route::post('change-password', [ApiAuthController::class, 'changePassword']);
    Route::get('categories', [UserManagement::class, 'listCategories']);
});


// Admin Routes - Only apply `isAdmin` middleware to these routes
Route::middleware(['auth:sanctum', IsAdmin::class])->group(function () {
    // User Management CRUD Operations
    Route::get('users', [UserManagement::class, 'index']);
    Route::post('users', [UserManagement::class, 'store']);
    Route::get('users/{id}', [UserManagement::class, 'show']);
    Route::post('users/{id}', [UserManagement::class, 'update']);
    Route::get('user-delete-restore/{id}', [UserManagement::class, 'destroy']);
    
    // Company Check
    Route::post('check-company', [UserManagement::class, 'checkCompany']);
    Route::get('companies', [UserManagement::class, 'getCompanies']);

    //MasterProduct CRUD Operations
    Route::get('master-products', [MasterProductsController::class, 'index']);
    Route::get('master-products/{id}', [MasterProductsController::class, 'show']);
    Route::post('master-products', [MasterProductsController::class, 'store']);
    Route::post('master-products/{id}', [MasterProductsController::class, 'update']);
    Route::delete('master-products/{id}', [MasterProductsController::class, 'destroy']);

    //category routes
    // Route::get('categories', [UserManagement::class, 'listCategories']);
    Route::post('categories', [UserManagement::class, 'addCategory']);
    Route::post('categories/{id}', [UserManagement::class, 'updateCategory']);
    Route::delete('categories/{id}', [UserManagement::class, 'deleteCategory']);
    Route::get('categories/{id}', [UserManagement::class, 'getCategory']);

    //SupplierWithZones
    Route::get('suppliers-with-zones', [UserManagement::class, 'getSuppliersWithDeliveryZones']);
});

//Supplier Routes - Only apply `isSupplier` middleware to these routes
Route::middleware(['auth:sanctum', isSupplier::class])->group(function () {
    // Supplier Product Management
    Route::post('supplier-offers', [SupplierController::class, 'addProductToInventory']);
    Route::post('request-master-product', [SupplierController::class, 'requestNewProduct']);
    Route::get('supplier-products', [SupplierController::class, 'getSupplierProducts']);
    Route::post('update-pricing/{offerId}', [SupplierController::class, 'updateProductPricing']);
    Route::get('master-product-inventory', [SupplierController::class, 'getMasterProductInventory']);
    Route::delete('supplier-offers/{offerId}', [SupplierController::class, 'deleteProductFromInventory']);
    Route::get('supplier-offer-status', [SupplierController::class, 'getSupplierOfferStatus']);

    //Delivery Zones
    Route::post('delivery-zones', [SupplierController::class, 'deliveryZonesManagement']);
    Route::get('delivery-zones', [SupplierController::class, 'getDeliveryZones']);
});


//Client Routes - Only apply `isClient` middleware to these routes
Route::middleware(['auth:sanctum', isClient::class])->group(function () {

    // Project Management CRUD Operations
    Route::get('projects', [ProjectController::class, 'index']);
    Route::get('projects/{id}', [ProjectController::class, 'show']);
    Route::post('projects', [ProjectController::class, 'store']);
    Route::post('projects/{id}', [ProjectController::class, 'update']);
    Route::delete('projects/{id}', [ProjectController::class, 'destroy']);


    // Order Management
    Route::post('orders', [OrderController::class, 'createOrder']);
});



Route::post('/register-admin', [AuthController::class, 'registerAdmin']);

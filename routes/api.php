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
use App\Http\Controllers\XeroController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\SupplierOrderController;
use App\Http\Controllers\Admin\OrderAdminController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\PaymentController;
//Middleware Imports
use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\IsSupplier;
use App\Http\Middleware\IsClient;


// Auth Routes - No middleware on login routes
Route::post('register/client', [ApiAuthController::class, 'registerClient']);
Route::post('register/supplier', [ApiAuthController::class, 'registerSupplier']);
Route::post('login', [ApiAuthController::class, 'login']);
Route::get('xero/callback', [ApiAuthController::class, 'xeroCallback']);
Route::prefix('xero')->group(function () {
    Route::get('/authorize', [XeroController::class, 'authorize']);  // Open in browser
    Route::get('/callback', [XeroController::class, 'callback']);
    Route::get('/status', [XeroController::class, 'status']);
    Route::post('/invoice', [XeroController::class, 'createInvoice']); // Use from Postman
});


//General Authenticated Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [ApiAuthController::class, 'logout']);
    Route::get('user', [ApiAuthController::class, 'checkAuth']);

    // profile + password
    Route::post('profile', [ApiAuthController::class, 'updateProfile']);
    Route::post('change-password', [ApiAuthController::class, 'changePassword']);
    Route::get('categories', [UserManagement::class, 'listCategories']);
    Route::get('product-types', [UserManagement::class, 'listProductTypes']);


    

});


// Admin Routes - Only apply `isAdmin` middleware to these routes
Route::middleware(['auth:sanctum', IsAdmin::class])->group(function () {

    //Dashboard Api
    Route::get('/admin/dashboard/summary', [DashboardController::class, 'summary']);

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
    Route::get('approve-reject-master-product/{id}', [MasterProductsController::class, 'approveRejectMasterProduct']);
    Route::post('approve-reject-supplier-offer/{id}', [MasterProductsController::class, 'approveRejectSupplierOffer']);

    //category routes
    // Route::get('categories', [UserManagement::class, 'listCategories']);
    Route::post('categories', [UserManagement::class, 'addCategory']);
    Route::post('categories/{id}', [UserManagement::class, 'updateCategory']);
    Route::delete('categories/{id}', [UserManagement::class, 'deleteCategory']);
    Route::get('categories/{id}', [UserManagement::class, 'getCategory']);

    //SupplierWithZones
    Route::get('suppliers-with-zones', [UserManagement::class, 'getSuppliersWithDeliveryZones']);


    //order routes
    Route::get('admin/orders', [OrderAdminController::class, 'index']);
    Route::get('admin/orders/{order}', [OrderAdminController::class, 'show']);
    Route::post('admin/orders/{order}/items/{item}/quoted-price', [OrderAdminController::class, 'setItemQuotedPrice']);
    Route::post('admin/orders/{order}/admin-update', [OrderAdminController::class, 'adminUpdate']);
    Route::post('admin/orders/assign-supplier', [OrderAdminController::class, 'assignSupplier']);
    Route::post('admin/update-item-pricing/{orderItem}', [OrderAdminController::class, 'updateOrderPricingAdmin']);
    Route::post('admin/orders/{order}/items/{orderItem}/mark-paid', [OrderAdminController::class, 'supplierPaidStatus']);
    Route::post('admin/orders/update-order-status/{order}', [OrderAdminController::class, 'updateOrderStatus']);
    Route::post('admin/orders/payment-status/{order}', [OrderAdminController::class, 'updatePaymentStatus']);
    Route::delete('admin/delete-order/{order}', [OrderAdminController::class, 'archiveOrder']);

    //Get Archives 
    Route::get('admin/archives', [OrderAdminController::class, 'getArchive']);

});

//Supplier Routes - Only apply `isSupplier` middleware to these routes
Route::middleware(['auth:sanctum', IsSupplier::class])->group(function () {
    // Supplier Product Management
    Route::post('supplier-offers', [SupplierController::class, 'addProductToInventory']);
    Route::post('request-master-product', [SupplierController::class, 'requestNewProduct']);
    Route::get('supplier-products', [SupplierController::class, 'getSupplierProducts']);
    Route::post('update-pricing/{offerId}', [SupplierController::class, 'updateProductPricing']);
    Route::get('master-product-inventory', [SupplierController::class, 'getMasterProductInventory']);
    Route::get('get-supplier-products', [SupplierController::class, 'getSupplierProducts2']);
    Route::delete('supplier-offers/{offerId}', [SupplierController::class, 'deleteProductFromInventory']);
    Route::get('supplier-offer-status', [SupplierController::class, 'getSupplierOfferStatus']);

    //Delivery Zones
    Route::post('delivery-zones', [SupplierController::class, 'deliveryZonesManagement']);
    Route::get('delivery-zones', [SupplierController::class, 'getDeliveryZones']);

    //Supplier Order Management
    Route::get('supplier-orders', [SupplierOrderController::class, 'getSupplierOrders']);
    Route::post('supplier-orders/update-pricing/{orderItem}', [SupplierOrderController::class, 'updateOrderPricing']);
    Route::get('supplier-orders/{order}', [SupplierOrderController::class, 'viewOrderDetails']);
});


//Client Routes - Only apply `isClient` middleware to these routes
Route::middleware(['auth:sanctum', IsClient::class])->group(function () {

    //payment routes
    Route::post('/payment-intent/{orderId}', [PaymentController::class, 'createPaymentIntent']);
    Route::post('/payment-intent/{orderId}', [PaymentController::class, 'createPaymentIntent']);
    Route::post('/process-payment', [PaymentController::class, 'processPayment']);

    // Project Management CRUD Operations
    Route::get('projects', [ProjectController::class, 'index']);
    Route::get('projects/{id}', [ProjectController::class, 'show']);
    Route::get('project-details/{id}', [ProjectController::class, 'projectDetails']);
    Route::post('projects', [ProjectController::class, 'store']);
    Route::post('projects/{project}', [ProjectController::class, 'update']);
    Route::delete('projects/{project}', [ProjectController::class, 'destroy']);


    // Order Management
    Route::post('orders', [OrderController::class, 'createOrder']);
    Route::get('my-orders', [OrderController::class, 'getMyOrders']);
    Route::get('orders/{order}', [OrderController::class, 'viewMyOrder']);
    Route::get('/mark-repeat-order/{order}', [OrderController::class, 'markRepeatOrder']);
    Route::post('repeat-order/{order}', [OrderController::class, 'repeatOrder']);
    Route::post('reorder-from-project', [OrderController::class, 'reorderFromProject']);
    Route::post('set-order-status/{order}', [OrderController::class, 'setOrderStatus']);
    Route::delete('orders/{order}', [OrderController::class, 'archiveOrder']);
    Route::post('order-edit/{order}', [OrderController::class, 'editMyOrder']);

    //Product listing and searching
    Route::get('client/products', [OrderController::class, 'getClientProducts']);
    Route::get('client/products/{id}', [OrderController::class, 'getClientProductDetails']);
    Route::get('client-dashboard-data', [OrderController::class, 'clientDashboard']);
});



Route::post('/register-admin', [AuthController::class, 'registerAdmin']);



// Route::middleware('auth:sanctum')->post('/payments/intents', [PaymentController::class,'createIntent']);
// Route::get('/payments/return', [PaymentController::class,'handleReturn']);
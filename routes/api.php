<?php

// API Routes
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


//Controller Imports
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserManagement;
use App\Http\Controllers\ApiAuthController;
use App\Http\Controllers\ProjectController;
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

    //category routes
    Route::get('categories', [UserManagement::class, 'listCategories']);
    Route::post('categories', [UserManagement::class, 'addCategory']);
    Route::post('categories/{id}', [UserManagement::class, 'updateCategory']);
    Route::delete('categories/{id}', [UserManagement::class, 'deleteCategory']);
    Route::get('categories/{id}', [UserManagement::class, 'getCategory']);
});

//Supplier Routes - Only apply `isSupplier` middleware to these routes
Route::middleware(['auth:sanctum', isSupplier::class])->group(function () {
});


//Client Routes - Only apply `isClient` middleware to these routes
Route::middleware(['auth:sanctum', isClient::class])->group(function () {

    // Project Management CRUD Operations
    Route::get('projects', [ProjectController::class, 'index']);
    Route::get('projects/{id}', [ProjectController::class, 'show']);
    Route::post('projects', [ProjectController::class, 'store']);
    Route::post('projects/{id}', [ProjectController::class, 'update']);
    Route::delete('projects/{id}', [ProjectController::class, 'destroy']);
});



Route::post('/register-admin', [AuthController::class, 'registerAdmin']);

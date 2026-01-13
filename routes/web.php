<?php
// routes/web.php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\MasterProductsController;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('welcome');
});

// Public routes - NO middleware
Route::middleware('guest')->group(function () {
    Route::get('/login', fn() => view('login'))->name('login');
    Route::get('/register', fn() => view('register'))->name('register');
    Route::post('/login-submit', [AuthController::class, 'login'])->name('login.submit');
    Route::post('/register-submit', [AuthController::class, 'register'])->name('register.submit');
});

// Protected routes - auth middleware only
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');
    Route::get('/clients', [AdminController::class, 'clients'])->name('clients');
    Route::get('/clients/create', fn() => view('clients.create'))->name('client.create');
    Route::post('/client-add', [AdminController::class, 'addClient'])->name('client.add');
});

// Logout - no middleware
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/import-products', [MasterProductsController::class, 'importMasterProducts']);
Route::get('/import-offers', [MasterProductsController::class, 'importOffers']);
Route::get('/import-users', [MasterProductsController::class, 'importUsers']);
Route::get('/geocode-suppliers', [MasterProductsController::class, 'updateDeliveryZonesFromCSV']);
Route::get('/import-offers-with-products', [MasterProductsController::class, 'importOffersWithProducts']);
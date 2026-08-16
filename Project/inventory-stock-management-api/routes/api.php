<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\WarehouseController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\StockController;
use App\Http\Controllers\Api\DashboardController;

// Authentication
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {

    // Profile & Logout
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Bisa diakses Admin & User
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/stock-history', [StockController::class, 'history']);

    // ADMIN ONLY
    Route::middleware('admin')->group(function () {

        Route::apiResource('categories', CategoryController::class);

        Route::apiResource('suppliers', SupplierController::class);

        Route::apiResource('warehouses', WarehouseController::class);

        Route::apiResource('products', ProductController::class);

        Route::post('/stock-in', [StockController::class, 'stockIn']);

        Route::post('/stock-out', [StockController::class, 'stockOut']);

        Route::post('/stock-adjustment', [StockController::class, 'stockAdjustment']);
    });

});
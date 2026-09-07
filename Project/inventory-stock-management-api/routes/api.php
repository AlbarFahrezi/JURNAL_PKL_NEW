<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\WarehouseController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\StockController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\TransactionController;

// =====================================================
// AUTHENTICATION
// =====================================================

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


// =====================================================
// PROTECTED ROUTES
// =====================================================

Route::middleware('auth:sanctum')->group(function () {

    // -------------------------------------------------
    // PROFILE & LOGOUT
    // -------------------------------------------------

    Route::get('/profile', [
        AuthController::class,
        'profile'
    ]);

    Route::post('/logout', [
        AuthController::class,
        'logout'
    ]);


    // -------------------------------------------------
    // DASHBOARD & STOCK HISTORY
    // -------------------------------------------------

    Route::get('/dashboard', [
        DashboardController::class,
        'index'
    ]);

    Route::get('/stock-history', [
        StockController::class,
        'history'
    ]);


    // =================================================
    // TRANSACTIONS
    // =================================================

    // List transactions
    Route::get('/transactions', [
        TransactionController::class,
        'index'
    ]);

    // Create transaction as draft
    Route::post('/transactions', [
        TransactionController::class,
        'store'
    ]);

    // Show transaction detail
    Route::get('/transactions/{transaction}', [
        TransactionController::class,
        'show'
    ]);

    // Update draft transaction
    Route::put('/transactions/{transaction}', [
        TransactionController::class,
        'update'
    ]);

    // Delete draft transaction
    Route::delete('/transactions/{transaction}', [
        TransactionController::class,
        'destroy'
    ]);

    // Complete draft transaction
    Route::post('/transactions/{transaction}/complete', [
        TransactionController::class,
        'complete'
    ]);

    // Cancel draft transaction
    Route::post('/transactions/{transaction}/cancel', [
        TransactionController::class,
        'cancel'
    ]);


    // =================================================
    // ADMIN ONLY
    // =================================================

    Route::middleware('admin')->group(function () {

        // Master Data - Category
        Route::apiResource(
            'categories',
            CategoryController::class
        );

        // Master Data - Supplier
        Route::apiResource(
            'suppliers',
            SupplierController::class
        );

        // Master Data - Warehouse
        Route::apiResource(
            'warehouses',
            WarehouseController::class
        );

        // Master Data - Product
        Route::apiResource(
            'products',
            ProductController::class
        );

        // Manual Stock Management
        Route::post('/stock-in', [
            StockController::class,
            'stockIn'
        ]);

        Route::post('/stock-out', [
            StockController::class,
            'stockOut'
        ]);

        Route::post('/stock-adjustment', [
            StockController::class,
            'stockAdjustment'
        ]);
    });
});
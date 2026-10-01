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
use App\Http\Controllers\Api\ReportController;

// =====================================================
// AUTHENTICATION
// =====================================================

Route::post('/register', [
    AuthController::class,
    'register'
]);

Route::post('/login', [
    AuthController::class,
    'login'
]);


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
    // DASHBOARD
    // -------------------------------------------------

    Route::get('/dashboard', [
        DashboardController::class,
        'index'
    ]);


    // -------------------------------------------------
    // STOCK HISTORY
    // -------------------------------------------------

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
    // REPORTS
    // =================================================

    /*
    |--------------------------------------------------------------------------
    | Laporan Harian
    |--------------------------------------------------------------------------
    |
    | Contoh:
    | GET /api/reports/daily
    | GET /api/reports/daily?date=2026-09-30
    |
    */

    Route::get('/reports/daily', [
        ReportController::class,
        'daily'
    ]);


    /*
    |--------------------------------------------------------------------------
    | Laporan Periode
    |--------------------------------------------------------------------------
    |
    | Contoh:
    | GET /api/reports/period?date_from=2026-09-01&date_to=2026-09-30
    |
    | Summary:
    | GET /api/reports/period?date_from=2026-09-01&date_to=2026-09-30&view=summary
    |
    | Detail:
    | GET /api/reports/period?date_from=2026-09-01&date_to=2026-09-30&view=detail
    |
    | Filter IN:
    | GET /api/reports/period?date_from=2026-09-01&date_to=2026-09-30&type=IN
    |
    | Filter OUT:
    | GET /api/reports/period?date_from=2026-09-01&date_to=2026-09-30&type=OUT
    |
    */

    Route::get('/reports/period', [
        ReportController::class,
        'period'
    ]);


    /*
    |--------------------------------------------------------------------------
    | Statistik Laporan
    |--------------------------------------------------------------------------
    |
    | Menghasilkan:
    | - total transaksi
    | - total nilai
    | - rata-rata
    | - nilai tertinggi
    | - nilai terendah
    | - persentase perubahan
    |
    */

    Route::get('/reports/statistics', [
        ReportController::class,
        'statistics'
    ]);


    // =================================================
    // ADMIN ONLY
    // =================================================

    Route::middleware('admin')->group(function () {

        // -------------------------------------------------
        // MASTER DATA - CATEGORY
        // -------------------------------------------------

        Route::apiResource(
            'categories',
            CategoryController::class
        );


        // -------------------------------------------------
        // MASTER DATA - SUPPLIER
        // -------------------------------------------------

        Route::apiResource(
            'suppliers',
            SupplierController::class
        );


        // -------------------------------------------------
        // MASTER DATA - WAREHOUSE
        // -------------------------------------------------

        Route::apiResource(
            'warehouses',
            WarehouseController::class
        );


        // -------------------------------------------------
        // MASTER DATA - PRODUCT
        // -------------------------------------------------

        Route::apiResource(
            'products',
            ProductController::class
        );


        // -------------------------------------------------
        // MANUAL STOCK MANAGEMENT
        // -------------------------------------------------

        // Stock In
        Route::post('/stock-in', [
            StockController::class,
            'stockIn'
        ]);

        // Stock Out
        Route::post('/stock-out', [
            StockController::class,
            'stockOut'
        ]);

        // Stock Adjustment
        Route::post('/stock-adjustment', [
            StockController::class,
            'stockAdjustment'
        ]);
    });
});
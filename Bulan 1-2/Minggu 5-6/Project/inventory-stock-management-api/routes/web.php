<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardWebController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardWebController::class, 'index']);
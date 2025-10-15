<?php

// ============================================
// FILE: routes/api.php
// ============================================

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BranchController;
use App\Http\Controllers\Api\LeadController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes (no authentication required)
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Protected routes (authentication required)
Route::middleware('auth:sanctum')->group(function () {

    // Auth routes
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });

    // Lead routes
    Route::apiResource('leads', LeadController::class);

    // Branch routes
    Route::prefix('branches')->group(function () {
        Route::get('/{branch}/summary', [BranchController::class, 'summary']);
        Route::post('/{branch}/clear-cache', [BranchController::class, 'clearCache'])
            ->middleware('can:admin');
    });
});

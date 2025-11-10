<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CountyController;
use App\Http\Controllers\Api\CityController;
use App\Http\Controllers\Api\PostalCodeController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public read-only routes
Route::get('/counties', [CountyController::class, 'index']);
Route::get('/counties/{county}', [CountyController::class, 'show']);

Route::get('/cities', [CityController::class, 'index']);
Route::get('/cities/{city}', [CityController::class, 'show']);

Route::get('/postal-codes', [PostalCodeController::class, 'index']);
Route::get('/postal-codes/search', [PostalCodeController::class, 'search']);
Route::get('/postal-codes/{postalCode}', [PostalCodeController::class, 'show']);

// Protected routes (authentication required)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Counties
    Route::post('/counties', [CountyController::class, 'store']);
    Route::put('/counties/{county}', [CountyController::class, 'update']);
    Route::delete('/counties/{county}', [CountyController::class, 'destroy']);
    
    // Cities
    Route::post('/cities', [CityController::class, 'store']);
    Route::put('/cities/{city}', [CityController::class, 'update']);
    Route::delete('/cities/{city}', [CityController::class, 'destroy']);
    
    // Postal Codes
    Route::post('/postal-codes', [PostalCodeController::class, 'store']);
    Route::put('/postal-codes/{postalCode}', [PostalCodeController::class, 'update']);
    Route::delete('/postal-codes/{postalCode}', [PostalCodeController::class, 'destroy']);
});
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LapanganController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\ProfileController;

/*
|--------------------------------------------------------------------------
| API Routes - Version 1
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    
    // Public routes
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    
    // Lapangan public routes
    Route::get('/lapangan', [LapanganController::class, 'index']);
    Route::get('/lapangan/{id}', [LapanganController::class, 'show']);
    Route::get('/lapangan/{id}/available-slots', [LapanganController::class, 'availableSlots']);
    Route::post('/lapangan/{id}/calculate-price', [LapanganController::class, 'calculatePrice']);
    
    // Protected routes (require authentication)
    Route::middleware('auth:sanctum')->group(function () {
        
        // Auth
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        
        // Profile
        Route::get('/profile', [ProfileController::class, 'show']);
        Route::put('/profile', [ProfileController::class, 'update']);
        Route::post('/profile/change-password', [ProfileController::class, 'changePassword']);
        Route::get('/profile/points', [ProfileController::class, 'points']);
        
        // Bookings
        Route::get('/bookings', [BookingController::class, 'index']);
        Route::post('/bookings', [BookingController::class, 'store']);
        Route::get('/bookings/{id}', [BookingController::class, 'show']);
        Route::post('/bookings/{id}/upload-payment', [BookingController::class, 'uploadPaymentProof']);
        Route::post('/bookings/{id}/cancel', [BookingController::class, 'cancel']);
        
    });
    
});

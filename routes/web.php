<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/detail/{id}', [HomeController::class, 'detail'])->name('detail');

// Debug route (REMOVE after fixing admin access!)
Route::get('/debug-admin', function() {
    $info = [
        'app_url' => config('app.url'),
        'app_env' => config('app.env'),
        'database_connected' => true,
        'users_count' => 0,
        'admin_count' => 0,
        'admin_users' => [],
    ];
    
    try {
        DB::connection()->getPdo();
        $info['users_count'] = \App\Models\User::count();
        $info['admin_count'] = \App\Models\User::where('is_admin', true)->count();
        $info['admin_users'] = \App\Models\User::where('is_admin', true)
            ->get(['id', 'name', 'email', 'is_admin'])
            ->toArray();
    } catch (\Exception $e) {
        $info['database_connected'] = false;
        $info['error'] = $e->getMessage();
    }
    
    return response()->json($info, 200, [], JSON_PRETTY_PRINT);
});

// Authenticated user dashboard
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/bookings/{id}/cancel', [DashboardController::class, 'cancelBooking'])->name('dashboard.cancel-booking');
    
    // Invoice routes
    Route::get('/invoice/{invoice}/download', [InvoiceController::class, 'download'])->name('invoice.download');
    Route::get('/invoice/{invoice}/view', [InvoiceController::class, 'view'])->name('invoice.view');
    Route::get('/invoice/{invoice}/stream', [InvoiceController::class, 'stream'])->name('invoice.stream');
    
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

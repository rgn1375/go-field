<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Lapangan;
use App\Models\Booking;
use App\Observers\LapanganObserver;
use App\Observers\BookingObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // CRITICAL: Force timezone to Asia/Jakarta
        date_default_timezone_set('Asia/Jakarta');
        
        // Register observers for cache invalidation
        Lapangan::observe(LapanganObserver::class);
        Booking::observe(BookingObserver::class);
    }
}

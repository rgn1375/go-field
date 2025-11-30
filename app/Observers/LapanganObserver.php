<?php

namespace App\Observers;

use App\Models\Lapangan;
use Illuminate\Support\Facades\Cache;

class LapanganObserver
{
    /**
     * Handle the Lapangan "created" event.
     */
    public function created(Lapangan $lapangan): void
    {
        $this->clearLapanganCaches();
    }

    /**
     * Handle the Lapangan "updated" event.
     */
    public function updated(Lapangan $lapangan): void
    {
        $this->clearLapanganCaches();
        
        // Clear specific lapangan detail cache
        Cache::forget("lapangan_detail_{$lapangan->id}");
        Cache::forget("api_lapangan_detail_{$lapangan->id}");
    }

    /**
     * Handle the Lapangan "deleted" event.
     */
    public function deleted(Lapangan $lapangan): void
    {
        $this->clearLapanganCaches();
        
        // Clear specific lapangan caches
        Cache::forget("lapangan_detail_{$lapangan->id}");
        Cache::forget("api_lapangan_detail_{$lapangan->id}");
    }

    /**
     * Handle the Lapangan "restored" event.
     */
    public function restored(Lapangan $lapangan): void
    {
        $this->clearLapanganCaches();
    }

    /**
     * Handle the Lapangan "force deleted" event.
     */
    public function forceDeleted(Lapangan $lapangan): void
    {
        $this->clearLapanganCaches();
    }
    
    /**
     * Clear all lapangan-related caches
     */
    private function clearLapanganCaches(): void
    {
        // Clear home page caches (all pages)
        for ($i = 1; $i <= 10; $i++) {
            Cache::forget("lapangan_home_page_{$i}");
        }
        
        // Clear API list caches (pattern matching not supported, so clear common ones)
        Cache::flush(); // Or use tags if Redis is available
    }
}

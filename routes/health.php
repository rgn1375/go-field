<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

// Health check endpoint for Railway
Route::get('/health', function () {
    try {
        // Check database connection
        DB::connection()->getPdo();
        $dbStatus = 'ok';
    } catch (\Exception $e) {
        $dbStatus = 'error: ' . $e->getMessage();
    }

    try {
        // Check Redis connection
        Cache::driver('redis')->put('health_check', 'ok', 10);
        $redisStatus = Cache::driver('redis')->get('health_check') === 'ok' ? 'ok' : 'error';
    } catch (\Exception $e) {
        $redisStatus = 'error: ' . $e->getMessage();
    }

    $isHealthy = $dbStatus === 'ok' && $redisStatus === 'ok';

    return response()->json([
        'status' => $isHealthy ? 'healthy' : 'unhealthy',
        'timestamp' => now()->toISOString(),
        'app' => config('app.name'),
        'env' => config('app.env'),
        'services' => [
            'database' => $dbStatus,
            'redis' => $redisStatus,
        ]
    ], $isHealthy ? 200 : 503);
});

<?php

namespace App\Providers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class StorageServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Force HTTPS in production for storage URLs
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        // Auto-create storage link if missing (for ephemeral filesystems)
        $this->ensureStorageLinkExists();
    }

    /**
     * Ensure storage link exists (handles ephemeral filesystems like Laravel Cloud)
     */
    protected function ensureStorageLinkExists(): void
    {
        $publicPath = public_path('storage');
        $storagePath = storage_path('app/public');

        // Check if link exists or is broken
        if (!file_exists($publicPath) || !is_link($publicPath)) {
            // Remove if it's a regular directory (not a symlink)
            if (file_exists($publicPath) && !is_link($publicPath)) {
                if (is_dir($publicPath)) {
                    // Don't remove, might have files
                    return;
                }
            }

            // Try to create symlink
            try {
                if (!file_exists($publicPath)) {
                    symlink($storagePath, $publicPath);
                    \Log::info('Storage link created automatically');
                }
            } catch (\Exception $e) {
                \Log::warning('Could not create storage link: ' . $e->getMessage());
            }
        }
    }
}

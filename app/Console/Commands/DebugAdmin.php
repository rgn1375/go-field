<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class DebugAdmin extends Command
{
    protected $signature = 'admin:debug';
    protected $description = 'Debug admin access issues';

    public function handle()
    {
        $this->info('🔍 Debugging Admin Access...');
        $this->newLine();

        // Check database connection
        try {
            \DB::connection()->getPdo();
            $this->info('✅ Database connection: OK');
        } catch (\Exception $e) {
            $this->error('❌ Database connection: FAILED');
            $this->error($e->getMessage());
            return 1;
        }

        // Check if users table exists
        try {
            $userCount = User::count();
            $this->info("✅ Users table exists: {$userCount} users found");
        } catch (\Exception $e) {
            $this->error('❌ Users table not found or error');
            $this->error($e->getMessage());
            return 1;
        }

        // Check for admin users
        $admins = User::where('is_admin', true)->get(['id', 'name', 'email', 'is_admin']);
        
        if ($admins->isEmpty()) {
            $this->warn('⚠️  No admin users found!');
            $this->newLine();
            
            if ($this->confirm('Create admin user now?', true)) {
                $email = $this->ask('Admin email', 'admin@admin.com');
                $password = $this->secret('Admin password (leave empty for admin123)') ?: 'admin123';
                
                User::create([
                    'name' => 'Administrator',
                    'email' => $email,
                    'password' => bcrypt($password),
                    'is_admin' => true,
                    'email_verified_at' => now(),
                    'phone' => '081234567890',
                    'address' => 'Admin Office',
                ]);
                
                $this->info("✅ Admin created: {$email}");
            }
        } else {
            $this->info("✅ Admin users found:");
            foreach ($admins as $admin) {
                $this->line("   - {$admin->name} ({$admin->email}) [ID: {$admin->id}]");
            }
        }

        $this->newLine();

        // Check Filament installation
        if (class_exists(\Filament\Facades\Filament::class)) {
            $this->info('✅ Filament installed');
            
            // Get panel info
            try {
                $panels = \Filament\Facades\Filament::getPanels();
                $this->info('✅ Panels: ' . implode(', ', array_keys($panels)));
            } catch (\Exception $e) {
                $this->warn('⚠️  Could not get panel info');
            }
        } else {
            $this->error('❌ Filament not found');
        }

        $this->newLine();
        $this->info('🎯 Next Steps:');
        $this->line('   1. Try accessing: ' . config('app.url') . '/admin/login');
        $this->line('   2. If still 403, check Laravel Cloud logs');
        $this->line('   3. Ensure APP_URL is set correctly in environment');
        $this->newLine();

        return 0;
    }
}

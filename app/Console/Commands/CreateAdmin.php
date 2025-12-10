<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class CreateAdmin extends Command
{
    protected $signature = 'admin:create {email=admin@admin.com} {password=admin123}';
    protected $description = 'Create admin user for production';

    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');

        // Check if admin already exists
        $existingAdmin = User::where('email', $email)->first();
        
        if ($existingAdmin) {
            // Update existing user to admin
            $existingAdmin->update([
                'is_admin' => true,
                'password' => bcrypt($password),
                'email_verified_at' => now(),
            ]);
            
            $this->info("✅ User {$email} updated to admin!");
            $this->info("🔑 Password set to: {$password}");
            $this->warn("⚠️  Please change the password after first login!");
            return 0;
        }

        // Create new admin
        User::create([
            'name' => 'Administrator',
            'email' => $email,
            'password' => bcrypt($password),
            'is_admin' => true,
            'email_verified_at' => now(),
            'phone' => '081234567890',
            'address' => 'GoField Admin',
        ]);

        $this->info("✅ Admin user created successfully!");
        $this->info("📧 Email: {$email}");
        $this->info("🔑 Password: {$password}");
        $this->warn("⚠️  Please change the password after first login!");
        
        return 0;
    }
}

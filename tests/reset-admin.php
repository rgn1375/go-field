<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo "=== Reset Admin Password ===" . PHP_EOL . PHP_EOL;

// Check if admin exists
$admin = User::first();

if ($admin) {
    echo "User found:" . PHP_EOL;
    echo "  ID: " . $admin->id . PHP_EOL;
    echo "  Name: " . $admin->name . PHP_EOL;
    echo "  Email: " . $admin->email . PHP_EOL . PHP_EOL;
    
    // Update password
    $admin->password = Hash::make('admin123');
    $admin->save();
    
    echo "✓ Password has been reset!" . PHP_EOL . PHP_EOL;
} else {
    echo "No user found. Creating new admin..." . PHP_EOL . PHP_EOL;
    
    // Create new admin
    $admin = User::create([
        'name' => 'Administrator',
        'email' => 'admin@admin.com',
        'password' => Hash::make('admin123'),
    ]);
    
    echo "✓ New admin created!" . PHP_EOL . PHP_EOL;
}

echo "Login Credentials:" . PHP_EOL;
echo "==================" . PHP_EOL;
echo "Email: " . $admin->email . PHP_EOL;
echo "Password: admin123" . PHP_EOL . PHP_EOL;

echo "Login at: http://127.0.0.1:8000/admin" . PHP_EOL;

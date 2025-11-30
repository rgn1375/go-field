<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== SETTING ADMIN ACCESS ===\n\n";

// Set admin@admin.com as admin
$admin = \App\Models\User::where('email', 'admin@admin.com')->first();

if ($admin) {
    $admin->is_admin = true;
    $admin->save();
    
    echo "✅ Admin access granted!\n";
    echo "Email: {$admin->email}\n";
    echo "Name: {$admin->name}\n";
    echo "Is Admin: " . ($admin->is_admin ? 'YES' : 'NO') . "\n\n";
} else {
    echo "❌ Admin user not found!\n\n";
}

// Show all users with admin status
echo "=== ALL USERS ADMIN STATUS ===\n\n";
$users = \App\Models\User::all();

foreach ($users as $user) {
    $adminStatus = $user->is_admin ? '✅ ADMIN' : '❌ USER';
    echo "{$adminStatus} - {$user->name} ({$user->email})\n";
}

echo "\n✅ Done! Only users with is_admin=true can access /admin now.\n";

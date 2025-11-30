<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== DAFTAR ADMIN GOFIELD ===\n\n";

$users = \App\Models\User::all();

foreach ($users as $user) {
    echo "ID: {$user->id}\n";
    echo "Nama: {$user->name}\n";
    echo "Email: {$user->email}\n";
    echo "Phone: {$user->phone}\n";
    echo "Points: {$user->points_balance}\n";
    echo "Created: {$user->created_at->format('Y-m-d H:i:s')}\n";
    echo str_repeat('-', 50) . "\n";
}

echo "\nTotal Users: " . $users->count() . "\n";
echo "\n=== ADMIN DEFAULT ===\n";
echo "Email: admin@admin.com\n";
echo "Password: admin123\n";
echo "URL Admin: /admin\n";

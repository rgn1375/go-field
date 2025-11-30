<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== FORCE LOGOUT NON-ADMIN USERS ===\n\n";

// Delete all session files to force re-login
$sessionPath = storage_path('framework/sessions');
if (is_dir($sessionPath)) {
    $files = glob($sessionPath . '/*');
    foreach($files as $file) {
        if(is_file($file)) {
            unlink($file);
        }
    }
    echo "✅ All sessions deleted\n";
}

// Clear database sessions if using database driver
try {
    DB::table('sessions')->truncate();
    echo "✅ Database sessions cleared\n";
} catch (\Exception $e) {
    echo "ℹ️  No database sessions table (probably using file driver)\n";
}

echo "\n=== CURRENT STATUS ===\n\n";
echo "Admin users (can access /admin):\n";
$admins = \App\Models\User::where('is_admin', true)->get();
foreach ($admins as $admin) {
    echo "  ✅ {$admin->name} ({$admin->email})\n";
}

echo "\nRegular users (CANNOT access /admin):\n";
$users = \App\Models\User::where('is_admin', false)->get();
foreach ($users as $user) {
    echo "  ❌ {$user->name} ({$user->email})\n";
}

echo "\n=== NEXT STEPS ===\n";
echo "1. Refresh browser (hard refresh: Ctrl+Shift+R)\n";
echo "2. Clear browser cookies for this site\n";
echo "3. Try to login with non-admin user\n";
echo "4. Should be rejected/denied access\n";
echo "\n✅ All active sessions have been cleared!\n";

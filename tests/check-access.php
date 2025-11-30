<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CHECKING CURRENT LOGGED IN USER ===\n\n";

$rama = \App\Models\User::where('email', '9inehhhhh@gmail.com')->first();

if ($rama) {
    echo "User: {$rama->name}\n";
    echo "Email: {$rama->email}\n";
    echo "is_admin column value: " . ($rama->is_admin ? 'TRUE' : 'FALSE') . "\n";
    echo "has canAccessPanel method: " . (method_exists($rama, 'canAccessPanel') ? 'YES' : 'NO') . "\n";
    
    // Test canAccessPanel
    try {
        $panel = \Filament\Facades\Filament::getPanel('admin');
        $canAccess = $rama->canAccessPanel($panel);
        echo "canAccessPanel result: " . ($canAccess ? 'TRUE (ALLOWED)' : 'FALSE (DENIED)') . "\n";
    } catch (\Exception $e) {
        echo "Error testing canAccessPanel: " . $e->getMessage() . "\n";
    }
}

echo "\n=== SOLUTION ===\n";
echo "User Rama currently has is_admin = FALSE\n";
echo "But they might still be logged in from before the migration.\n";
echo "To fix:\n";
echo "1. Clear browser cache/cookies\n";
echo "2. Logout from /admin\n";
echo "3. Clear Laravel cache: php artisan cache:clear\n";
echo "4. Try login again - should be DENIED\n";

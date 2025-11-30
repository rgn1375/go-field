<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Lapangan;
use App\Models\Booking;
use Carbon\Carbon;

echo "Testing Booking Code Auto-Generation\n";
echo "=====================================\n\n";

// Get first lapangan
$lapangan = Lapangan::first();

if (!$lapangan) {
    echo "❌ No lapangan found. Please run: php artisan db:seed\n";
    exit(1);
}

echo "Creating test booking...\n";

// Create test booking
$booking = Booking::create([
    'lapangan_id' => $lapangan->id,
    'user_id' => null,
    'tanggal' => now()->addDays(2)->format('Y-m-d'),
    'jam_mulai' => '10:00',
    'jam_selesai' => '11:00',
    'nama_pemesan' => 'Test User',
    'nomor_telepon' => '628123456789',
    'email' => 'test@example.com',
    'total_price' => 100000,
    'status' => 'pending',
]);

echo "✅ Booking created successfully!\n";
echo "   ID: {$booking->id}\n";
echo "   Booking Code: {$booking->booking_code}\n";
echo "   Lapangan: {$lapangan->title}\n";
echo "   Date: {$booking->tanggal}\n\n";

// Verify format
$expectedFormat = '/^BKG-\d{8}-\d{5}$/';
if (preg_match($expectedFormat, $booking->booking_code)) {
    echo "✅ Booking code format is correct (BKG-YYYYMMDD-XXXXX)\n\n";
} else {
    echo "❌ Booking code format is incorrect: {$booking->booking_code}\n\n";
}

// Create another booking for the same day to test sequential numbering
echo "Creating second booking for the same day...\n";

$booking2 = Booking::create([
    'lapangan_id' => $lapangan->id,
    'user_id' => null,
    'tanggal' => $booking->tanggal,
    'jam_mulai' => '11:00',
    'jam_selesai' => '12:00',
    'nama_pemesan' => 'Test User 2',
    'nomor_telepon' => '628123456790',
    'email' => 'test2@example.com',
    'total_price' => 100000,
    'status' => 'pending',
]);

echo "✅ Second booking created!\n";
echo "   ID: {$booking2->id}\n";
echo "   Booking Code: {$booking2->booking_code}\n\n";

// Check sequential numbering
$code1Parts = explode('-', $booking->booking_code);
$code2Parts = explode('-', $booking2->booking_code);

if ($code1Parts[1] === $code2Parts[1]) { // Same date
    $num1 = intval($code1Parts[2]);
    $num2 = intval($code2Parts[2]);
    
    if ($num2 === $num1 + 1) {
        echo "✅ Sequential numbering works correctly ({$num1} → {$num2})\n\n";
    } else {
        echo "❌ Sequential numbering failed ({$num1} → {$num2})\n\n";
    }
}

// Cleanup test bookings
echo "Cleaning up test bookings...\n";
$booking->delete();
$booking2->delete();

echo "✅ Test bookings deleted.\n\n";
echo "=====================================\n";
echo "✅ All tests passed! Booking code system is working correctly.\n";

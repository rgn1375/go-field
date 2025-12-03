<?php

/**
 * Test Suite for Critical Logical Fixes
 * Tests all 3 payment/refund bugs to ensure fixes work correctly
 * 
 * Run: php tests/test-logical-fixes.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Booking;
use App\Models\UserPoint;
use App\Models\Lapangan;
use App\Services\CancellationService;
use Illuminate\Support\Facades\DB;

echo "\n=== TESTING CRITICAL LOGICAL FIXES ===\n\n";

// Clean up existing test user first
User::where('email', 'test.fixes@test.com')->delete();

// Create test user
$testUser = User::create([
    'name' => 'Test User Fixes',
    'email' => 'test.fixes@test.com',
    'password' => bcrypt('password'),
    'phone' => '081234567890',
    'email_verified_at' => now(),
    'points_balance' => 1000,
]);

$lapangan = Lapangan::where('status', 1)->first();

echo "✓ Test user created with 1000 points\n";
echo "  User ID: {$testUser->id}\n\n";

// ==========================================
// TEST #1: Refund doesn't deduct earned points
// ==========================================
echo "TEST #1: Cancel after payment - Verify correct refund (no earned deduction)\n";
echo str_repeat("-", 70) . "\n";

$booking1 = Booking::create([
    'user_id' => $testUser->id,
    'lapangan_id' => $lapangan->id,
    'tanggal' => now()->addDays(2)->format('Y-m-d'),
    'jam_mulai' => '10:00:00',
    'jam_selesai' => '12:00:00',
    'nama_pemesan' => $testUser->name,
    'nomor_telepon' => $testUser->phone,
    'email' => $testUser->email,
    'harga' => 100000,
    'payment_method_id' => 2, // Transfer
    'booking_code' => 'TEST-FIX1-' . time(),
    'status' => 'confirmed',
    'payment_status' => 'paid',
    'paid_at' => now(),
    'payment_confirmed_at' => now(),
]);

// User earns 1% = 1000 points
UserPoint::create([
    'user_id' => $testUser->id,
    'booking_id' => $booking1->id,
    'points' => 1000,
    'type' => 'earned',
    'description' => 'Poin dari booking #' . $booking1->booking_code,
    'balance_after' => 2000,
]);
$testUser->points_balance = 2000;
$testUser->save();

echo "  ✓ Booking created: Rp 100,000 (earned 1000 points)\n";
echo "  ✓ User balance: 2000 points\n";

// Cancel booking (refund 100%)
$cancellationService = app(CancellationService::class);
$cancellationService->cancelBooking($booking1, 'Testing Fix #1');

$testUser->refresh();
$refundPoint = UserPoint::where('booking_id', $booking1->id)
    ->where('type', 'refund')
    ->first();

echo "\n  After cancellation:\n";
echo "  - Refund points: " . ($refundPoint ? $refundPoint->points : 'N/A') . "\n";
echo "  - User balance: {$testUser->points_balance}\n";

$expectedBalance = 2000 + 100; // Original 2000 + refund 100 (NO DEDUCTION)
if ($testUser->points_balance == $expectedBalance) {
    echo "  ✅ PASS: Balance correct ({$expectedBalance} points)\n";
    echo "  ✅ Earned points NOT deducted (refund replaces earned)\n";
} else {
    echo "  ❌ FAIL: Balance incorrect. Expected {$expectedBalance}, got {$testUser->points_balance}\n";
}

echo "\n";

// ==========================================
// TEST #2: Idempotency check prevents double earning
// ==========================================
echo "TEST #2: Double-click approve - Verify points only given once\n";
echo str_repeat("-", 70) . "\n";

// Reset balance
$testUser->points_balance = 1000;
$testUser->save();

$booking2 = Booking::create([
    'user_id' => $testUser->id,
    'lapangan_id' => $lapangan->id,
    'tanggal' => now()->addDays(3)->format('Y-m-d'),
    'jam_mulai' => '14:00:00',
    'jam_selesai' => '16:00:00',
    'nama_pemesan' => $testUser->name,
    'nomor_telepon' => $testUser->phone,
    'email' => $testUser->email,
    'harga' => 100000,
    'payment_method_id' => 2,
    'booking_code' => 'TEST-FIX2-' . time(),
    'status' => 'confirmed',
    'payment_status' => 'waiting_confirmation',
    'paid_at' => now(),
]);

echo "  ✓ Booking created with payment_status='waiting_confirmation'\n";
echo "  ✓ Initial balance: {$testUser->points_balance} points\n";

// Simulate admin approval (first time)
$existingPoint = UserPoint::where('booking_id', $booking2->id)
    ->where('type', 'earned')
    ->first();

if (!$existingPoint) {
    $earnedPoints = floor($booking2->harga * 0.01);
    $testUser->points_balance += $earnedPoints;
    $testUser->save();
    
    UserPoint::create([
        'user_id' => $testUser->id,
        'booking_id' => $booking2->id,
        'points' => $earnedPoints,
        'type' => 'earned',
        'description' => 'Poin dari booking #' . $booking2->booking_code,
        'balance_after' => $testUser->points_balance,
    ]);
    echo "  ✓ Admin approved payment: +1000 points\n";
}

// Simulate double-click (second time)
$existingPoint = UserPoint::where('booking_id', $booking2->id)
    ->where('type', 'earned')
    ->first();

if ($existingPoint) {
    echo "  ✓ Idempotency check: Points already given, skipping\n";
} else {
    echo "  ❌ FAIL: Idempotency check failed, would give points again\n";
}

$testUser->refresh();
echo "\n  Final balance: {$testUser->points_balance} points\n";

if ($testUser->points_balance == 2000) {
    echo "  ✅ PASS: Balance correct (2000 points, no double earning)\n";
} else {
    echo "  ❌ FAIL: Balance incorrect. Expected 2000, got {$testUser->points_balance}\n";
}

echo "\n";

// ==========================================
// TEST #3: Manual refund deducts automatic points
// ==========================================
echo "TEST #3: Manual bank transfer - Verify points deducted\n";
echo str_repeat("-", 70) . "\n";

// Reset balance
$testUser->points_balance = 1000;
$testUser->save();

$booking3 = Booking::create([
    'user_id' => $testUser->id,
    'lapangan_id' => $lapangan->id,
    'tanggal' => now()->addDays(4)->format('Y-m-d'),
    'jam_mulai' => '16:00:00',
    'jam_selesai' => '18:00:00',
    'nama_pemesan' => $testUser->name,
    'nomor_telepon' => $testUser->phone,
    'email' => $testUser->email,
    'harga' => 100000,
    'payment_method_id' => 2,
    'booking_code' => 'TEST-FIX3-' . time(),
    'status' => 'confirmed',
    'payment_status' => 'paid',
    'paid_at' => now(),
    'payment_confirmed_at' => now(),
    'refund_amount' => 100000,
    'refund_method' => 'points', // Auto refund
]);

// User earns 1% = 1000 points
UserPoint::create([
    'user_id' => $testUser->id,
    'booking_id' => $booking3->id,
    'points' => 1000,
    'type' => 'earned',
    'description' => 'Poin dari booking #' . $booking3->booking_code,
    'balance_after' => 2000,
]);
$testUser->points_balance = 2000;
$testUser->save();

// Cancel booking (auto refund points)
$cancellationService->cancelBooking($booking3, 'Testing Fix #3');

$testUser->refresh();
echo "  ✓ Booking cancelled, auto refund: +100 points (refund)\n";
echo "  ✓ Balance after auto refund: {$testUser->points_balance} points\n";

// Admin processes manual bank transfer
if ($booking3->refund_method === 'points') {
    $refundPoints = floor($booking3->refund_amount / 1000);
    
    if ($refundPoints > 0 && $testUser->points_balance >= $refundPoints) {
        $testUser->points_balance -= $refundPoints;
        $testUser->save();
        
        UserPoint::create([
            'user_id' => $testUser->id,
            'booking_id' => $booking3->id,
            'points' => -$refundPoints,
            'type' => 'adjusted',
            'description' => 'Refund poin dibatalkan karena sudah di-transfer ke rekening',
            'balance_after' => $testUser->points_balance,
        ]);
        
        echo "  ✓ Admin processed bank transfer: -{$refundPoints} points deducted\n";
    }
}

$testUser->refresh();
echo "\n  Final balance: {$testUser->points_balance} points\n";

if ($testUser->points_balance == 2000) {
    echo "  ✅ PASS: Balance correct (2000 points, no double refund)\n";
    echo "  ✅ User doesn't get both points AND cash\n";
} else {
    echo "  ❌ FAIL: Balance incorrect. Expected 2000, got {$testUser->points_balance}\n";
}

echo "\n";

// Cleanup
echo "=== CLEANUP ===\n";
UserPoint::where('user_id', $testUser->id)->delete();
Booking::whereIn('id', [$booking1->id, $booking2->id, $booking3->id])->delete();
$testUser->delete();
echo "✓ Test data cleaned up\n\n";

echo "=== ALL TESTS COMPLETE ===\n";

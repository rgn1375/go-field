<?php

/**
 * Test Transaction Creation Flow
 * 
 * This script simulates the payment submission process to verify
 * that Transaction records are created correctly.
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Booking;
use App\Models\PaymentMethod;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

echo "\n========================================\n";
echo "TEST: Transaction Creation Flow\n";
echo "========================================\n\n";

// Get test data
$booking = Booking::where('payment_status', 'unpaid')->first();
$paymentMethod = PaymentMethod::where('code', 'bank_transfer')->first();

if (!$booking) {
    echo "❌ No unpaid booking found for testing\n";
    echo "Creating test booking...\n";
    
    $booking = Booking::create([
        'lapangan_id' => 1,
        'user_id' => 1,
        'tanggal' => now()->addDays(3)->format('Y-m-d'),
        'jam_mulai' => '14:00',
        'jam_selesai' => '15:00',
        'nama_pemesan' => 'Test User',
        'nomor_telepon' => '081234567890',
        'email' => 'test@test.com',
        'harga' => 200000,
        'status' => 'pending',
        'payment_status' => 'unpaid',
    ]);
    
    echo "✅ Test booking created: #{$booking->id}\n\n";
}

if (!$paymentMethod) {
    echo "❌ Payment method not found\n";
    exit(1);
}

echo "Test Data:\n";
echo "  - Booking ID: {$booking->id}\n";
echo "  - Booking Code: {$booking->booking_code}\n";
echo "  - Amount: Rp " . number_format($booking->harga, 0, ',', '.') . "\n";
echo "  - Payment Method: {$paymentMethod->name} ({$paymentMethod->code})\n";
echo "  - Current Status: {$booking->payment_status}\n\n";

// Count transactions before
$transactionsBefore = Transaction::count();
echo "Transactions before: {$transactionsBefore}\n\n";

// Simulate PaymentForm submission
echo "Simulating payment submission...\n";

try {
    DB::beginTransaction();
    
    // Prepare transaction data (same as PaymentForm.php)
    $transactionData = [
        'booking_id' => $booking->id,
        'payment_method_id' => $paymentMethod->id,
        'amount' => $booking->harga,
        'total_amount' => $booking->harga,
        'notes' => 'Test payment submission',
        'status' => 'waiting_confirmation',
        'paid_at' => now(),
    ];
    
    echo "Transaction data prepared:\n";
    print_r($transactionData);
    echo "\n";
    
    // Create transaction
    $transaction = Transaction::create($transactionData);
    
    echo "✅ Transaction created successfully!\n";
    echo "  - Transaction ID: {$transaction->id}\n";
    echo "  - Transaction Code: {$transaction->transaction_code}\n";
    echo "  - Status: {$transaction->status}\n";
    echo "  - Amount: Rp " . number_format($transaction->amount, 0, ',', '.') . "\n\n";
    
    // Update booking
    $booking->update([
        'payment_method_id' => $paymentMethod->id,
        'payment_status' => 'waiting_confirmation',
        'paid_at' => now(),
    ]);
    
    echo "✅ Booking updated successfully!\n";
    echo "  - Payment Status: {$booking->payment_status}\n";
    echo "  - Paid At: {$booking->paid_at}\n\n";
    
    DB::commit();
    
    // Count transactions after
    $transactionsAfter = Transaction::count();
    echo "Transactions after: {$transactionsAfter}\n";
    echo "New transactions created: " . ($transactionsAfter - $transactionsBefore) . "\n\n";
    
    // Verify relationship
    $booking->load('transactions');
    echo "Booking has " . $booking->transactions->count() . " transaction(s)\n";
    
    if ($booking->transactions->count() > 0) {
        echo "✅ Transaction relationship verified!\n\n";
        
        echo "Transaction details:\n";
        foreach ($booking->transactions as $txn) {
            echo "  - Code: {$txn->transaction_code}\n";
            echo "    Status: {$txn->status}\n";
            echo "    Amount: Rp " . number_format($txn->amount, 0, ',', '.') . "\n";
            echo "    Created: {$txn->created_at}\n\n";
        }
    } else {
        echo "❌ Transaction relationship not found!\n\n";
    }
    
    echo "========================================\n";
    echo "✅ TEST PASSED - Transaction created successfully\n";
    echo "========================================\n\n";
    
} catch (\Exception $e) {
    DB::rollBack();
    
    echo "\n❌ ERROR: Transaction creation failed!\n";
    echo "Error message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n\n";
    echo "Stack trace:\n";
    echo $e->getTraceAsString() . "\n\n";
    
    echo "========================================\n";
    echo "❌ TEST FAILED\n";
    echo "========================================\n\n";
    
    exit(1);
}

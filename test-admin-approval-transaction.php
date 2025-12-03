<?php

/**
 * Test Admin Approval Creates Transaction
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Booking;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

echo "\n========================================\n";
echo "TEST: Admin Approval Transaction Creation\n";
echo "========================================\n\n";

// Get booking #9 yang sudah paid
$booking = Booking::find(9);

if (!$booking) {
    echo "❌ Booking #9 not found\n";
    exit(1);
}

echo "Booking Info:\n";
echo "  - ID: {$booking->id}\n";
echo "  - Code: {$booking->booking_code}\n";
echo "  - Status: {$booking->status}\n";
echo "  - Payment Status: {$booking->payment_status}\n";
echo "  - Payment Method ID: " . ($booking->payment_method_id ?? 'NULL') . "\n";
echo "  - Amount: Rp " . number_format($booking->harga, 0, ',', '.') . "\n\n";

// Check existing transactions
$existingTransactions = $booking->transactions()->count();
echo "Existing transactions for this booking: {$existingTransactions}\n\n";

if ($existingTransactions > 0) {
    echo "Transactions:\n";
    foreach ($booking->transactions as $txn) {
        echo "  - {$txn->transaction_code} | Status: {$txn->status} | Amount: Rp " . number_format($txn->amount, 0, ',', '.') . "\n";
    }
    echo "\n✅ Transaction already exists!\n";
    echo "========================================\n\n";
    exit(0);
}

// Simulate admin approval (create transaction if not exists)
echo "Simulating admin approval...\n";

try {
    DB::beginTransaction();
    
    // Check for existing transaction first
    $existingTransaction = $booking->transactions()
        ->whereIn('status', ['waiting_confirmation', 'pending'])
        ->first();
    
    if (!$existingTransaction) {
        echo "No existing transaction found, creating new one...\n";
        
        $transaction = Transaction::create([
            'booking_id' => $booking->id,
            'payment_method_id' => $booking->payment_method_id ?? 1,
            'amount' => $booking->harga,
            'total_amount' => $booking->harga,
            'status' => 'paid',
            'paid_at' => now(),
            'confirmed_at' => now(),
            'confirmed_by' => 1, // Admin ID
            'notes' => 'Admin approved payment directly (backfill test)',
        ]);
        
        echo "✅ Transaction created successfully!\n";
        echo "  - Transaction ID: {$transaction->id}\n";
        echo "  - Transaction Code: {$transaction->transaction_code}\n";
        echo "  - Status: {$transaction->status}\n";
        echo "  - Amount: Rp " . number_format($transaction->amount, 0, ',', '.') . "\n\n";
    } else {
        echo "Transaction exists, updating...\n";
        $existingTransaction->update([
            'status' => 'paid',
            'confirmed_at' => now(),
            'confirmed_by' => 1,
        ]);
        echo "✅ Transaction updated!\n\n";
    }
    
    DB::commit();
    
    // Verify
    $booking->load('transactions');
    echo "Verification:\n";
    echo "  - Total transactions: " . $booking->transactions->count() . "\n";
    
    if ($booking->transactions->count() > 0) {
        echo "  - Latest transaction: {$booking->transactions->first()->transaction_code}\n";
        echo "\n✅ TEST PASSED - Transaction created/updated successfully\n";
    } else {
        echo "\n❌ TEST FAILED - Transaction not found after creation\n";
    }
    
    echo "========================================\n\n";
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n\n";
    echo "========================================\n\n";
    exit(1);
}

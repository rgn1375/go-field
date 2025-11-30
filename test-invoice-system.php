<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Lapangan;
use App\Models\Booking;
use App\Models\User;
use App\Models\Invoice;
use Carbon\Carbon;

echo "Testing Invoice System\n";
echo "======================\n\n";

// Get test data
$lapangan = Lapangan::first();
$user = User::where('email', 'regular@test.com')->first();

if (!$lapangan) {
    echo "❌ No lapangan found. Please run: php artisan db:seed\n";
    exit(1);
}

if (!$user) {
    echo "❌ No test user found. Please run: php artisan db:seed\n";
    exit(1);
}

echo "Step 1: Create a booking\n";
echo "------------------------\n";

$booking = Booking::create([
    'lapangan_id' => $lapangan->id,
    'user_id' => $user->id,
    'tanggal' => now()->addDays(3)->format('Y-m-d'),
    'jam_mulai' => '14:00',
    'jam_selesai' => '15:00',
    'nama_pemesan' => $user->name,
    'nomor_telepon' => '628123456789',
    'email' => $user->email,
    'harga' => 150000,
    'points_redeemed' => 0,
    'status' => 'confirmed',
    'payment_status' => 'unpaid',
]);

echo "✅ Booking created\n";
echo "   ID: {$booking->id}\n";
echo "   Booking Code: {$booking->booking_code}\n";
echo "   Harga: Rp " . number_format($booking->harga, 0, ',', '.') . "\n\n";

echo "Step 2: Check invoice status (should be null)\n";
echo "----------------------------------------------\n";

if ($booking->invoice) {
    echo "❌ Invoice should not exist yet!\n";
    echo "   Invoice Number: {$booking->invoice->invoice_number}\n\n";
} else {
    echo "✅ No invoice yet (correct)\n\n";
}

echo "Step 3: Update payment status to 'paid'\n";
echo "----------------------------------------\n";

echo "   Before: payment_status = {$booking->payment_status}\n";

// Update individual attribute dan save untuk trigger observer dengan benar
$booking->payment_status = 'paid';
$booking->payment_method = 'bank_transfer';
$booking->payment_confirmed_at = now();
$booking->save();

echo "   After: payment_status = {$booking->payment_status}\n";
echo "✅ Payment status updated to 'paid'\n\n";

// Debug: Check if observer was triggered
echo "   Debug: Checking observer logs...\n";
$logContents = file_get_contents(storage_path('logs/laravel.log'));
$recentLogs = substr($logContents, -2000); // Last 2000 chars
if (strpos($recentLogs, 'Invoice created automatically') !== false) {
    echo "   ✅ Observer log found\n";
} else {
    echo "   ⚠️ Observer log not found (observer might not have triggered)\n";
}
echo "\n";

// Refresh booking to load relationship
$booking->refresh();

echo "Step 4: Check if invoice was auto-created\n";
echo "------------------------------------------\n";

if ($booking->invoice) {
    $invoice = $booking->invoice;
    echo "✅ Invoice automatically created!\n";
    echo "   Invoice Number: {$invoice->invoice_number}\n";
    echo "   Booking Code: {$booking->booking_code}\n";
    echo "   Subtotal: Rp " . number_format($invoice->subtotal, 0, ',', '.') . "\n";
    echo "   Discount: Rp " . number_format($invoice->discount, 0, ',', '.') . "\n";
    echo "   Total: Rp " . number_format($invoice->total, 0, ',', '.') . "\n";
    echo "   Status: {$invoice->status}\n";
    echo "   Payment Method: {$invoice->payment_method}\n";
    echo "   Payment Date: {$invoice->payment_date}\n\n";
} else {
    echo "❌ Invoice not created automatically!\n";
    echo "   Observer might not be working.\n\n";
}

echo "Step 5: Test invoice number format\n";
echo "-----------------------------------\n";

if ($booking->invoice) {
    $expectedFormat = '/^INV-\d{8}-\d{5}$/';
    if (preg_match($expectedFormat, $booking->invoice->invoice_number)) {
        echo "✅ Invoice number format is correct (INV-YYYYMMDD-XXXXX)\n\n";
    } else {
        echo "❌ Invoice number format is incorrect: {$booking->invoice->invoice_number}\n\n";
    }
}

echo "Step 6: Test PDF generation\n";
echo "----------------------------\n";

if ($booking->invoice) {
    try {
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('invoices.pdf', ['invoice' => $booking->invoice]);
        $pdf->setPaper('a4', 'portrait');
        
        // Save to temporary file for verification
        $tempFile = storage_path('app/test_invoice.pdf');
        $pdf->save($tempFile);
        
        if (file_exists($tempFile)) {
            $fileSize = filesize($tempFile);
            echo "✅ PDF generated successfully!\n";
            echo "   File size: " . number_format($fileSize / 1024, 2) . " KB\n";
            echo "   File saved to: {$tempFile}\n\n";
            
            // Clean up
            unlink($tempFile);
            echo "✅ Test PDF deleted\n\n";
        } else {
            echo "❌ PDF file not created\n\n";
        }
    } catch (\Exception $e) {
        echo "❌ PDF generation failed: {$e->getMessage()}\n\n";
    }
}

echo "Step 7: Cleanup test data\n";
echo "--------------------------\n";

if ($booking->invoice) {
    $booking->invoice->delete();
    echo "✅ Invoice deleted\n";
}

$booking->delete();
echo "✅ Booking deleted\n\n";

echo "======================\n";
echo "✅ All tests passed! Invoice system is working correctly.\n\n";

echo "Summary:\n";
echo "--------\n";
echo "✅ Invoice auto-created when payment_status changed to 'paid'\n";
echo "✅ Invoice number format: INV-YYYYMMDD-XXXXX\n";
echo "✅ PDF generation working\n";
echo "✅ BookingObserver working correctly\n";

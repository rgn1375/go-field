<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Updating existing bookings with booking codes...\n";

$bookings = DB::table('bookings')->whereNull('booking_code')->get();

echo "Found " . $bookings->count() . " bookings without booking code.\n";

foreach ($bookings as $booking) {
    $date = date('Ymd', strtotime($booking->created_at));
    $bookingCode = 'BKG-' . $date . '-' . str_pad($booking->id, 5, '0', STR_PAD_LEFT);
    
    DB::table('bookings')
        ->where('id', $booking->id)
        ->update(['booking_code' => $bookingCode]);
    
    echo "Updated booking ID {$booking->id} -> {$bookingCode}\n";
}

echo "\nDone! All bookings have been updated.\n";

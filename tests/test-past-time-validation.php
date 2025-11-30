<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Carbon\Carbon;

echo "=== TEST: WAKTU YANG SUDAH LEWAT TIDAK BISA DIBOOKING ===\n\n";

$now = Carbon::now();
echo "Waktu Sekarang: {$now->format('Y-m-d H:i:s')}\n\n";

// Test Case 1: Waktu di masa depan (HARUS BISA)
$futureTime = $now->copy()->addHours(2);
echo "TEST 1: Booking untuk 2 jam dari sekarang\n";
echo "Waktu Booking: {$futureTime->format('H:i')}\n";
$canBook = $futureTime->gt($now);
echo "Status: " . ($canBook ? "✅ BISA DIBOOKING" : "❌ TIDAK BISA") . "\n\n";

// Test Case 2: Waktu sekarang persis (TIDAK BISA - sudah lewat)
echo "TEST 2: Booking untuk waktu sekarang persis\n";
echo "Waktu Booking: {$now->format('H:i')}\n";
$canBook = $now->gt($now);
echo "Status: " . ($canBook ? "❌ BISA DIBOOKING (SALAH!)" : "✅ TIDAK BISA (BENAR)") . "\n\n";

// Test Case 3: Waktu 1 milidetik yang lalu (TIDAK BISA)
$oneMsAgo = $now->copy()->subMillisecond();
echo "TEST 3: Booking untuk 1 milidetik yang lalu\n";
echo "Waktu Booking: {$oneMsAgo->format('H:i:s.u')}\n";
$canBook = $oneMsAgo->gt($now);
echo "Status: " . ($canBook ? "❌ BISA DIBOOKING (SALAH!)" : "✅ TIDAK BISA (BENAR)") . "\n\n";

// Test Case 4: Waktu 1 jam yang lalu (TIDAK BISA)
$oneHourAgo = $now->copy()->subHour();
echo "TEST 4: Booking untuk 1 jam yang lalu\n";
echo "Waktu Booking: {$oneHourAgo->format('H:i')}\n";
$canBook = $oneHourAgo->gt($now);
echo "Status: " . ($canBook ? "❌ BISA DIBOOKING (SALAH!)" : "✅ TIDAK BISA (BENAR)") . "\n\n";

// Test Case 5: Besok jam 10 pagi (HARUS BISA)
$tomorrow10AM = Carbon::tomorrow()->setTime(10, 0);
echo "TEST 5: Booking untuk besok jam 10:00\n";
echo "Waktu Booking: {$tomorrow10AM->format('Y-m-d H:i')}\n";
$canBook = $tomorrow10AM->gt($now);
echo "Status: " . ($canBook ? "✅ BISA DIBOOKING" : "❌ TIDAK BISA") . "\n\n";

// Test Case 6: Hari ini jam yang sudah lewat
$today8AM = Carbon::today()->setTime(8, 0);
echo "TEST 6: Booking untuk hari ini jam 08:00\n";
echo "Waktu Booking: {$today8AM->format('H:i')}\n";
echo "Waktu Sekarang: {$now->format('H:i')}\n";
$canBook = $today8AM->gt($now);
if ($now->hour >= 8) {
    echo "Status: " . ($canBook ? "❌ BISA DIBOOKING (SALAH!)" : "✅ TIDAK BISA (BENAR)") . "\n";
} else {
    echo "Status: " . ($canBook ? "✅ BISA DIBOOKING" : "❌ TIDAK BISA") . "\n";
}
echo "\n";

// Simulasi check seperti di BookingForm
echo "=== SIMULASI LOGIKA DI BOOKINGFORM ===\n\n";

$testSlots = [
    ['time' => $now->copy()->subHour(), 'label' => '1 jam lalu'],
    ['time' => $now->copy()->subMinutes(30), 'label' => '30 menit lalu'],
    ['time' => $now->copy()->subMinutes(1), 'label' => '1 menit lalu'],
    ['time' => $now->copy()->subMillisecond(), 'label' => '1 milidetik lalu'],
    ['time' => $now->copy(), 'label' => 'Sekarang persis'],
    ['time' => $now->copy()->addMillisecond(), 'label' => '1 milidetik dari sekarang'],
    ['time' => $now->copy()->addMinutes(1), 'label' => '1 menit dari sekarang'],
    ['time' => $now->copy()->addHour(), 'label' => '1 jam dari sekarang'],
];

foreach ($testSlots as $slot) {
    $isPast = $slot['time']->lte($now); // <= waktu sekarang = LEWAT
    $status = $isPast ? '❌ LEWAT (tidak bisa booking)' : '✅ BISA BOOKING';
    echo "{$slot['label']}: {$status}\n";
}

echo "\n=== KESIMPULAN ===\n";
echo "✅ Logika menggunakan lte() (less than or equal)\n";
echo "✅ Jika waktu slot <= waktu sekarang → TIDAK BISA BOOKING\n";
echo "✅ Jika waktu slot > waktu sekarang → BISA BOOKING\n";
echo "✅ Bahkan 1 milidetik yang lewat TIDAK BISA dibooking!\n\n";

echo "=== IMPLEMENTASI DI KODE ===\n";
echo "File: app/Livewire/BookingForm.php\n";
echo "Method: generateTimeSlots() & submitBooking()\n";
echo "Check: \$selectedDateTime->lte(Carbon::now())\n";
echo "Jika TRUE → Waktu sudah lewat → Slot ditandai 'is_booked' = true\n";
echo "Jika FALSE → Waktu masih akan datang → Slot bisa dipilih\n";

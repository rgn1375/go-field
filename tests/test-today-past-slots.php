<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Carbon\Carbon;

echo "=== TEST: JAM PAGI HARI INI (SUDAH LEWAT) TIDAK BISA DIBOOKING ===\n\n";

// Simulasi: Sekarang jam 20:00 (8 malam)
$now = Carbon::now();
echo "Waktu Sekarang: {$now->format('Y-m-d H:i:s')} ({$now->format('H:i')})\n";
echo "Jam: {$now->hour}:00\n\n";

// Test: Booking jam 06:00 HARI INI
$selectedDate = Carbon::today(); // Hari ini
$jamMulai = '06:00';

echo "TEST: Booking untuk HARI INI jam {$jamMulai}\n";
echo "Tanggal: {$selectedDate->format('Y-m-d')} (Hari ini)\n";
echo "Jam: {$jamMulai}\n\n";

// Parse slot start time dengan format yang benar
$slotStartTime = Carbon::createFromFormat('Y-m-d H:i', $selectedDate->format('Y-m-d') . ' ' . $jamMulai);

echo "Parsed Slot Time: {$slotStartTime->format('Y-m-d H:i:s')}\n";
echo "Current Time: {$now->format('Y-m-d H:i:s')}\n\n";

// Check apakah sudah lewat
$isPast = $slotStartTime->lte($now);

echo "Comparison:\n";
echo "Slot Time ({$slotStartTime->format('H:i')}) <= Now ({$now->format('H:i')})?\n";
echo "Result: " . ($isPast ? 'TRUE (sudah lewat)' : 'FALSE (belum lewat)') . "\n\n";

if ($isPast) {
    echo "✅ BENAR: Jam 06:00 hari ini TIDAK BISA dibooking (sudah lewat)\n";
} else {
    echo "❌ SALAH: Jam 06:00 hari ini masih bisa dibooking (SEHARUSNYA TIDAK BISA)\n";
}

echo "\n" . str_repeat('=', 70) . "\n\n";

// Test berbagai jam untuk hari ini
echo "=== TEST SEMUA JAM UNTUK HARI INI ===\n\n";

$testSlots = [
    '06:00', '07:00', '08:00', '09:00', '10:00', '11:00', '12:00',
    '13:00', '14:00', '15:00', '16:00', '17:00', '18:00', '19:00',
    '20:00', '21:00',
];

foreach ($testSlots as $slot) {
    $slotTime = Carbon::createFromFormat('Y-m-d H:i', $selectedDate->format('Y-m-d') . ' ' . $slot);
    $isPast = $slotTime->lte($now);
    
    $status = $isPast ? '❌ LEWAT (tidak bisa)' : '✅ BISA BOOKING';
    $comparison = $isPast ? '<=' : '>';
    
    echo sprintf(
        "%s: %s %s %s → %s\n",
        $slot,
        $slotTime->format('H:i'),
        $comparison,
        $now->format('H:i'),
        $status
    );
}

echo "\n" . str_repeat('=', 70) . "\n\n";

// Simulasi spesifik untuk jam 20:00 sekarang
echo "=== SIMULASI: SEKARANG JAM 20:00 (8 MALAM) ===\n\n";

$currentHour = $now->hour;
echo "Jam sekarang: {$currentHour}:00\n";
echo "Semua jam 06:00 - " . ($currentHour) . ":00 hari ini → ❌ SUDAH LEWAT\n";
echo "Semua jam " . ($currentHour + 1) . ":00 - 21:00 hari ini → ✅ MASIH BISA (jika masih ada)\n";
echo "\nKesimpulan:\n";
echo "- Jam 06:00 hari ini → ❌ TIDAK BISA (sudah lewat {" . ($currentHour - 6) . "} jam)\n";
echo "- Jam {$currentHour}:00 hari ini → ❌ TIDAK BISA (tepat sekarang)\n";
if ($currentHour < 20) {
    echo "- Jam 21:00 hari ini → ✅ MASIH BISA (1 jam lagi)\n";
} else {
    echo "- Jam 21:00 hari ini → ❌ TIDAK BISA (sudah lewat atau tepat sekarang)\n";
}

echo "\n" . str_repeat('=', 70) . "\n\n";

echo "✅ Sistem HARUS memblokir jam 06:00 pagi jika sekarang sudah malam!\n";
echo "✅ Logic: \$slotStartTime->lte(\$now) untuk hari yang sama\n";
echo "✅ Jam yang sudah lewat di hari yang sama = TIDAK BISA BOOKING\n";

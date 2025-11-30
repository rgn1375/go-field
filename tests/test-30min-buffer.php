<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Carbon\Carbon;

echo "=== TEST: BUFFER 30 MENIT BOOKING ===\n\n";

// Simulasi waktu sekarang
$now = Carbon::now();
echo "Waktu Sekarang: {$now->format('Y-m-d H:i:s')} ({$now->format('H:i')})\n";
echo "Buffer: 30 menit ke depan = {$now->copy()->addMinutes(30)->format('H:i')}\n\n";

echo str_repeat('=', 70) . "\n\n";

// Test berbagai skenario
$testCases = [
    [
        'slot' => '20:00',
        'description' => 'Jam 20:00 (TEPAT sekarang)',
    ],
    [
        'slot' => '20:15',
        'description' => 'Jam 20:15 (15 menit dari sekarang - DALAM BUFFER)',
    ],
    [
        'slot' => '20:25',
        'description' => 'Jam 20:25 (25 menit dari sekarang - DALAM BUFFER)',
    ],
    [
        'slot' => '20:30',
        'description' => 'Jam 20:30 (TEPAT 30 menit - DALAM BUFFER)',
    ],
    [
        'slot' => '20:31',
        'description' => 'Jam 20:31 (31 menit dari sekarang - DI LUAR BUFFER)',
    ],
    [
        'slot' => '21:00',
        'description' => 'Jam 21:00 (1 jam dari sekarang - DI LUAR BUFFER)',
    ],
    [
        'slot' => '19:00',
        'description' => 'Jam 19:00 (1 jam yang lalu - SUDAH LEWAT)',
    ],
    [
        'slot' => '06:00',
        'description' => 'Jam 06:00 (pagi tadi - SUDAH LEWAT)',
    ],
];

$selectedDate = Carbon::today();
$minimumBookingTime = $now->copy()->addMinutes(30);

echo "ATURAN:\n";
echo "- Slot TIDAK BISA dibooking jika: slotStartTime < (now + 30 menit)\n";
echo "- Slot BISA dibooking jika: slotStartTime >= (now + 30 menit)\n\n";

echo str_repeat('=', 70) . "\n\n";

foreach ($testCases as $index => $test) {
    echo "TEST CASE #" . ($index + 1) . ": {$test['description']}\n";
    echo str_repeat('-', 70) . "\n";
    
    $slotStartTime = Carbon::createFromFormat('Y-m-d H:i', $selectedDate->format('Y-m-d') . ' ' . $test['slot']);
    
    echo "Slot Time: {$slotStartTime->format('Y-m-d H:i:s')} ({$slotStartTime->format('H:i')})\n";
    echo "Now + 30min: {$minimumBookingTime->format('Y-m-d H:i:s')} ({$minimumBookingTime->format('H:i')})\n";
    
    // Logic: slot is unavailable if slotStartTime < minimumBookingTime
    $isPast = $slotStartTime->lt($minimumBookingTime);
    
    if ($isPast) {
        echo "Comparison: {$slotStartTime->format('H:i')} < {$minimumBookingTime->format('H:i')}\n";
        echo "Result: ❌ TIDAK BISA BOOKING (dalam buffer 30 menit atau sudah lewat)\n";
    } else {
        echo "Comparison: {$slotStartTime->format('H:i')} >= {$minimumBookingTime->format('H:i')}\n";
        echo "Result: ✅ BISA BOOKING (di luar buffer 30 menit)\n";
    }
    
    echo "\n";
}

echo str_repeat('=', 70) . "\n\n";

// Summary saat jam 20:00
echo "=== SUMMARY (Jam {$now->format('H:i')}) ===\n\n";

$jamBuka = 6;
$jamTutup = 22;

echo "Operasional: {$jamBuka}:00 - {$jamTutup}:00\n";
echo "Sekarang: {$now->format('H:i')}\n";
echo "Buffer: Minimal 30 menit sebelum jam main\n\n";

echo "Slot yang TIDAK BISA dibooking:\n";
for ($h = $jamBuka; $h < $jamTutup; $h++) {
    $slot = sprintf('%02d:00', $h);
    $slotTime = Carbon::createFromFormat('Y-m-d H:i', $selectedDate->format('Y-m-d') . ' ' . $slot);
    
    if ($slotTime->lt($minimumBookingTime)) {
        $reason = $slotTime->lt($now) ? '(sudah lewat)' : '(dalam buffer 30 menit)';
        echo "  - {$slot} {$reason}\n";
    }
}

echo "\nSlot yang MASIH BISA dibooking:\n";
for ($h = $jamBuka; $h < $jamTutup; $h++) {
    $slot = sprintf('%02d:00', $h);
    $slotTime = Carbon::createFromFormat('Y-m-d H:i', $selectedDate->format('Y-m-d') . ' ' . $slot);
    
    if ($slotTime->gte($minimumBookingTime)) {
        $duration = $slotTime->diffInMinutes($now);
        echo "  - {$slot} ({$duration} menit dari sekarang)\n";
    }
}

echo "\n" . str_repeat('=', 70) . "\n\n";

echo "✅ Sistem HARUS memblokir booking yang:\n";
echo "   1. Jam sudah lewat (slotTime < now)\n";
echo "   2. Kurang dari 30 menit dari sekarang (slotTime < now + 30min)\n\n";

echo "✅ Logic: \$slotStartTime->lt(\$minimumBookingTime)\n";
echo "   dengan \$minimumBookingTime = Carbon::now()->addMinutes(30)\n";

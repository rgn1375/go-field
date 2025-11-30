<?php

require __DIR__.'/vendor/autoload.php';

use Carbon\Carbon;

echo "=== DEBUG: Time Slot Validation ===\n\n";

$now = Carbon::now();
echo "Current Time: {$now->format('Y-m-d H:i:s')} ({$now->format('H:i')})\n";

$minimumBookingTime = $now->copy()->addMinutes(30);
echo "Minimum Booking Time: {$minimumBookingTime->format('Y-m-d H:i:s')} ({$minimumBookingTime->format('H:i')})\n\n";

$selectedDate = Carbon::today();
echo "Selected Date: {$selectedDate->format('Y-m-d')}\n";
echo "Is Today? " . ($selectedDate->isToday() ? 'YES' : 'NO') . "\n\n";

echo "Testing slots:\n";
echo str_repeat('=', 70) . "\n";

$slots = ['06:00', '07:00', '08:00', '14:00', '15:00', '16:00', '17:00', '18:00', '19:00', '20:00', '21:00'];

foreach ($slots as $slot) {
    $slotStartTime = Carbon::createFromFormat('Y-m-d H:i', $selectedDate->format('Y-m-d') . ' ' . $slot);
    $isPast = $slotStartTime->lt($minimumBookingTime);
    
    echo sprintf(
        "Slot %s: %s | isPast=%s | Comparison: %s < %s\n",
        $slot,
        $slotStartTime->format('Y-m-d H:i:s'),
        $isPast ? 'TRUE (DISABLED)' : 'FALSE (AVAILABLE)',
        $slotStartTime->format('H:i'),
        $minimumBookingTime->format('H:i')
    );
}

echo "\n" . str_repeat('=', 70) . "\n";
echo "Expected: All slots before {$minimumBookingTime->format('H:i')} should be DISABLED\n";

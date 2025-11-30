<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule booking reminders daily at 9 AM
Schedule::command('bookings:send-reminders')->dailyAt('09:00');

// Update booking status to completed every hour
Schedule::command('bookings:update-status')->hourly();

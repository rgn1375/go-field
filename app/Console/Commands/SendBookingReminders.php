<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Notifications\BookingReminder;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendBookingReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookings:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminders for bookings happening within the next 24 hours';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Looking for bookings that need reminders...');

        // Get bookings for tomorrow that are confirmed
        $tomorrow = Carbon::tomorrow();
        $bookings = Booking::with('lapangan')
            ->where('tanggal', $tomorrow->format('Y-m-d'))
            ->where('status', 'confirmed')
            ->get();

        if ($bookings->isEmpty()) {
            $this->info('✅ No bookings found for reminder.');
            return Command::SUCCESS;
        }

        $this->info("📧 Found {$bookings->count()} booking(s) to remind.");

        $successCount = 0;
        $failedCount = 0;

        foreach ($bookings as $booking) {
            try {
                $booking->notify(new BookingReminder($booking));
                $successCount++;
                $this->line("✓ Reminder sent for booking #{$booking->id} - {$booking->nama_pemesan}");
                
                Log::info('Booking reminder sent', [
                    'booking_id' => $booking->id,
                    'customer' => $booking->nama_pemesan,
                    'date' => $booking->tanggal,
                ]);
            } catch (\Exception $e) {
                $failedCount++;
                $this->error("✗ Failed to send reminder for booking #{$booking->id}: {$e->getMessage()}");
                
                Log::error('Booking reminder failed', [
                    'booking_id' => $booking->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->newLine();
        $this->info("📊 Summary:");
        $this->info("   Success: {$successCount}");
        $this->info("   Failed: {$failedCount}");
        $this->newLine();

        return Command::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use Carbon\Carbon;

class UpdateBookingStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookings:update-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update booking status to completed for past bookings';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();
        
        // Update booking yang sudah lewat dari confirmed ke completed
        $updated = Booking::where('status', 'confirmed')
            ->where(function ($q) use ($now) {
                // Tanggal sudah lewat
                $q->whereDate('tanggal', '<', $now->toDateString())
                  // Atau hari ini tapi jam selesai sudah lewat
                  ->orWhere(function ($q2) use ($now) {
                      $q2->whereDate('tanggal', '=', $now->toDateString())
                         ->whereTime('jam_selesai', '<=', $now->toTimeString());
                  });
            })
            ->update(['status' => 'completed']);
        
        $this->info("Updated {$updated} booking(s) to completed status.");
        
        return Command::SUCCESS;
    }
}

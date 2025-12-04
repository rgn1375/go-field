<?php

namespace App\Observers;

use App\Models\Booking;
use App\Models\Invoice;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BookingObserver
{
    /**
     * Handle the Booking "created" event.
     */
    public function created(Booking $booking): void
    {
        $this->clearBookingCaches($booking);
    }

    /**
     * Handle the Booking "updated" event.
     */
    public function updated(Booking $booking): void
    {
        $this->clearBookingCaches($booking);
        
        // Auto-create invoice when payment_status changes to 'paid'
        if ($booking->wasChanged('payment_status') && $booking->payment_status === 'paid') {
            $this->createInvoiceForBooking($booking);
        }
    }

    /**
     * Handle the Booking "deleted" event.
     */
    public function deleted(Booking $booking): void
    {
        $this->clearBookingCaches($booking);
    }

    /**
     * Handle the Booking "restored" event.
     */
    public function restored(Booking $booking): void
    {
        $this->clearBookingCaches($booking);
    }

    /**
     * Handle the Booking "force deleted" event.
     */
    public function forceDeleted(Booking $booking): void
    {
        $this->clearBookingCaches($booking);
    }
    
    /**
     * Clear booking-related caches
     */
    private function clearBookingCaches(Booking $booking): void
    {
        // Clear available slots cache for the lapangan on booking date
        Cache::forget("api_slots_{$booking->lapangan_id}_{$booking->tanggal}");
        
        // Clear user-specific caches if needed
        // In production with Redis, you would use tags here
    }
    
    /**
     * Create invoice for booking when payment is confirmed
     */
    private function createInvoiceForBooking(Booking $booking): void
    {
        // Check if invoice already exists
        if ($booking->invoice) {
            Log::info('Invoice already exists for booking', ['booking_id' => $booking->id]);
            return;
        }

        try {
            // Calculate amounts
            $subtotal = $booking->harga ?? 0;
            $discount = $booking->points_redeemed > 0
                ? ($booking->points_redeemed / 100)
                : 0;
            $total = $subtotal - $discount;

            // Create invoice
            $invoice = Invoice::create([
                'booking_id' => $booking->id,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
                'status' => 'paid',
                'payment_date' => $booking->payment_confirmed_at ?? now(),
                'payment_method' => $booking->payment_method,
            ]);

            Log::info('Invoice created automatically', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'booking_id' => $booking->id
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create invoice automatically', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}

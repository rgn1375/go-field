<?php

namespace App\Observers;

use App\Models\Booking;
use App\Models\Invoice;
use App\Models\User;
use App\Notifications\NewBookingNotification;
use App\Notifications\RefundRequestNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class BookingObserver
{
    /**
     * Handle the Booking "created" event.
     */
    public function created(Booking $booking): void
    {
        $this->clearBookingCaches($booking);
        
        // Notify all admins about new booking
        $this->notifyAdminsAboutNewBooking($booking);
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
        
        // Notify admins when status changes to pending_cancellation (refund request)
        if ($booking->wasChanged('status') && $booking->status === 'pending_cancellation' && $booking->refund_amount > 0) {
            $this->notifyAdminsAboutRefundRequest($booking);
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
            $discount = 0;
            $total = $subtotal;

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
    
    /**
     * Notify all admins about new booking
     */
    private function notifyAdminsAboutNewBooking(Booking $booking): void
    {
        try {
            $admins = User::where('is_admin', true)->get();
            
            Notification::send($admins, new NewBookingNotification($booking));
            
            Log::info('Admins notified about new booking', [
                'booking_id' => $booking->id,
                'booking_code' => $booking->booking_code,
                'admin_count' => $admins->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to notify admins about new booking', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Notify all admins about refund request
     */
    private function notifyAdminsAboutRefundRequest(Booking $booking): void
    {
        try {
            $admins = User::where('is_admin', true)->get();
            
            Notification::send($admins, new RefundRequestNotification($booking));
            
            Log::info('Admins notified about refund request', [
                'booking_id' => $booking->id,
                'booking_code' => $booking->booking_code,
                'refund_amount' => $booking->refund_amount,
                'admin_count' => $admins->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to notify admins about refund request', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}

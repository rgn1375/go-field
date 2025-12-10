<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CancellationService
{
    /**
     * Calculate cancellation fee based on booking time
     *
     * Rules:
     * - More than 24 hours before: 100% refund
     * - Less than 24 hours before: 50% refund
     * - After booking time: No refund
     *
     * @param Booking $booking
     * @return array ['refund_percentage' => int, 'refund_amount' => int, 'can_cancel' => bool, 'reason' => string]
     */
    public function calculateRefund(Booking $booking): array
    {
        $now = Carbon::now();
        $bookingDateTime = Carbon::parse($booking->tanggal . ' ' . $booking->jam_mulai);
        
        // Check if booking is already past
        if ($now->greaterThan($bookingDateTime)) {
            return [
                'can_cancel' => false,
                'refund_percentage' => 0,
                'refund_amount' => 0,
                'reason' => 'Tidak dapat membatalkan booking yang sudah berlalu.',
            ];
        }
        
        // Calculate hours until booking
        $hoursUntilBooking = $now->diffInHours($bookingDateTime, false);
        
        // More than 24 hours: full refund
        if ($hoursUntilBooking >= 24) {
            $refundPercentage = 100;
        }
        // Less than 24 hours: 50% refund
        else {
            $refundPercentage = 50;
        }
        
        $refundAmount = ($booking->harga * $refundPercentage) / 100;
        
        return [
            'can_cancel' => true,
            'refund_percentage' => $refundPercentage,
            'refund_amount' => (int) $refundAmount,
            'hours_until_booking' => $hoursUntilBooking,
            'reason' => $refundPercentage === 100
                ? 'Pembatalan lebih dari 24 jam sebelum booking. Refund 100%.'
                : 'Pembatalan kurang dari 24 jam sebelum booking. Refund 50%.',
        ];
    }
    
    /**
     * Process booking cancellation with refund
     *
     * @param Booking $booking
     * @param string|null $cancellationReason
     * @param int|null $userId User who initiated cancellation (null for admin)
     * @return array ['success' => bool, 'message' => string, 'refund_amount' => int]
     */
    public function cancelBooking(Booking $booking, ?string $cancellationReason = null, ?int $userId = null): array
    {
        try {
            DB::beginTransaction();
            
            // Check if booking can be cancelled
            if (!in_array($booking->status, ['pending', 'confirmed'])) {
                return [
                    'success' => false,
                    'message' => 'Booking dengan status ' . $booking->status . ' tidak dapat dibatalkan.',
                    'refund_amount' => 0,
                ];
            }
            
            // Calculate refund
            $refundInfo = $this->calculateRefund($booking);
            
            if (!$refundInfo['can_cancel']) {
                return [
                    'success' => false,
                    'message' => $refundInfo['reason'],
                    'refund_amount' => 0,
                ];
            }
            
            // Update booking status to pending_cancellation (waiting admin approval)
            $booking->status = 'pending_cancellation';
            $booking->cancellation_reason = $cancellationReason ?? 'Dibatalkan oleh pengguna';
            $booking->cancelled_at = now();
            $booking->cancelled_by = $userId;
            $booking->refund_amount = $refundInfo['refund_amount'];
            $booking->refund_percentage = $refundInfo['refund_percentage'];
            
            // Determine refund method
            if ($booking->user_id && $refundInfo['refund_amount'] > 0) {
                // Only refund if payment was already made (paid status)
                if ($booking->payment_status === 'paid') {
                    $booking->refund_method = 'manual';
                    $booking->refund_notes = 'Menunggu konfirmasi admin untuk proses refund.';
                } else {
                    // Not paid yet (unpaid or waiting_confirmation)
                    $booking->refund_method = 'none';
                    $booking->refund_notes = 'Booking dibatalkan sebelum pembayaran dikonfirmasi. Tidak ada refund.';
                    // Reset refund amount karena belum bayar
                    $booking->refund_amount = 0;
                    $booking->refund_percentage = 0;
                }
            }
            
            $booking->save();
            
            DB::commit();
            
            Log::info('Booking cancelled successfully', [
                'booking_id' => $booking->id,
                'user_id' => $booking->user_id,
                'refund_amount' => $refundInfo['refund_amount'],
                'refund_percentage' => $refundInfo['refund_percentage'],
            ]);
            
            return [
                'success' => true,
                'message' => 'Permintaan pembatalan berhasil dikirim. Menunggu konfirmasi admin untuk proses refund ' . $refundInfo['refund_percentage'] . '% (Rp ' . number_format($refundInfo['refund_amount']) . ').',
                'refund_amount' => $refundInfo['refund_amount'],
                'refund_percentage' => $refundInfo['refund_percentage'],
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Booking cancellation failed', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat membatalkan booking. Silakan coba lagi.',
                'refund_amount' => 0,
            ];
        }
    }
    
    /**
     * Check if booking can be cancelled by user
     *
     * @param Booking $booking
     * @param int $userId
     * @return array ['can_cancel' => bool, 'reason' => string]
     */
    public function canUserCancelBooking(Booking $booking, int $userId): array
    {
        // Check ownership
        if ($booking->user_id !== $userId) {
            return [
                'can_cancel' => false,
                'reason' => 'Anda tidak memiliki akses untuk membatalkan booking ini.',
            ];
        }
        
        // Check status
        if (!in_array($booking->status, ['pending', 'confirmed'])) {
            return [
                'can_cancel' => false,
                'reason' => 'Booking dengan status ' . $booking->status . ' tidak dapat dibatalkan.',
            ];
        }
        
        // Check timing
        $refundInfo = $this->calculateRefund($booking);
        
        if (!$refundInfo['can_cancel']) {
            return [
                'can_cancel' => false,
                'reason' => $refundInfo['reason'],
            ];
        }
        
        return [
            'can_cancel' => true,
            'reason' => 'Booking dapat dibatalkan.',
            'refund_info' => $refundInfo,
        ];
    }
}

<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Lapangan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class BookingValidationService
{
    const MINIMUM_BOOKING_BUFFER_MINUTES = 30;
    
    const MAXIMUM_BOOKING_DAYS_ADVANCE = 30;
    
    public function validateBookingRequest(
        Lapangan $lapangan,
        string $tanggal,
        string $jamMulai,
        string $jamSelesai,
        ?int $excludeBookingId = null
    ): array {
        // Step 1: Validate date format
        $dateValidation = $this->validateDateFormat($tanggal);
        if (!$dateValidation['valid']) {
            return $dateValidation;
        }
        
        // Step 2: Validate time format
        $timeValidation = $this->validateTimeFormat($jamMulai, $jamSelesai);
        if (!$timeValidation['valid']) {
            return $timeValidation;
        }
        
        // Step 3: Validate booking is not in the past
        $pastValidation = $this->validateNotPast($tanggal, $jamMulai);
        if (!$pastValidation['valid']) {
            return $pastValidation;
        }
        
        // Step 4: Validate minimum booking buffer (30 minutes)
        $bufferValidation = $this->validateMinimumBuffer($tanggal, $jamMulai);
        if (!$bufferValidation['valid']) {
            return $bufferValidation;
        }
        
        // Step 5: Validate maximum booking window
        $windowValidation = $this->validateBookingWindow($tanggal);
        if (!$windowValidation['valid']) {
            return $windowValidation;
        }
        
        // Step 6: Validate lapangan is operational
        $operationalValidation = $this->validateLapanganOperational($lapangan, $tanggal);
        if (!$operationalValidation['valid']) {
            return $operationalValidation;
        }
        
        // Step 7: Validate time is within operational hours
        $hoursValidation = $this->validateWithinOperationalHours($lapangan, $jamMulai, $jamSelesai);
        if (!$hoursValidation['valid']) {
            return $hoursValidation;
        }
        
        // Step 8: Validate no overlapping bookings
        $overlapValidation = $this->validateNoOverlap($lapangan->id, $tanggal, $jamMulai, $jamSelesai, $excludeBookingId);
        if (!$overlapValidation['valid']) {
            return $overlapValidation;
        }
        
        // Step 9: Validate duration (at least 1 hour, max reasonable duration)
        $durationValidation = $this->validateDuration($jamMulai, $jamSelesai);
        if (!$durationValidation['valid']) {
            return $durationValidation;
        }
        
        return [
            'valid' => true,
            'error' => null,
            'details' => [
                'tanggal' => $tanggal,
                'jam_mulai' => $jamMulai,
                'jam_selesai' => $jamSelesai,
                'lapangan_id' => $lapangan->id,
            ],
        ];
    }
    
    /**
     * Validate date format
     */
    private function validateDateFormat(string $tanggal): array
    {
        try {
            Carbon::createFromFormat('Y-m-d', $tanggal);
            return ['valid' => true];
        } catch (\Exception $e) {
            return [
                'valid' => false,
                'error' => 'Format tanggal tidak valid. Gunakan format YYYY-MM-DD.',
                'details' => ['received' => $tanggal],
            ];
        }
    }
    
    /**
     * Validate time format
     */
    private function validateTimeFormat(string $jamMulai, string $jamSelesai): array
    {
        if (!preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]$/', $jamMulai)) {
            return [
                'valid' => false,
                'error' => 'Format jam mulai tidak valid. Gunakan format HH:MM (24-hour).',
                'details' => ['jam_mulai' => $jamMulai],
            ];
        }
        
        if (!preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]$/', $jamSelesai)) {
            return [
                'valid' => false,
                'error' => 'Format jam selesai tidak valid. Gunakan format HH:MM (24-hour).',
                'details' => ['jam_selesai' => $jamSelesai],
            ];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Validate booking is not in the past
     */
    private function validateNotPast(string $tanggal, string $jamMulai): array
    {
        $bookingDateTime = Carbon::createFromFormat('Y-m-d H:i', $tanggal . ' ' . $jamMulai);
        $now = Carbon::now();
        
        if ($bookingDateTime->lte($now)) {
            return [
                'valid' => false,
                'error' => 'Waktu booking sudah lewat. Silakan pilih waktu di masa depan.',
                'details' => [
                    'booking_time' => $bookingDateTime->format('Y-m-d H:i'),
                    'current_time' => $now->format('Y-m-d H:i'),
                ],
            ];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Validate minimum booking buffer (30 minutes from now)
     */
    private function validateMinimumBuffer(string $tanggal, string $jamMulai): array
    {
        $bookingDateTime = Carbon::createFromFormat('Y-m-d H:i', $tanggal . ' ' . $jamMulai);
        $minimumTime = Carbon::now()->addMinutes(self::MINIMUM_BOOKING_BUFFER_MINUTES);
        
        if ($bookingDateTime->lt($minimumTime)) {
            return [
                'valid' => false,
                'error' => sprintf(
                    'Booking harus dilakukan minimal %d menit sebelum waktu main.',
                    self::MINIMUM_BOOKING_BUFFER_MINUTES
                ),
                'details' => [
                    'booking_time' => $bookingDateTime->format('Y-m-d H:i'),
                    'minimum_time' => $minimumTime->format('Y-m-d H:i'),
                    'buffer_minutes' => self::MINIMUM_BOOKING_BUFFER_MINUTES,
                ],
            ];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Validate maximum booking window
     */
    private function validateBookingWindow(string $tanggal): array
    {
        $bookingDate = Carbon::createFromFormat('Y-m-d', $tanggal);
        $maxDate = Carbon::today()->addDays(self::MAXIMUM_BOOKING_DAYS_ADVANCE);
        
        if ($bookingDate->gt($maxDate)) {
            return [
                'valid' => false,
                'error' => sprintf(
                    'Booking hanya dapat dilakukan maksimal %d hari ke depan.',
                    self::MAXIMUM_BOOKING_DAYS_ADVANCE
                ),
                'details' => [
                    'requested_date' => $bookingDate->format('Y-m-d'),
                    'max_date' => $maxDate->format('Y-m-d'),
                ],
            ];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Validate lapangan is operational on date
     */
    private function validateLapanganOperational(Lapangan $lapangan, string $tanggal): array
    {
        if (!$lapangan->isOperationalOn($tanggal)) {
            $maintenanceInfo = $lapangan->getMaintenanceInfo();
            $error = $maintenanceInfo
                ? 'Lapangan sedang maintenance: ' . $maintenanceInfo['reason']
                : 'Lapangan tidak beroperasi pada tanggal ini.';
            
            return [
                'valid' => false,
                'error' => $error,
                'details' => [
                    'tanggal' => $tanggal,
                    'maintenance_info' => $maintenanceInfo,
                ],
            ];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Validate time is within operational hours
     */
    private function validateWithinOperationalHours(
        Lapangan $lapangan,
        string $jamMulai,
        string $jamSelesai
    ): array {
        $hours = $lapangan->getOperationalHours();
        $jamBuka = $hours['jam_buka'];
        $jamTutup = $hours['jam_tutup'];
        
        if ($jamMulai < $jamBuka || $jamSelesai > $jamTutup) {
            return [
                'valid' => false,
                'error' => sprintf(
                    'Waktu booking harus dalam jam operasional (%s - %s).',
                    substr($jamBuka, 0, 5),
                    substr($jamTutup, 0, 5)
                ),
                'details' => [
                    'jam_mulai' => $jamMulai,
                    'jam_selesai' => $jamSelesai,
                    'jam_buka' => $jamBuka,
                    'jam_tutup' => $jamTutup,
                ],
            ];
        }
        
        return ['valid' => true];
    }
    
    private function validateNoOverlap(
        int $lapanganId,
        string $tanggal,
        string $jamMulai,
        string $jamSelesai,
        ?int $excludeBookingId = null
    ): array {
        $query = Booking::where('lapangan_id', $lapanganId)
            ->where('tanggal', $tanggal)
            ->whereIn('status', ['pending', 'confirmed']);
        
        if ($excludeBookingId) {
            $query->where('id', '!=', $excludeBookingId);
        }
        
        $conflictBooking = $query->where(function ($q) use ($jamMulai, $jamSelesai) {
            // Case 1: Exact match
            $q->where(function ($qq) use ($jamMulai, $jamSelesai) {
                $qq->where('jam_mulai', '=', $jamMulai)
                   ->where('jam_selesai', '=', $jamSelesai);
            })
            // Case 2: New booking starts during existing booking
            ->orWhere(function ($qq) use ($jamMulai) {
                $qq->where('jam_mulai', '<=', $jamMulai)
                   ->where('jam_selesai', '>', $jamMulai);
            })
            // Case 3: New booking ends during existing booking
            ->orWhere(function ($qq) use ($jamSelesai) {
                $qq->where('jam_mulai', '<', $jamSelesai)
                   ->where('jam_selesai', '>=', $jamSelesai);
            })
            // Case 4: New booking completely contains existing booking
            ->orWhere(function ($qq) use ($jamMulai, $jamSelesai) {
                $qq->where('jam_mulai', '>=', $jamMulai)
                   ->where('jam_selesai', '<=', $jamSelesai);
            })
            // Case 5: Existing booking completely contains new booking
            ->orWhere(function ($qq) use ($jamMulai, $jamSelesai) {
                $qq->where('jam_mulai', '<=', $jamMulai)
                   ->where('jam_selesai', '>=', $jamSelesai);
            });
        })->first();
        
        if ($conflictBooking) {
            return [
                'valid' => false,
                'error' => 'Slot waktu ini sudah dibooking oleh pengguna lain. Silakan pilih waktu lain.',
                'details' => [
                    'requested' => [
                        'tanggal' => $tanggal,
                        'jam_mulai' => $jamMulai,
                        'jam_selesai' => $jamSelesai,
                    ],
                    'conflict_with' => [
                        'booking_id' => $conflictBooking->id,
                        'jam_mulai' => $conflictBooking->jam_mulai,
                        'jam_selesai' => $conflictBooking->jam_selesai,
                        'status' => $conflictBooking->status,
                    ],
                ],
            ];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Validate booking duration
     */
    private function validateDuration(string $jamMulai, string $jamSelesai): array
    {
        $start = Carbon::createFromFormat('H:i', $jamMulai);
        $end = Carbon::createFromFormat('H:i', $jamSelesai);
        
        $durationMinutes = $start->diffInMinutes($end);
        
        // Minimum 1 hour
        if ($durationMinutes < 60) {
            return [
                'valid' => false,
                'error' => 'Durasi booking minimal 1 jam.',
                'details' => [
                    'jam_mulai' => $jamMulai,
                    'jam_selesai' => $jamSelesai,
                    'duration_minutes' => $durationMinutes,
                ],
            ];
        }
        
        // Maximum 6 hours (reasonable for sports field)
        if ($durationMinutes > 360) {
            return [
                'valid' => false,
                'error' => 'Durasi booking maksimal 6 jam.',
                'details' => [
                    'jam_mulai' => $jamMulai,
                    'jam_selesai' => $jamSelesai,
                    'duration_minutes' => $durationMinutes,
                ],
            ];
        }
        
        // Must be whole hours
        if ($durationMinutes % 60 !== 0) {
            return [
                'valid' => false,
                'error' => 'Durasi booking harus dalam kelipatan 1 jam.',
                'details' => [
                    'jam_mulai' => $jamMulai,
                    'jam_selesai' => $jamSelesai,
                    'duration_minutes' => $durationMinutes,
                ],
            ];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Check if slot is available (quick check without full validation)
     */
    public function isSlotAvailable(
        int $lapanganId,
        string $tanggal,
        string $jamMulai,
        string $jamSelesai
    ): bool {
        $overlapCheck = $this->validateNoOverlap($lapanganId, $tanggal, $jamMulai, $jamSelesai);
        $bufferCheck = $this->validateMinimumBuffer($tanggal, $jamMulai);
        
        return $overlapCheck['valid'] && $bufferCheck['valid'];
    }
}

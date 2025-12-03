<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lapangan extends Model
{
    protected $table = 'lapangan';

    protected $fillable = [
        'sport_type_id',
        'title',
        'description',
        'price',
        'weekday_price',
        'weekend_price',
        'peak_hour_start',
        'peak_hour_end',
        'peak_hour_multiplier',
        'image',
        'status',
        'jam_buka',
        'jam_tutup',
        'hari_operasional',
        'is_maintenance',
        'maintenance_start',
        'maintenance_end',
        'maintenance_reason',
    ];

    protected $casts = [
        'image' => 'array',
        'price' => 'decimal:2',
        'weekday_price' => 'decimal:2',
        'weekend_price' => 'decimal:2',
        'peak_hour_multiplier' => 'decimal:2',
        'status' => 'integer',
        'hari_operasional' => 'array',
        'is_maintenance' => 'boolean',
        'maintenance_start' => 'date',
        'maintenance_end' => 'date',
    ];

    /**
     * Get the sport type for this lapangan.
     */
    public function sportType()
    {
        return $this->belongsTo(SportType::class);
    }

    /**
     * Get all bookings for this lapangan.
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    // Provide a virtual 'images' attribute for compatibility with views
    public function getImagesAttribute()
    {
        // Prefer an explicit 'images' column if it exists; otherwise use 'image'
        if (array_key_exists('images', $this->attributes)) {
            $raw = $this->attributes['images'];
            $decoded = is_string($raw) ? json_decode($raw, true) : $raw;
            return is_array($decoded) ? $decoded : (array) $decoded;
        }

        $img = $this->getAttribute('image');
        if (is_string($img)) {
            $decoded = json_decode($img, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
            return [trim($img)];
        }
        return is_array($img) ? $img : ([]);
    }
    
    /**
     * Get operational hours (field-specific or global fallback)
     */
    public function getOperationalHours()
    {
        return [
            'jam_buka' => $this->jam_buka ?? \App\Services\SettingsService::get('jam_buka', '06:00'),
            'jam_tutup' => $this->jam_tutup ?? \App\Services\SettingsService::get('jam_tutup', '21:00'),
        ];
    }
    
    /**
     * Check if field is operational on a given date
     */
    public function isOperationalOn($date)
    {
        // Check maintenance
        if ($this->is_maintenance) {
            $checkDate = \Carbon\Carbon::parse($date);
            if ($this->maintenance_start && $this->maintenance_end) {
                if ($checkDate->between($this->maintenance_start, $this->maintenance_end)) {
                    return false;
                }
            }
        }
        
        // Check day of week
        if ($this->hari_operasional && is_array($this->hari_operasional)) {
            $dayOfWeek = \Carbon\Carbon::parse($date)->dayOfWeekIso; // 1=Monday, 7=Sunday
            if (!in_array($dayOfWeek, $this->hari_operasional)) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Get maintenance info if active
     */
    public function getMaintenanceInfo()
    {
        if (!$this->is_maintenance) {
            return null;
        }
        
        return [
            'is_active' => true,
            'start' => $this->maintenance_start,
            'end' => $this->maintenance_end,
            'reason' => $this->maintenance_reason ?? 'Sedang dalam perawatan',
        ];
    }
    
    /**
     * Calculate dynamic price based on date, time, and duration
     *
     * @param string $date - Booking date (Y-m-d)
     * @param string $startTime - Start time (H:i)
     * @param string $endTime - End time (H:i)
     * @return array - ['base_price' => float, 'peak_multiplier' => float, 'total_price' => float, 'is_weekend' => bool, 'is_peak_hour' => bool]
     */
    public function calculatePrice($date, $startTime, $endTime)
    {
        $bookingDate = \Carbon\Carbon::parse($date);
        $start = \Carbon\Carbon::parse($date . ' ' . $startTime);
        $end = \Carbon\Carbon::parse($date . ' ' . $endTime);
        
        // Calculate duration in hours
        $durationHours = $start->diffInMinutes($end) / 60;
        
        // Determine if weekend (Saturday=6, Sunday=7)
        $isWeekend = in_array($bookingDate->dayOfWeekIso, [6, 7]);
        
        // Get base price (weekday/weekend or fallback to regular price)
        if ($isWeekend && $this->weekend_price) {
            $basePrice = $this->weekend_price;
        } elseif (!$isWeekend && $this->weekday_price) {
            $basePrice = $this->weekday_price;
        } else {
            // Fallback to regular price
            $basePrice = $this->price;
        }
        
        // Check if booking falls within peak hours
        $isPeakHour = false;
        $peakMultiplier = 1.0;
        
        if ($this->peak_hour_start && $this->peak_hour_end) {
            $peakStart = \Carbon\Carbon::parse($date . ' ' . $this->peak_hour_start);
            $peakEnd = \Carbon\Carbon::parse($date . ' ' . $this->peak_hour_end);
            
            // Check if any part of the booking overlaps with peak hours
            if ($start->lt($peakEnd) && $end->gt($peakStart)) {
                $isPeakHour = true;
                $peakMultiplier = $this->peak_hour_multiplier ?? 1.5;
            }
        }
        
        // Calculate total price
        $totalPrice = $basePrice * $durationHours * $peakMultiplier;
        
        return [
            'base_price' => $basePrice,
            'duration_hours' => $durationHours,
            'peak_multiplier' => $peakMultiplier,
            'total_price' => round($totalPrice, 2),
            'is_weekend' => $isWeekend,
            'is_peak_hour' => $isPeakHour,
            'price_breakdown' => [
                'base' => $basePrice * $durationHours,
                'peak_additional' => $isPeakHour ? ($basePrice * $durationHours * ($peakMultiplier - 1)) : 0,
            ]
        ];
    }
}

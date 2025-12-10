<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Carbon\Carbon;

class Booking extends Model
{
    use Notifiable;

    /**
     * Boot method to auto-generate booking code
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($booking) {
            if (empty($booking->booking_code)) {
                $booking->booking_code = self::generateBookingCode();
            }
        });
    }

    /**
     * Generate unique booking code
     * Format: BKG-YYYYMMDD-XXXXX
     */
    public static function generateBookingCode(): string
    {
        $date = now()->format('Ymd');
        $prefix = "BKG-{$date}-";
        
        // Get last booking code for today
        $lastBooking = self::whereDate('created_at', now()->toDateString())
            ->where('booking_code', 'like', $prefix . '%')
            ->orderBy('booking_code', 'desc')
            ->first();
        
        if ($lastBooking) {
            // Extract number and increment
            $lastNumber = (int) substr($lastBooking->booking_code, -5);
            $newNumber = $lastNumber + 1;
        } else {
            // First booking of the day
            $newNumber = 1;
        }
        
        // Format: BKG-20251121-00001
        return $prefix . str_pad($newNumber, 5, '0', STR_PAD_LEFT);
    }

    protected $fillable = [
        'booking_code',
        'user_id',
        'lapangan_id',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'nama_pemesan',
        'nomor_telepon',
        'email',
        'harga',
        'payment_method_id',
        'payment_status',
        'payment_proof',
        'paid_at',
        'payment_confirmed_at',
        'payment_confirmed_by',
        'payment_notes',
        'status',
        'cancellation_reason',
        'cancelled_at',
        'cancelled_by',
        'refund_amount',
        'refund_percentage',
        'refund_processed_at',
        'refund_method',
        'refund_notes',
    ];

    protected $casts = [
        'cancelled_at' => 'datetime',
        'refund_processed_at' => 'datetime',
        'paid_at' => 'datetime',
        'payment_confirmed_at' => 'datetime',
    ];

    /**
     * Get the lapangan for this booking.
     */
    public function lapangan()
    {
        return $this->belongsTo(Lapangan::class);
    }

    /**
     * Get the user that made this booking.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the payment method for this booking.
     */
    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    /**
     * Get all transactions for this booking.
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get the invoice for this booking.
     */
    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function getDurationAttribute()
    {
        $mulai = Carbon::parse($this->jam_mulai);
        $selesai = Carbon::parse($this->jam_selesai);

        $durasi = $mulai->diffInHours($selesai);
        return $durasi . ' jam';
    }

    /**
     * Route notifications for the mail channel.
     */
    public function routeNotificationForMail(): ?string
    {
        return $this->email;
    }

    /**
     * Route notifications for WhatsApp channel.
     */
    public function routeNotificationForWhatsApp(): ?string
    {
        return $this->nomor_telepon;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number',
        'booking_id',
        'subtotal',
        'discount',
        'total',
        'payment_date',
        'payment_method',
        'status',
        'notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'payment_date' => 'datetime',
    ];

    /**
     * Boot method untuk auto-generate invoice number
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = static::generateInvoiceNumber();
            }
        });
    }

    /**
     * Generate unique invoice number dengan format INV-YYYYMMDD-XXXXX
     */
    protected static function generateInvoiceNumber(): string
    {
        $date = now()->format('Ymd');
        $lastInvoice = static::whereDate('created_at', now())
            ->orderBy('id', 'desc')
            ->first();
        
        $number = $lastInvoice ? (intval(substr($lastInvoice->invoice_number, -5)) + 1) : 1;
        
        return 'INV-' . $date . '-' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Relationship dengan Booking
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Mark invoice sebagai paid
     */
    public function markAsPaid(string $paymentMethod = null): void
    {
        $this->update([
            'status' => 'paid',
            'payment_date' => now(),
            'payment_method' => $paymentMethod,
        ]);
    }

    /**
     * Check apakah invoice sudah dibayar
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * Get formatted invoice number untuk display
     */
    public function getFormattedNumberAttribute(): string
    {
        return $this->invoice_number;
    }

    /**
     * Get formatted total dengan currency
     */
    public function getFormattedTotalAttribute(): string
    {
        return 'Rp ' . number_format($this->total, 0, ',', '.');
    }
}

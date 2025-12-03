<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Transaction extends Model
{
    /**
     * Boot method to auto-generate transaction code
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            if (empty($transaction->transaction_code)) {
                $transaction->transaction_code = self::generateTransactionCode();
            }
        });
    }

    /**
     * Generate unique transaction code
     * Format: TRX-YYYYMMDD-XXXXX
     */
    public static function generateTransactionCode(): string
    {
        $date = now()->format('Ymd');
        $prefix = "TRX-{$date}-";
        
        // Get last transaction code for today
        $lastTransaction = self::whereDate('created_at', now()->toDateString())
            ->where('transaction_code', 'like', $prefix . '%')
            ->orderBy('transaction_code', 'desc')
            ->first();
        
        if ($lastTransaction) {
            // Extract number and increment
            $lastNumber = (int) substr($lastTransaction->transaction_code, -5);
            $newNumber = $lastNumber + 1;
        } else {
            // First transaction of the day
            $newNumber = 1;
        }
        
        // Format: TRX-20251203-00001
        return $prefix . str_pad($newNumber, 5, '0', STR_PAD_LEFT);
    }

    protected $fillable = [
        'transaction_code',
        'booking_id',
        'payment_method_id',
        'amount',
        'admin_fee',
        'total_amount',
        'status',
        'payment_proof',
        'notes',
        'admin_notes',
        'paid_at',
        'confirmed_at',
        'confirmed_by',
        'refunded_at',
        'refund_amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'admin_fee' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    /**
     * Get the booking for this transaction.
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get the payment method for this transaction.
     */
    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    /**
     * Get the user who confirmed this transaction.
     */
    public function confirmedBy()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    /**
     * Scope to get pending transactions.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get paid transactions.
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope to get waiting confirmation transactions.
     */
    public function scopeWaitingConfirmation($query)
    {
        return $query->where('status', 'waiting_confirmation');
    }

    /**
     * Mark transaction as paid.
     */
    public function markAsPaid($confirmedBy = null)
    {
        $this->update([
            'status' => 'paid',
            'confirmed_at' => now(),
            'confirmed_by' => $confirmedBy,
        ]);
    }

    /**
     * Mark transaction as failed.
     */
    public function markAsFailed($reason = null)
    {
        $this->update([
            'status' => 'failed',
            'admin_notes' => $reason,
        ]);
    }

    /**
     * Process refund for this transaction.
     */
    public function processRefund($refundAmount, $reason = null)
    {
        $this->update([
            'status' => 'refunded',
            'refund_amount' => $refundAmount,
            'refunded_at' => now(),
            'admin_notes' => $reason,
        ]);
    }
}

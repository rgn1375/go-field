<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'logo',
        'is_active',
        'config',
        'admin_fee',
        'admin_fee_percentage',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'config' => 'array',
        'admin_fee' => 'decimal:2',
        'admin_fee_percentage' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    /**
     * Get all transactions using this payment method.
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get all bookings using this payment method.
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Scope to get only active payment methods.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order by sort order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Calculate total admin fee for given amount.
     */
    public function calculateAdminFee($amount)
    {
        $fixedFee = $this->admin_fee;
        $percentageFee = ($amount * $this->admin_fee_percentage) / 100;
        
        return $fixedFee + $percentageFee;
    }

    /**
     * Calculate total amount including admin fee.
     */
    public function calculateTotalAmount($amount)
    {
        return $amount + $this->calculateAdminFee($amount);
    }
}

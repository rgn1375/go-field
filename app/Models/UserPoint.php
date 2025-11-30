<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPoint extends Model
{
    protected $fillable = [
        'user_id',
        'booking_id',
        'type',
        'points',
        'balance_after',
        'description',
    ];

    protected $casts = [
        'type' => 'string',
        'points' => 'integer',
        'balance_after' => 'integer',
    ];

    /**
     * Get the user that owns the point transaction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the booking associated with this point transaction.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}

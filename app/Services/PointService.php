<?php

namespace App\Services;

use App\Models\User;
use App\Models\Booking;
use App\Models\UserPoint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PointService
{
    /**
     * Point earning rate: 1% of booking price
     */
    const EARN_RATE = 0.01;

    /**
     * Point redemption rate: 100 points = Rp 1,000
     */
    const REDEEM_RATE = 100; // points
    const REDEEM_VALUE = 1000; // rupiah

    /**
     * Calculate points earned from booking price.
     */
    public function calculateEarnedPoints(int $price): int
    {
        return (int) floor($price * self::EARN_RATE);
    }

    /**
     * Calculate discount from redeemed points.
     */
    public function calculateDiscount(int $points): int
    {
        return (int) floor(($points / self::REDEEM_RATE) * self::REDEEM_VALUE);
    }

    /**
     * Award points to user after booking completion.
     */
    public function awardPoints(User $user, Booking $booking, int $points): UserPoint
    {
        return DB::transaction(function () use ($user, $booking, $points) {
            // Update user balance
            $user->increment('points_balance', $points);
            $user->refresh();

            // Create transaction record
            $transaction = UserPoint::create([
                'user_id' => $user->id,
                'booking_id' => $booking->id,
                'type' => 'earned',
                'points' => $points,
                'balance_after' => $user->points_balance,
                'description' => "Earned from booking #{$booking->id} - {$booking->lapangan->title}",
            ]);

            // Update booking record
            $booking->update(['points_earned' => $points]);

            Log::info("Awarded {$points} points to user {$user->id} for booking {$booking->id}");

            return $transaction;
        });
    }

    /**
     * Redeem points for discount.
     */
    public function redeemPoints(User $user, Booking $booking, int $points): UserPoint
    {
        if ($user->points_balance < $points) {
            throw new \Exception('Insufficient points balance');
        }

        return DB::transaction(function () use ($user, $booking, $points) {
            // Update user balance (deduct points)
            $user->decrement('points_balance', $points);
            $user->refresh();

            // Create transaction record (negative points)
            $transaction = UserPoint::create([
                'user_id' => $user->id,
                'booking_id' => $booking->id,
                'type' => 'redeemed',
                'points' => -$points, // Negative for redemption
                'balance_after' => $user->points_balance,
                'description' => "Redeemed for booking #{$booking->id} - {$booking->lapangan->title}",
            ]);

            // Update booking record
            $booking->update(['points_redeemed' => $points]);

            Log::info("Redeemed {$points} points from user {$user->id} for booking {$booking->id}");

            return $transaction;
        });
    }

    /**
     * Manually adjust user points (admin only).
     */
    public function adjustPoints(User $user, int $points, string $reason): UserPoint
    {
        return DB::transaction(function () use ($user, $points, $reason) {
            // Update user balance
            if ($points > 0) {
                $user->increment('points_balance', $points);
            } else {
                $user->decrement('points_balance', abs($points));
            }
            $user->refresh();

            // Create transaction record
            return UserPoint::create([
                'user_id' => $user->id,
                'type' => 'adjusted',
                'points' => $points,
                'balance_after' => $user->points_balance,
                'description' => $reason,
            ]);
        });
    }

    /**
     * Refund points if booking is cancelled.
     */
    public function refundPoints(Booking $booking): ?UserPoint
    {
        if (!$booking->user_id || $booking->points_redeemed <= 0) {
            return null;
        }

        $user = $booking->user;

        return DB::transaction(function () use ($user, $booking) {
            $points = $booking->points_redeemed;

            // Return points to user
            $user->increment('points_balance', $points);
            $user->refresh();

            // Create refund transaction
            $transaction = UserPoint::create([
                'user_id' => $user->id,
                'booking_id' => $booking->id,
                'type' => 'adjusted',
                'points' => $points,
                'balance_after' => $user->points_balance,
                'description' => "Refund from cancelled booking #{$booking->id}",
            ]);

            Log::info("Refunded {$points} points to user {$user->id} from cancelled booking {$booking->id}");

            return $transaction;
        });
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display user dashboard with bookings.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $tab = $request->get('tab', 'upcoming');

        // Base query with cursor pagination
        $baseQuery = $user->bookings()
            ->with('lapangan')
            ->orderBy('tanggal', 'desc')
            ->orderBy('jam_mulai', 'desc')
            ->orderBy('id', 'desc'); // Stable sort for cursor pagination

        // Filter by tab
        switch ($tab) {
            case 'upcoming':
                // Tampilkan booking yang akan datang (pending atau confirmed) DAN belum lewat
                $bookings = $baseQuery->whereIn('status', ['pending', 'confirmed'])
                    ->where(function ($q) {
                        // Booking di masa depan
                        $q->whereDate('tanggal', '>', Carbon::today())
                          ->orWhere(function ($q2) {
                              // Atau hari ini tapi belum mulai
                              $q2->whereDate('tanggal', '=', Carbon::today())
                                 ->whereTime('jam_mulai', '>', Carbon::now()->toTimeString());
                          });
                    })
                    ->cursorPaginate(10);
                break;

            case 'past':
                // Tampilkan booking yang sudah lewat ATAU completed
                $bookings = $baseQuery->where(function ($q) {
                        // Status completed
                        $q->where('status', 'completed')
                          // ATAU confirmed tapi sudah lewat waktunya
                          ->orWhere(function ($q2) {
                              $q2->where('status', 'confirmed')
                                 ->where(function ($q3) {
                                     // Tanggal sudah lewat
                                     $q3->whereDate('tanggal', '<', Carbon::today())
                                        // Atau hari ini tapi jam selesai sudah lewat
                                        ->orWhere(function ($q4) {
                                            $q4->whereDate('tanggal', '=', Carbon::today())
                                               ->whereTime('jam_selesai', '<=', Carbon::now()->toTimeString());
                                        });
                                 });
                          });
                    })
                    ->cursorPaginate(10);
                break;

            case 'cancelled':
                $bookings = $baseQuery->where('status', 'cancelled')->cursorPaginate(10);
                break;

            default:
                $bookings = $baseQuery->cursorPaginate(10);
        }

        return view('dashboard.index', compact('user', 'bookings', 'tab'));
    }

    /**
     * Cancel a booking.
     */
    public function cancelBooking(Request $request, $id)
    {
        $booking = Auth::user()->bookings()->findOrFail($id);

        // Only allow cancelling confirmed bookings
        if ($booking->status !== 'confirmed') {
            return back()->with('error', 'Booking tidak dapat dibatalkan');
        }

        // Only allow cancelling future bookings
        $bookingDateTime = Carbon::parse($booking->tanggal . ' ' . $booking->jam_mulai);
        if ($bookingDateTime->isPast()) {
            return back()->with('error', 'Tidak dapat membatalkan booking yang sudah berlalu');
        }

        // Update status
        $booking->update(['status' => 'cancelled']);

        // Refund points if any were redeemed
        if ($booking->points_redeemed > 0) {
            $pointService = app(\App\Services\PointService::class);
            $pointService->refundPoints($booking);
        }

        // Send cancellation notification
        try {
            $booking->notify(new \App\Notifications\BookingCancelled($booking, 'Dibatalkan oleh customer'));
        } catch (\Exception $e) {
            Log::error('Failed to send cancellation notification', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage()
            ]);
        }

        return back()->with('success', 'Booking berhasil dibatalkan');
    }
}


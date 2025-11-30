<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Models\Lapangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class BookingController extends Controller
{
    /**
     * Display a listing of user's bookings.
     */
    public function index(Request $request)
    {
        $query = Booking::where('user_id', $request->user()->id)
            ->with(['lapangan:id,title,category,price,image']) // Eager load with select
            ->select('id', 'lapangan_id', 'user_id', 'tanggal', 'jam_mulai', 'jam_selesai', 
                     'nama_pemesan', 'nomor_telepon', 'email', 'harga', 'payment_method', 
                     'payment_status', 'payment_proof', 'status', 'cancellation_reason', 
                     'cancellation_type', 'refund_amount', 'refund_processed_at', 
                     'points_earned', 'points_redeemed', 'created_at', 'updated_at')
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by payment status
        if ($request->has('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Filter upcoming/past
        if ($request->has('filter')) {
            $now = Carbon::now();
            if ($request->filter === 'upcoming') {
                $query->whereIn('status', ['pending', 'confirmed'])
                    ->where(function ($q) use ($now) {
                        $q->where('tanggal', '>', $now->toDateString())
                          ->orWhere(function ($q2) use ($now) {
                              $q2->where('tanggal', $now->toDateString())
                                 ->where('jam_mulai', '>', $now->toTimeString());
                          });
                    });
            } elseif ($request->filter === 'past') {
                $query->where(function ($q) use ($now) {
                    $q->where('status', 'completed')
                      ->orWhere(function ($q2) use ($now) {
                          $q2->where('status', 'confirmed')
                             ->where(function ($q3) use ($now) {
                                 $q3->where('tanggal', '<', $now->toDateString())
                                    ->orWhere(function ($q4) use ($now) {
                                        $q4->where('tanggal', $now->toDateString())
                                           ->where('jam_selesai', '<=', $now->toTimeString());
                                    });
                             });
                      });
                });
            }
        }

        $perPage = $request->input('per_page', 15);
        $bookings = $query->paginate($perPage);

        return BookingResource::collection($bookings);
    }

    /**
     * Store a newly created booking.
     */
    public function store(Request $request)
    {
        $request->validate([
            'lapangan_id' => 'required|exists:lapangan,id',
            'tanggal' => 'required|date|after_or_equal:today',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'nama_pemesan' => 'nullable|string|max:255',
            'nomor_telepon' => 'nullable|string|max:20',
            'email' => 'nullable|email',
        ]);

        try {
            DB::beginTransaction();

            // Lock lapangan record
            $lapangan = Lapangan::where('id', $request->lapangan_id)
                ->lockForUpdate()
                ->first();

            if (!$lapangan) {
                throw new \Exception('Lapangan not found.');
            }

            // Check if field is operational
            if (!$lapangan->isOperationalOn($request->tanggal)) {
                throw new \Exception('Field is not operational on this date.');
            }

            // Check for conflicts
            $conflictBooking = Booking::where('lapangan_id', $request->lapangan_id)
                ->where('tanggal', $request->tanggal)
                ->whereIn('status', ['pending', 'confirmed'])
                ->where(function ($query) use ($request) {
                    $query->where(function ($q) use ($request) {
                        $q->where('jam_mulai', '<=', $request->jam_mulai)
                          ->where('jam_selesai', '>', $request->jam_mulai);
                    })->orWhere(function ($q) use ($request) {
                        $q->where('jam_mulai', '<', $request->jam_selesai)
                          ->where('jam_selesai', '>=', $request->jam_selesai);
                    })->orWhere(function ($q) use ($request) {
                        $q->where('jam_mulai', '>=', $request->jam_mulai)
                          ->where('jam_selesai', '<=', $request->jam_selesai);
                    });
                })
                ->lockForUpdate()
                ->first();

            if ($conflictBooking) {
                throw new \Exception('Time slot is already booked.');
            }

            // Calculate dynamic price
            $priceData = $lapangan->calculatePrice(
                $request->tanggal,
                $request->jam_mulai,
                $request->jam_selesai
            );

            // Calculate points (1% but don't add yet)
            $pointsEarned = floor($priceData['total_price'] * 0.01);

            // Create booking
            $booking = Booking::create([
                'lapangan_id' => $request->lapangan_id,
                'user_id' => $request->user()->id,
                'tanggal' => $request->tanggal,
                'jam_mulai' => $request->jam_mulai,
                'jam_selesai' => $request->jam_selesai,
                'nama_pemesan' => $request->nama_pemesan ?? $request->user()->name,
                'nomor_telepon' => $request->nomor_telepon ?? $request->user()->phone,
                'email' => $request->email ?? $request->user()->email,
                'harga' => $priceData['total_price'],
                'points_earned' => $pointsEarned,
                'payment_status' => 'unpaid',
                'status' => 'pending',
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Booking created successfully',
                'booking' => new BookingResource($booking->load('lapangan')),
                'price_details' => $priceData,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('API Booking Error: ' . $e->getMessage(), [
                'lapangan_id' => $request->lapangan_id,
                'user_id' => $request->user()->id,
            ]);

            return response()->json([
                'message' => 'Failed to create booking',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Display the specified booking.
     */
    public function show(Request $request, $id)
    {
        $booking = Booking::where('user_id', $request->user()->id)
            ->with(['lapangan:id,title,category,price,image'])
            ->findOrFail($id);

        return new BookingResource($booking);
    }

    /**
     * Upload payment proof.
     */
    public function uploadPaymentProof(Request $request, $id)
    {
        $booking = Booking::where('user_id', $request->user()->id)
            ->where('payment_status', 'unpaid')
            ->findOrFail($id);

        $request->validate([
            'payment_method' => 'required|in:Bank Transfer,QRIS,E-Wallet',
            'payment_proof' => 'required|image|max:2048',
            'payment_notes' => 'nullable|string|max:500',
        ]);

        if ($request->hasFile('payment_proof')) {
            // Delete old proof if exists
            if ($booking->payment_proof && Storage::disk('public')->exists($booking->payment_proof)) {
                Storage::disk('public')->delete($booking->payment_proof);
            }

            $path = $request->file('payment_proof')->store('payment-proofs', 'public');

            $booking->update([
                'payment_method' => $request->payment_method,
                'payment_proof' => $path,
                'payment_notes' => $request->payment_notes,
                'payment_status' => 'waiting_confirmation',
                'payment_submitted_at' => now(),
            ]);
        }

        return response()->json([
            'message' => 'Payment proof uploaded successfully',
            'booking' => new BookingResource($booking->load('lapangan')),
        ]);
    }

    /**
     * Cancel booking.
     */
    public function cancel(Request $request, $id)
    {
        $booking = Booking::where('user_id', $request->user()->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->findOrFail($id);

        $request->validate([
            'cancellation_reason' => 'required|string|max:500',
        ]);

        // Calculate refund
        $bookingDateTime = Carbon::parse($booking->tanggal . ' ' . $booking->jam_mulai);
        $hoursUntilBooking = now()->diffInHours($bookingDateTime, false);

        if ($hoursUntilBooking < 0) {
            return response()->json([
                'message' => 'Cannot cancel past bookings',
            ], 400);
        }

        // H-24 or more: 100% refund
        // Less than H-24: 50% refund
        $refundPercentage = $hoursUntilBooking >= 24 ? 100 : 50;
        $refundAmount = ($booking->harga * $refundPercentage) / 100;

        $booking->update([
            'status' => 'cancelled',
            'cancellation_reason' => $request->cancellation_reason,
            'cancellation_type' => 'user',
            'refund_amount' => $refundAmount,
            'cancelled_at' => now(),
        ]);

        return response()->json([
            'message' => 'Booking cancelled successfully',
            'booking' => new BookingResource($booking->load('lapangan')),
            'refund_info' => [
                'percentage' => $refundPercentage,
                'amount' => $refundAmount,
                'hours_until_booking' => $hoursUntilBooking,
            ],
        ]);
    }
}

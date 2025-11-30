<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\LapanganResource;
use App\Models\Lapangan;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class LapanganController extends Controller
{
    /**
     * Display a listing of active lapangan.
     */
    public function index(Request $request)
    {
        // Cache key based on query parameters
        $cacheKey = 'api_lapangan_' . md5(json_encode($request->all()));
        
        $lapangan = Cache::remember($cacheKey, 300, function () use ($request) {
            $query = Lapangan::where('status', 1)
                ->select('id', 'title', 'category', 'description', 'price', 'weekday_price', 
                         'weekend_price', 'peak_hour_start', 'peak_hour_end', 'peak_hour_multiplier',
                         'image', 'status', 'jam_buka', 'jam_tutup', 'hari_operasional', 
                         'is_maintenance', 'maintenance_start', 'maintenance_end', 'maintenance_reason',
                         'created_at', 'updated_at');

            // Filter by category
            if ($request->has('category')) {
                $query->where('category', $request->category);
            }

            // Search by title
            if ($request->has('search')) {
                $query->where('title', 'like', '%' . $request->search . '%');
            }

            // Sort by price
            if ($request->has('sort_by') && $request->sort_by === 'price') {
                $direction = $request->input('sort_direction', 'asc');
                $query->orderBy('price', $direction);
            }

            $perPage = $request->input('per_page', 15);
            return $query->paginate($perPage);
        });
        
        return LapanganResource::collection($lapangan);
    }

    /**
     * Display the specified lapangan with details.
     */
    public function show($id)
    {
        $lapangan = Cache::remember("api_lapangan_detail_{$id}", 600, function () use ($id) {
            return Lapangan::where('status', 1)->findOrFail($id);
        });
        
        return new LapanganResource($lapangan);
    }

    /**
     * Get available time slots for a specific date.
     */
    public function availableSlots(Request $request, $id)
    {
        $request->validate([
            'date' => 'required|date|after_or_equal:today',
        ]);

        $date = $request->date;
        
        // Cache slots for 2 minutes (short cache for real-time availability)
        $cacheKey = "api_slots_{$id}_{$date}";
        
        $result = Cache::remember($cacheKey, 120, function () use ($id, $date) {
            $lapangan = Lapangan::where('status', 1)->findOrFail($id);

            // Check if field is operational on selected date
            if (!$lapangan->isOperationalOn($date)) {
                $maintenanceInfo = $lapangan->getMaintenanceInfo();
                return [
                    'error' => true,
                    'message' => 'Field is not operational on this date',
                    'reason' => $maintenanceInfo ? $maintenanceInfo['reason'] : 'Not operational',
                ];
            }

            // Get operational hours
            $hours = $lapangan->getOperationalHours();
            $startHour = (int) substr($hours['jam_buka'], 0, 2);
            $endHour = (int) substr($hours['jam_tutup'], 0, 2);

            // Get booked slots with select optimization
            $bookedSlots = Booking::where('lapangan_id', $id)
                ->where('tanggal', $date)
                ->whereIn('status', ['pending', 'confirmed'])
                ->select('jam_mulai', 'jam_selesai')
                ->get();

            $availableSlots = [];
            for ($hour = $startHour; $hour < $endHour; $hour++) {
                $timeStart = sprintf('%02d:00', $hour);
                $timeEnd = sprintf('%02d:00', $hour + 1);

                // Check if booked
                $isBooked = $bookedSlots->contains(function ($booking) use ($timeStart) {
                    return $booking->jam_mulai <= $timeStart && $booking->jam_selesai > $timeStart;
                });

                // Calculate dynamic price
                $priceData = $lapangan->calculatePrice($date, $timeStart, $timeEnd);

                $availableSlots[] = [
                    'start' => $timeStart,
                    'end' => $timeEnd,
                    'is_available' => !$isBooked,
                    'price' => $priceData['total_price'],
                    'is_weekend' => $priceData['is_weekend'],
                    'is_peak_hour' => $priceData['is_peak_hour'],
                    'peak_multiplier' => $priceData['peak_multiplier'],
                ];
            }

            return [
                'error' => false,
                'lapangan' => $lapangan,
                'slots' => $availableSlots,
            ];
        });
        
        if ($result['error']) {
            return response()->json([
                'message' => $result['message'],
                'reason' => $result['reason'],
                'available_slots' => [],
            ], 400);
        }

        return response()->json([
            'date' => $date,
            'lapangan' => new LapanganResource($result['lapangan']),
            'slots' => $result['slots'],
        ]);
    }

    /**
     * Get price calculation for specific time range.
     */
    public function calculatePrice(Request $request, $id)
    {
        $request->validate([
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        $lapangan = Lapangan::findOrFail($id);
        $priceData = $lapangan->calculatePrice(
            $request->date,
            $request->start_time,
            $request->end_time
        );

        return response()->json($priceData);
    }
}

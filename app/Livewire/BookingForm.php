<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Lapangan;
use App\Models\Setting;
use Carbon\Carbon;
use App\Models\Booking;
use App\Notifications\BookingConfirmed;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Services\PointService;

class BookingForm extends Component
{
    public $lapangan;
    public $selectedDate;
    public $selectedTimeSlot;
    public $nama_pemesan;
    public $no_telepon;
    public $email;
    public $usePoints = false;
    public $pointsToRedeem = 0;

    public $availableDates = [];
    public $availableTimeSlots = [];
    public $bookedSlots = [];
    public $operationalHours = [];
    public $totalPrice = 0;
    public $discount = 0;
    public $finalPrice = 0;
    public $userPointsBalance = 0;
    public $maxRedeemablePoints = 0;

    protected $rules = [
        'nama_pemesan' => 'required|string|min:3',
        'no_telepon' => 'required|string|min:8',
        'email' => 'required|email',
        'selectedDate' => 'required|date_format:Y-m-d',
        'selectedTimeSlot' => 'required|string',
        'pointsToRedeem' => 'nullable|integer|min:0',
    ];

    public function mount($lapanganId)
    {
        $this->lapangan = Lapangan::findOrFail($lapanganId);
        
        // Auto-fill for authenticated users
        if (Auth::check()) {
            $user = Auth::user();
            $this->nama_pemesan = $user->name;
            $this->no_telepon = $user->phone ?? '';
            $this->email = $user->email;
            $this->userPointsBalance = $user->points_balance;
        } else {
            // For guests, disable form fields (view only mode)
            $this->nama_pemesan = '';
            $this->no_telepon = '';
            $this->email = '';
            $this->userPointsBalance = 0;
        }

        $this->loadOperationalHours();

        $this->generateAvailableDates();
    }

    public function loadOperationalHours()
    {
        $jamBuka = Setting::where('key', 'jam_buka')->first();
    $jamTutup = Setting::where('key', 'jam_tutup')->first();

        $this->operationalHours = [
            'jam_buka' => $jamBuka ? $jamBuka->value : '06:00',
            'jam_tutup' => $jamTutup ? $jamTutup->value : '21:00',
        ];
    }

    public function generateAvailableDates()
    {
        $dates = [];
        $today = Carbon::now();

        for($i = 0; $i <7; $i++){
            $date = $today->copy()->addDays($i);
            $dates[] = [
                'date' => $date->format('Y-m-d'),
                'day' => $this->getDayName($date->dayOfWeek),
                'formatted' => $date->format('d M'),
                'full_date' => $date
            ];
        }

        $this->availableDates = $dates;
    }

    public function getDayName($dayOfWeek)
    {
        $days =
        [0 => 'Min',
        1 => 'Sen',
        2 => 'Sel',
        3 => 'Rab',
        4 => 'Kam',
        5 => 'Jum',
        6 => 'Sab'
    ];
        return $days[$dayOfWeek];
    }

    public function selectDate($date)
    {
        $this->selectedDate = $date;
        $this->selectedTimeSlot = null;
        
        // CRITICAL: Force refresh time slots with current time
        $this->updateAvailableTimeSlots();
    }

    public function selectTimeSlot($timeSlot)
    {
        $this->selectedTimeSlot = $timeSlot;
        $this->calculatePrice();
        $this->calculateMaxRedeemablePoints();
    }
    
    /**
     * Refresh time slot availability (called by wire:poll)
     * This ensures past slots are marked as unavailable in real-time
     */
    public function refreshAvailability()
    {
        if ($this->selectedDate) {
            $this->updateAvailableTimeSlots();
        }
    }

    public function calculatePrice()
    {
        $this->totalPrice = $this->lapangan->price;
        $this->calculateDiscount();
    }

    public function calculateDiscount()
    {
        if ($this->usePoints && $this->pointsToRedeem > 0) {
            $pointService = app(PointService::class);
            $this->discount = $pointService->calculateDiscount($this->pointsToRedeem);
        } else {
            $this->discount = 0;
        }
        
        $this->finalPrice = max(0, $this->totalPrice - $this->discount);
    }

    public function calculateMaxRedeemablePoints()
    {
        if (Auth::check()) {
            // Max 50% of price can be paid with points
            $maxDiscount = (int) floor($this->lapangan->price * 0.5);
            // Convert max discount to points (100 points = Rp 1000)
            $this->maxRedeemablePoints = min(
                (int) floor($maxDiscount / 1000 * 100),
                $this->userPointsBalance
            );
        }
    }

    public function updatedUsePoints()
    {
        if (!$this->usePoints) {
            $this->pointsToRedeem = 0;
        }
        $this->calculateDiscount();
    }

    public function updatedPointsToRedeem()
    {
        if ($this->pointsToRedeem > $this->maxRedeemablePoints) {
            $this->pointsToRedeem = $this->maxRedeemablePoints;
        }
        if ($this->pointsToRedeem < 0) {
            $this->pointsToRedeem = 0;
        }
        $this->calculateDiscount();
    }

    public function updateAvailableTimeSlots()
    {
        $this->availableTimeSlots = [];
        $this->bookedSlots = [];

        if (!$this->selectedDate) {
            return;
        }
        
        $this->bookedSlots = Booking::where('lapangan_id', $this->lapangan->id)
            ->where('tanggal', $this->selectedDate)
            ->where('status', '!=', 'cancelled')
            ->get()
            ->map(function($booking) {
                return [
                    'jam_mulai' => Carbon::parse($booking->jam_mulai)->format('H:i'),
                    'jam_selesai' => Carbon::parse($booking->jam_selesai)->format('H:i'),
                ];
            })->toArray();

        $this->generateTimeSlots();
    }

    public function generateTimeSlots()
    {
        $jamBuka = Carbon::parse($this->operationalHours['jam_buka']);
        $jamTutup = Carbon::parse($this->operationalHours['jam_tutup']);
        $timeSlots = [];
        
        // CRITICAL: Always get fresh current time
        $now = Carbon::now();
        $selectedDate = Carbon::parse($this->selectedDate)->startOfDay();
        $isToday = $selectedDate->isToday();
        
        // RULE: User must book at least 30 minutes before the slot starts
        $minimumBookingTime = $now->copy()->addMinutes(30);

        while($jamBuka->lt($jamTutup)) {
            $jamMulai = $jamBuka->format('H:i');
            $jamSelesai = $jamBuka->copy()->addHour()->format('H:i');
            $isBooked = $this->isSlotBooked($jamMulai, $jamSelesai);
            
            // Check if time slot has passed or within 30-minute buffer (only for today)
            $isPast = false;
            if ($isToday) {
                // Build full datetime for comparison
                $slotStartTime = Carbon::createFromFormat('Y-m-d H:i', $selectedDate->format('Y-m-d') . ' ' . $jamMulai);
                
                // Slot is unavailable if:
                // 1. It has already started (slotStartTime <= now)
                // 2. It's within 30 minutes from now (slotStartTime < minimumBookingTime)
                $isPast = $slotStartTime->lt($minimumBookingTime);
            }

            $timeSlots[] = [
                'jam_mulai' => $jamMulai,
                'jam_selesai' => $jamSelesai,
                'label' => $jamMulai . ' - ' . $jamSelesai,
                'price' => $this->lapangan->price,
                'is_booked' => $isBooked || $isPast, // Mark as booked if past or within buffer
                'is_past' => $isPast,
                'slot_key' => $jamMulai . '-' . $jamSelesai,
            ];

            $jamBuka->addHour();
        }
        
        $this->availableTimeSlots = $timeSlots;
    }

    public function isSlotBooked($jamMulai, $jamSelesai)
    {
        foreach($this->bookedSlots as $booked) {
            $bookingStart = Carbon::parse($booked['jam_mulai']);
            $bookingEnd = Carbon::parse($booked['jam_selesai']);
            $slotStart = Carbon::parse($jamMulai);
            $slotEnd = Carbon::parse($jamSelesai);
            if ($slotStart->lt($bookingEnd) && $slotEnd->gt($bookingStart)) {
                return true;
            }
        }
        return false;
    }

    public function submit()
    {
        // Validate inputs
        $this->validate();

        // Parse selected time slot (e.g., "08:00-09:00")
        if (!str_contains($this->selectedTimeSlot, '-')) {
            $this->addError('selectedTimeSlot', 'Format jam tidak valid.');
            return;
        }
        [$jamMulai, $jamSelesai] = explode('-', $this->selectedTimeSlot, 2);
        $jamMulai = trim($jamMulai);
        $jamSelesai = trim($jamSelesai);
        
        // CRITICAL: Enforce 30-minute buffer for same-day bookings
        $selectedDate = Carbon::parse($this->selectedDate)->startOfDay();
        $slotStartTime = Carbon::createFromFormat('Y-m-d H:i', $selectedDate->format('Y-m-d') . ' ' . $jamMulai);
        $minimumBookingTime = Carbon::now()->addMinutes(30);
        
        if ($slotStartTime->lt($minimumBookingTime)) {
            $this->addError('selectedTimeSlot', 'Booking harus dilakukan minimal 30 menit sebelum waktu main. Silakan pilih waktu yang lain.');
            $this->updateAvailableTimeSlots();
            return;
        }

        try {
            DB::beginTransaction();

            // RACE CONDITION PROTECTION: Lock lapangan and check availability
            $lapangan = Lapangan::where('id', $this->lapangan->id)->lockForUpdate()->first();
            
            if (!$lapangan) {
                throw new \Exception('Lapangan tidak ditemukan.');
            }

            // Prevent double booking with pessimistic lock (overlapping check)
            $exists = Booking::where('lapangan_id', $this->lapangan->id)
                ->where('tanggal', $this->selectedDate)
                ->where('status', '!=', 'cancelled')
                ->lockForUpdate() // CRITICAL: Lock to prevent race condition
                ->where(function ($q) use ($jamMulai, $jamSelesai) {
                    // Check all possible overlap scenarios
                    $q->where(function ($qq) use ($jamMulai, $jamSelesai) {
                        // New booking starts during existing booking
                        $qq->where('jam_mulai', '<=', $jamMulai)
                           ->where('jam_selesai', '>', $jamMulai);
                    })
                    ->orWhere(function ($qq) use ($jamMulai, $jamSelesai) {
                        // New booking ends during existing booking
                        $qq->where('jam_mulai', '<', $jamSelesai)
                           ->where('jam_selesai', '>=', $jamSelesai);
                    })
                    ->orWhere(function ($qq) use ($jamMulai, $jamSelesai) {
                        // New booking completely contains existing booking
                        $qq->where('jam_mulai', '>=', $jamMulai)
                           ->where('jam_selesai', '<=', $jamSelesai);
                    });
                })
                ->exists();

            if ($exists) {
                DB::rollBack();
                $this->addError('selectedTimeSlot', 'Slot sudah dibooking. Silakan pilih jam lain.');
                $this->updateAvailableTimeSlots();
                return;
            }

            // Create booking
            Booking::create([
                'lapangan_id' => $this->lapangan->id,
                'tanggal' => $this->selectedDate,
                'jam_mulai' => $jamMulai,
                'jam_selesai' => $jamSelesai,
                'nama_pemesan' => $this->nama_pemesan,
                'nomor_telepon' => $this->no_telepon,
                'status' => 'confirmed',
            ]);

            DB::commit();

            // Refresh slots and reset selection
            $this->updateAvailableTimeSlots();
            $this->selectedTimeSlot = null;
            session()->flash('success', 'Booking berhasil dibuat.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Booking failed', ['error' => $e->getMessage()]);
            session()->flash('error', 'Booking gagal. Silakan coba lagi.');
        }
    }

    public function submitBooking()
    {
        // CRITICAL: Require authentication
        if (!Auth::check()) {
            session()->flash('error', 'Silakan login terlebih dahulu untuk melakukan booking.');
            return redirect()->route('login');
        }

        // CRITICAL: Require email verification
        if (!Auth::user()->hasVerifiedEmail()) {
            session()->flash('error', 'Silakan verifikasi email Anda terlebih dahulu untuk melakukan booking.');
            return redirect()->route('verification.notice');
        }

        $this->validate([
            'selectedDate' => 'required|date',
            'selectedTimeSlot' => 'required|string',
            'nama_pemesan' => 'required|string|max:255',
            'no_telepon' => 'required|string|max:20',
            'email' => 'required|email',
            'pointsToRedeem' => 'nullable|integer|min:0',
        ]);

        if (!$this->selectedDate || !$this->selectedTimeSlot) {
            session()->flash('error', 'Silahkan pilih tanggal dan jam terlebih dahulu.');
            return;
        }

        $timeSlot = collect($this->availableTimeSlots)->firstWhere('slot_key', $this->selectedTimeSlot);

        if (!$timeSlot || $timeSlot['is_booked']) {
            session()->flash('error', 'Slot waktu yang dipilih tidak tersedia. Silakan pilih slot lain.');
            $this->updateAvailableTimeSlots();
            return;
        }
        
        // CRITICAL: Double-check if time has passed (real-time validation)
        $selectedDateTime = Carbon::parse($this->selectedDate . ' ' . $timeSlot['jam_mulai']);
        if ($selectedDateTime->lte(Carbon::now())) {
            session()->flash('error', 'Waktu booking sudah lewat. Silakan pilih waktu yang lain.');
            $this->updateAvailableTimeSlots();
            return redirect()->route('detail', $this->lapangan->id);
        }

        // Validate points redemption for authenticated users
        if (Auth::check() && $this->usePoints && $this->pointsToRedeem > 0) {
            if ($this->pointsToRedeem > Auth::user()->points_balance) {
                session()->flash('error', 'Poin tidak mencukupi.');
                return;
            }
            if ($this->pointsToRedeem > $this->maxRedeemablePoints) {
                session()->flash('error', 'Maksimal poin yang dapat digunakan: ' . $this->maxRedeemablePoints);
                return;
            }
        }

        try {
            DB::beginTransaction();

            // CRITICAL: Re-check slot availability with pessimistic lock to prevent race condition
            // Lock the lapangan record to prevent concurrent bookings
            $lapangan = Lapangan::where('id', $this->lapangan->id)->lockForUpdate()->first();
            
            if (!$lapangan) {
                throw new \Exception('Lapangan tidak ditemukan.');
            }

            // Double-check if slot is still available (race condition protection)
            $conflictExists = Booking::where('lapangan_id', $this->lapangan->id)
                ->where('tanggal', $this->selectedDate)
                ->where('status', '!=', 'cancelled')
                ->lockForUpdate() // Lock existing bookings for this check
                ->where(function ($q) use ($timeSlot) {
                    $jamMulai = $timeSlot['jam_mulai'];
                    $jamSelesai = $timeSlot['jam_selesai'];
                    
                    // Check for any time overlap
                    $q->where(function ($qq) use ($jamMulai, $jamSelesai) {
                        // New booking starts during existing booking
                        $qq->where('jam_mulai', '<=', $jamMulai)
                           ->where('jam_selesai', '>', $jamMulai);
                    })
                    ->orWhere(function ($qq) use ($jamMulai, $jamSelesai) {
                        // New booking ends during existing booking
                        $qq->where('jam_mulai', '<', $jamSelesai)
                           ->where('jam_selesai', '>=', $jamSelesai);
                    })
                    ->orWhere(function ($qq) use ($jamMulai, $jamSelesai) {
                        // New booking completely contains existing booking
                        $qq->where('jam_mulai', '>=', $jamMulai)
                           ->where('jam_selesai', '<=', $jamSelesai);
                    });
                })
                ->exists();

            if ($conflictExists) {
                DB::rollBack();
                session()->flash('error', 'Maaf, slot ini baru saja dibooking oleh orang lain. Silakan pilih slot lain.');
                $this->updateAvailableTimeSlots();
                return redirect()->route('detail', $this->lapangan->id);
            }

            $booking = Booking::create([
                'user_id' => Auth::id(),
                'lapangan_id' => $this->lapangan->id,
                'tanggal' => $this->selectedDate,
                'jam_mulai' => $timeSlot['jam_mulai'],
                'jam_selesai' => $timeSlot['jam_selesai'],
                'nama_pemesan' => $this->nama_pemesan,
                'nomor_telepon' => $this->no_telepon,
                'email' => $this->email,
                'status' => 'confirmed',
            ]);

            // Handle point redemption
            if (Auth::check() && $this->usePoints && $this->pointsToRedeem > 0) {
                $pointService = app(PointService::class);
                $pointService->redeemPoints(Auth::user(), $booking, $this->pointsToRedeem);
            }

            // Award points for this booking (1% of price)
            if (Auth::check()) {
                $pointService = app(PointService::class);
                $pointsEarned = $pointService->calculateEarnedPoints($this->lapangan->price);
                $pointService->awardPoints(Auth::user(), $booking, $pointsEarned);
            }

            // Send notification
            try {
                $booking->notify(new BookingConfirmed($booking));
                Log::info('Booking notification queued', ['booking_id' => $booking->id]);
            } catch (\Exception $e) {
                Log::error('Failed to queue booking notification', [
                    'booking_id' => $booking->id,
                    'error' => $e->getMessage()
                ]);
                // Don't fail the booking if notification fails
            }

            DB::commit();
            session()->flash('success', 'Pemesanan berhasil! Kode Booking: ' . $booking->booking_code . '. Notifikasi konfirmasi akan dikirim ke email dan WhatsApp Anda.');

            $this->reset(['nama_pemesan', 'no_telepon', 'email', 'selectedTimeSlot', 'totalPrice']);
            $this->no_telepon = '62'; // Reset to default country code
            $this->availableTimeSlots = [];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Booking failed', ['error' => $e->getMessage()]);
            session()->flash('error', 'Pemesanan gagal, silakan coba lagi.');
        }
    }

    public function render()
    {
        return view('livewire.booking-form');
    }
}

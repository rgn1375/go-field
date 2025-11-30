<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Lapangan;
use App\Models\Booking;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Services\SettingsService;
use App\Services\PointService;
use App\Services\BookingValidationService;
use App\Notifications\BookingConfirmed;

class BookingFormProduction extends Component
{
    // Lapangan data
    public $lapanganId;
    public $lapangan;

    // Form fields
    public $selectedDate;
    public $jamMulai;
    public $jamSelesai;
    public $namaPemesan = '';
    public $email = '';
    public $noTelepon = '';

    // Points and discounts
    public $usePoints = false;
    public $pointsToUse = 0;
    public $availablePoints = 0;
    public $discountAmount = 0;

    // UI state
    public $availableDates = [];
    public $timeSlots = [];
    public $totalPrice = 0;
    public $originalPrice = 0;

    // Validation rules
    protected $rules = [
        'selectedDate' => 'required|date',
        'jamMulai' => 'required|date_format:H:i',
        'jamSelesai' => 'required|date_format:H:i|after:jamMulai',
        'namaPemesan' => 'required|string|min:3|max:255',
        'email' => 'required|email|max:255',
        'noTelepon' => 'required|string|min:10|max:15',
    ];

    protected $messages = [
        'selectedDate.required' => 'Silakan pilih tanggal booking.',
        'jamMulai.required' => 'Silakan pilih jam mulai.',
        'jamSelesai.required' => 'Silakan pilih jam selesai.',
        'jamSelesai.after' => 'Jam selesai harus setelah jam mulai.',
        'namaPemesan.required' => 'Nama pemesan wajib diisi.',
        'namaPemesan.min' => 'Nama pemesan minimal 3 karakter.',
        'email.required' => 'Email wajib diisi.',
        'email.email' => 'Format email tidak valid.',
        'noTelepon.required' => 'Nomor telepon wajib diisi.',
        'noTelepon.min' => 'Nomor telepon minimal 10 digit.',
    ];

    /**
     * Component initialization
     */
    public function mount($lapanganId)
    {
        $this->lapanganId = $lapanganId;
        $this->lapangan = Lapangan::findOrFail($lapanganId);
        
        // Auto-fill for authenticated users
        if (Auth::check()) {
            $user = Auth::user();
            $this->namaPemesan = $user->name;
            $this->email = $user->email;
            $this->noTelepon = $user->phone ?? '';
            $this->availablePoints = $user->points_balance ?? 0;
        }
        
        // Generate available dates (next 30 days)
        $this->generateAvailableDates();
    }

    /**
     * Generate available booking dates (next 30 days)
     */
    public function generateAvailableDates()
    {
        $this->availableDates = [];
        $maxDays = BookingValidationService::MAXIMUM_BOOKING_DAYS_ADVANCE;
        
        for ($i = 0; $i <= $maxDays; $i++) {
            $date = Carbon::today()->addDays($i);
            
            // Check if lapangan is operational on this date
            if ($this->lapangan->isOperationalOn($date->format('Y-m-d'))) {
                $this->availableDates[] = [
                    'value' => $date->format('Y-m-d'),
                    'display' => $date->locale('id')->isoFormat('dddd, D MMMM YYYY'),
                    'is_weekend' => in_array($date->dayOfWeekIso, [6, 7]),
                ];
            }
        }
    }

    /**
     * Handle date selection
     */
    public function selectDate($date)
    {
        $this->selectedDate = $date;
        $this->jamMulai = null;
        $this->jamSelesai = null;
        $this->totalPrice = 0;
        $this->originalPrice = 0;
        
        // Generate time slots for selected date
        $this->generateTimeSlots();
    }

    /**
     * Generate time slots with comprehensive validation
     */
    public function generateTimeSlots()
    {
        if (!$this->selectedDate) {
            return;
        }

        $this->timeSlots = [];
        $hours = $this->lapangan->getOperationalHours();
        $jamBuka = $hours['jam_buka'];
        $jamTutup = $hours['jam_tutup'];

        // Parse operational hours
        $openTime = Carbon::createFromFormat('H:i', substr($jamBuka, 0, 5));
        $closeTime = Carbon::createFromFormat('H:i', substr($jamTutup, 0, 5));

        // Current time for comparison
        $now = Carbon::now();
        $minimumBookingTime = $now->copy()->addMinutes(
            BookingValidationService::MINIMUM_BOOKING_BUFFER_MINUTES
        );

        // Get all bookings for this date and lapangan
        $existingBookings = Booking::where('lapangan_id', $this->lapanganId)
            ->where('tanggal', $this->selectedDate)
            ->whereIn('status', ['pending', 'confirmed'])
            ->get(['jam_mulai', 'jam_selesai']);

        // Generate hourly slots
        $currentSlot = $openTime->copy();
        while ($currentSlot->lt($closeTime)) {
            $slotStart = $currentSlot->format('H:i');
            $slotEnd = $currentSlot->copy()->addHour()->format('H:i');

            // Check if slot is in the past (for today's bookings)
            $slotStartDateTime = Carbon::createFromFormat(
                'Y-m-d H:i',
                $this->selectedDate . ' ' . $slotStart
            );
            
            $isPast = $slotStartDateTime->lt($minimumBookingTime);

            // Check if slot is already booked
            $isBooked = $existingBookings->contains(function ($booking) use ($slotStart, $slotEnd) {
                return (
                    // Exact match
                    ($booking->jam_mulai === $slotStart && $booking->jam_selesai === $slotEnd) ||
                    // Slot starts during existing booking
                    ($booking->jam_mulai <= $slotStart && $booking->jam_selesai > $slotStart) ||
                    // Slot ends during existing booking
                    ($booking->jam_mulai < $slotEnd && $booking->jam_selesai >= $slotEnd) ||
                    // Slot contains existing booking
                    ($booking->jam_mulai >= $slotStart && $booking->jam_selesai <= $slotEnd)
                );
            });

            $this->timeSlots[] = [
                'start' => $slotStart,
                'end' => $slotEnd,
                'display' => $slotStart . ' - ' . $slotEnd,
                'is_past' => $isPast,
                'is_booked' => $isBooked,
                'is_available' => !$isPast && !$isBooked,
            ];

            $currentSlot->addHour();
        }
    }

    /**
     * Select time slot
     */
    public function selectTimeSlot($start, $end)
    {
        // Re-validate slot hasn't been booked by someone else
        $validationService = app(BookingValidationService::class);
        
        if (!$validationService->isSlotAvailable(
            $this->lapanganId,
            $this->selectedDate,
            $start,
            $end
        )) {
            session()->flash('error', 'Slot ini sudah dibooking oleh pengguna lain. Silakan pilih slot lain.');
            $this->generateTimeSlots(); // Refresh slots
            return;
        }

        $this->jamMulai = $start;
        $this->jamSelesai = $end;
        
        // Calculate price
        $this->calculatePrice();
        
        // Log slot selection
        Log::info('Time slot selected', [
            'lapangan_id' => $this->lapanganId,
            'date' => $this->selectedDate,
            'start' => $start,
            'end' => $end,
            'user_id' => Auth::id(),
        ]);
    }

    /**
     * Calculate booking price with dynamic pricing
     */
    public function calculatePrice()
    {
        if (!$this->selectedDate || !$this->jamMulai || !$this->jamSelesai) {
            return;
        }

        $priceData = $this->lapangan->calculatePrice(
            $this->selectedDate,
            $this->jamMulai,
            $this->jamSelesai
        );

        $this->originalPrice = $priceData['total_price'];
        $this->totalPrice = $priceData['total_price'];

        // Apply discount if points are being used
        if ($this->usePoints && $this->pointsToUse > 0) {
            $this->applyPointsDiscount();
        }
    }

    /**
     * Handle points toggle
     */
    public function updatedUsePoints()
    {
        if ($this->usePoints) {
            // Calculate maximum points that can be used (50% of price)
            $maxPointValue = floor($this->originalPrice * 0.5);
            $maxPoints = min($maxPointValue * 100, $this->availablePoints); // 100 points = Rp 1,000
            
            $this->pointsToUse = $maxPoints;
            $this->applyPointsDiscount();
        } else {
            $this->pointsToUse = 0;
            $this->discountAmount = 0;
            $this->totalPrice = $this->originalPrice;
        }
    }

    /**
     * Handle points input update
     */
    public function updatedPointsToUse()
    {
        // Validate points
        $maxPointValue = floor($this->originalPrice * 0.5);
        $maxPoints = min($maxPointValue * 100, $this->availablePoints);
        
        if ($this->pointsToUse > $maxPoints) {
            $this->pointsToUse = $maxPoints;
        }
        
        if ($this->pointsToUse < 0) {
            $this->pointsToUse = 0;
        }
        
        $this->applyPointsDiscount();
    }

    /**
     * Apply points discount
     */
    private function applyPointsDiscount()
    {
        if ($this->pointsToUse <= 0) {
            $this->discountAmount = 0;
            $this->totalPrice = $this->originalPrice;
            return;
        }

        // 100 points = Rp 1,000
        $pointService = app(PointService::class);
        $this->discountAmount = $pointService->pointsToRupiah($this->pointsToUse);
        $this->totalPrice = max(0, $this->originalPrice - $this->discountAmount);
    }

    /**
     * Refresh slot availability (called by wire:poll)
     */
    public function refreshAvailability()
    {
        $this->generateTimeSlots();
    }

    /**
     * Submit booking with comprehensive validation and transaction safety
     */
    public function submitBooking()
    {
        // Validate form inputs
        $this->validate();

        // Additional validation checks
        if (!$this->selectedDate || !$this->jamMulai || !$this->jamSelesai) {
            session()->flash('error', 'Silakan pilih tanggal dan waktu booking.');
            return;
        }

        // Server-side comprehensive validation
        $validationService = app(BookingValidationService::class);
        $validation = $validationService->validateBookingRequest(
            $this->lapangan,
            $this->selectedDate,
            $this->jamMulai,
            $this->jamSelesai
        );

        if (!$validation['valid']) {
            session()->flash('error', $validation['error']);
            Log::warning('Booking validation failed', [
                'user_id' => Auth::id(),
                'lapangan_id' => $this->lapanganId,
                'date' => $this->selectedDate,
                'time' => $this->jamMulai . ' - ' . $this->jamSelesai,
                'error' => $validation['error'],
                'details' => $validation['details'] ?? null,
            ]);
            $this->generateTimeSlots(); // Refresh slots
            return;
        }

        try {
            DB::beginTransaction();

            // Pessimistic locking: Lock lapangan and check for conflicts
            $lapangan = Lapangan::lockForUpdate()->findOrFail($this->lapanganId);

            // Double-check no conflicts (race condition protection)
            $conflictCheck = Booking::where('lapangan_id', $this->lapanganId)
                ->where('tanggal', $this->selectedDate)
                ->whereIn('status', ['pending', 'confirmed'])
                ->where(function ($q) {
                    $q->where(function ($qq) {
                        $qq->where('jam_mulai', '=', $this->jamMulai)
                           ->where('jam_selesai', '=', $this->jamSelesai);
                    })
                    ->orWhere(function ($qq) {
                        $qq->where('jam_mulai', '<=', $this->jamMulai)
                           ->where('jam_selesai', '>', $this->jamMulai);
                    })
                    ->orWhere(function ($qq) {
                        $qq->where('jam_mulai', '<', $this->jamSelesai)
                           ->where('jam_selesai', '>=', $this->jamSelesai);
                    })
                    ->orWhere(function ($qq) {
                        $qq->where('jam_mulai', '>=', $this->jamMulai)
                           ->where('jam_selesai', '<=', $this->jamSelesai);
                    });
                })
                ->lockForUpdate()
                ->exists();

            if ($conflictCheck) {
                DB::rollBack();
                session()->flash('error', 'Slot waktu ini baru saja dibooking oleh pengguna lain. Silakan pilih waktu lain.');
                $this->generateTimeSlots(); // Refresh
                return;
            }

            // Recalculate price (protection against price manipulation)
            $priceData = $lapangan->calculatePrice(
                $this->selectedDate,
                $this->jamMulai,
                $this->jamSelesai
            );

            $finalPrice = $priceData['total_price'];
            $pointsUsed = 0;
            $discountApplied = 0;

            // Handle point redemption
            if ($this->usePoints && $this->pointsToUse > 0 && Auth::check()) {
                $user = User::lockForUpdate()->findOrFail(Auth::id());
                
                // Validate user has enough points
                if ($user->points_balance >= $this->pointsToUse) {
                    $pointService = app(PointService::class);
                    $discountApplied = $pointService->pointsToRupiah($this->pointsToUse);
                    
                    // Ensure discount doesn't exceed 50% of price
                    $maxDiscount = floor($finalPrice * 0.5);
                    $discountApplied = min($discountApplied, $maxDiscount);
                    
                    $pointsUsed = $this->pointsToUse;
                    $finalPrice = max(0, $finalPrice - $discountApplied);
                } else {
                    // Not enough points - proceed without discount
                    $pointsUsed = 0;
                    $discountApplied = 0;
                }
            }

            // Create booking
            $booking = Booking::create([
                'lapangan_id' => $this->lapanganId,
                'user_id' => Auth::id(), // Nullable for guest bookings
                'tanggal' => $this->selectedDate,
                'jam_mulai' => $this->jamMulai,
                'jam_selesai' => $this->jamSelesai,
                'nama_pemesan' => $this->namaPemesan,
                'no_telepon' => $this->noTelepon,
                'email' => $this->email,
                'total_harga' => $finalPrice,
                'points_used' => $pointsUsed,
                'discount_amount' => $discountApplied,
                'status' => 'pending',
                'payment_status' => 'unpaid',
            ]);

            // Deduct points if used
            if ($pointsUsed > 0 && Auth::check()) {
                $pointService = app(PointService::class);
                $pointService->redeemPoints(Auth::user(), $booking, $pointsUsed);
            }

            DB::commit();

            // Send confirmation notification
            $booking->notify(new BookingConfirmed($booking));

            // Log successful booking
            Log::info('Booking created successfully', [
                'booking_id' => $booking->id,
                'user_id' => Auth::id(),
                'lapangan_id' => $this->lapanganId,
                'date' => $this->selectedDate,
                'time' => $this->jamMulai . ' - ' . $this->jamSelesai,
                'price' => $finalPrice,
                'points_used' => $pointsUsed,
            ]);

            session()->flash('success', 'Booking berhasil! Silakan lakukan pembayaran.');
            
            // Redirect to payment page
            return redirect()->route('payment.form', ['booking' => $booking->id]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Booking creation failed', [
                'user_id' => Auth::id(),
                'lapangan_id' => $this->lapanganId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            session()->flash('error', 'Terjadi kesalahan. Silakan coba lagi.');
        }
    }

    /**
     * Render component
     */
    public function render()
    {
        return view('livewire.booking-form-production');
    }
}

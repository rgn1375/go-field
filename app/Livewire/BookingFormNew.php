<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Lapangan;
use App\Models\Setting;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BookingFormNew extends Component
{
    public $lapanganId;
    public $lapangan;
    public $selectedDate;
    public $selectedTimeSlot;
    public $nama_pemesan = '';
    public $no_telepon = '';
    public $email = '';
    
    public $availableDates = [];
    public $availableTimeSlots = [];
    public $totalPrice = 0;

    protected $rules = [
        'nama_pemesan' => 'required|string|min:3',
        'no_telepon' => 'required|string|min:8',
        'email' => 'required|email',
        'selectedDate' => 'required',
        'selectedTimeSlot' => 'required',
    ];

    public function mount($lapanganId)
    {
        $this->lapanganId = $lapanganId;
        $this->lapangan = Lapangan::findOrFail($lapanganId);
        
        // Auto-fill if logged in
        if (Auth::check()) {
            $user = Auth::user();
            $this->nama_pemesan = $user->name;
            $this->no_telepon = $user->phone ?? '';
            $this->email = $user->email;
        }
        
        // Generate 7 days
        $this->generateAvailableDates();
    }

    public function generateAvailableDates()
    {
        $this->availableDates = [];
        
        for ($i = 0; $i < 30; $i++) {
            $date = Carbon::today()->addDays($i);
            $this->availableDates[] = [
                'date' => $date->format('Y-m-d'),
                'day' => $date->format('D'),
                'formatted' => $date->format('d M'),
            ];
        }
    }

    public function selectDate($date)
    {
        $this->selectedDate = $date;
        $this->selectedTimeSlot = null;
        $this->generateTimeSlots();
    }

    public function generateTimeSlots()
    {
        // Check if field is operational on selected date
        if (!$this->lapangan->isOperationalOn($this->selectedDate)) {
            $maintenanceInfo = $this->lapangan->getMaintenanceInfo();
            if ($maintenanceInfo) {
                session()->flash('error', 'Lapangan sedang maintenance: ' . $maintenanceInfo['reason']);
            } else {
                session()->flash('error', 'Lapangan tidak beroperasi pada tanggal ini.');
            }
            $this->availableTimeSlots = [];
            return;
        }
        
        // Get operational hours (field-specific or global)
        $hours = $this->lapangan->getOperationalHours();
        $jamBuka = $hours['jam_buka'];
        $jamTutup = $hours['jam_tutup'];
        
        $startHour = (int) substr($jamBuka, 0, 2);
        $endHour = (int) substr($jamTutup, 0, 2);
        
        // Get booked slots
        $bookedSlots = Booking::where('lapangan_id', $this->lapanganId)
            ->where('tanggal', $this->selectedDate)
            ->whereIn('status', ['pending', 'confirmed'])
            ->get();
        
        // CRITICAL: Get current time for past slot validation
        $now = Carbon::now();
        $selectedDate = Carbon::parse($this->selectedDate)->startOfDay();
        $isToday = $selectedDate->isToday();
        $minimumBookingTime = $now->copy()->addMinutes(30);
        
        $this->availableTimeSlots = [];
        for ($hour = $startHour; $hour < $endHour; $hour++) {
            $timeStart = sprintf('%02d:00', $hour);
            $timeEnd = sprintf('%02d:00', $hour + 1);
            
            // Check if booked
            $isBooked = $bookedSlots->contains(function ($booking) use ($timeStart) {
                return $booking->jam_mulai <= $timeStart && $booking->jam_selesai > $timeStart;
            });
            
            // CRITICAL: Check if slot has passed or within 30-minute buffer (only for today)
            $isPast = false;
            if ($isToday) {
                $slotStartTime = Carbon::createFromFormat('Y-m-d H:i', $selectedDate->format('Y-m-d') . ' ' . $timeStart);
                $isPast = $slotStartTime->lt($minimumBookingTime);
            }
            
            $this->availableTimeSlots[] = [
                'start' => $timeStart,
                'end' => $timeEnd,
                'display' => $timeStart . ' - ' . $timeEnd,
                'is_booked' => $isBooked || $isPast, // Mark as booked if past or within buffer
                'is_past' => $isPast,
            ];
        }
    }

    public function selectTimeSlot($start, $end)
    {
        // CRITICAL: Re-validate slot hasn't passed before allowing selection
        $selectedDate = Carbon::parse($this->selectedDate)->startOfDay();
        $slotStartTime = Carbon::createFromFormat('Y-m-d H:i', $selectedDate->format('Y-m-d') . ' ' . $start);
        $minimumBookingTime = Carbon::now()->addMinutes(30);
        
        if ($slotStartTime->lt($minimumBookingTime)) {
            session()->flash('error', 'Waktu yang dipilih sudah lewat atau terlalu dekat. Silakan pilih waktu lain.');
            $this->generateTimeSlots(); // Refresh to show updated slots
            return;
        }
        
        $this->selectedTimeSlot = $start . '-' . $end;
        
        // Calculate dynamic price
        $priceData = $this->lapangan->calculatePrice($this->selectedDate, $start, $end);
        $this->totalPrice = $priceData['total_price'];
    }
    
    /**
     * Refresh time slot availability (called by wire:poll if implemented)
     */
    public function refreshAvailability()
    {
        if ($this->selectedDate) {
            $this->generateTimeSlots();
        }
    }

    public function submitBooking()
    {
        $this->validate();
        
        if (!$this->selectedTimeSlot) {
            session()->flash('error', 'Silakan pilih waktu booking.');
            return;
        }
        
        $times = explode('-', $this->selectedTimeSlot);
        $jamMulai = $times[0];
        $jamSelesai = $times[1];
        
        // CRITICAL: Validate 30-minute buffer for same-day bookings
        $selectedDate = Carbon::parse($this->selectedDate)->startOfDay();
        $slotStartTime = Carbon::createFromFormat('Y-m-d H:i', $selectedDate->format('Y-m-d') . ' ' . $jamMulai);
        $minimumBookingTime = Carbon::now()->addMinutes(30);
        
        if ($slotStartTime->lt($minimumBookingTime)) {
            session()->flash('error', 'Waktu yang dipilih sudah lewat atau terlalu dekat. Booking harus dilakukan minimal 30 menit sebelum waktu main.');
            
            // Refresh time slots to show updated availability
            $this->generateTimeSlots();
            
            return;
        }
        
        try {
            DB::beginTransaction();
            
            // Lock lapangan record to prevent concurrent bookings
            $lapangan = Lapangan::where('id', $this->lapanganId)
                ->lockForUpdate()
                ->first();
            
            if (!$lapangan) {
                throw new \Exception('Lapangan tidak ditemukan.');
            }
            
            // Check for existing overlapping bookings with lock
            $conflictBooking = Booking::where('lapangan_id', $this->lapanganId)
                ->where('tanggal', $this->selectedDate)
                ->whereIn('status', ['pending', 'confirmed'])
                ->where(function ($query) use ($jamMulai, $jamSelesai) {
                    // Check if new booking overlaps with existing bookings
                    $query->where(function ($q) use ($jamMulai, $jamSelesai) {
                        // Case 1: New booking starts during existing booking
                        $q->where('jam_mulai', '<=', $jamMulai)
                          ->where('jam_selesai', '>', $jamMulai);
                    })->orWhere(function ($q) use ($jamMulai, $jamSelesai) {
                        // Case 2: New booking ends during existing booking
                        $q->where('jam_mulai', '<', $jamSelesai)
                          ->where('jam_selesai', '>=', $jamSelesai);
                    })->orWhere(function ($q) use ($jamMulai, $jamSelesai) {
                        // Case 3: New booking completely contains existing booking
                        $q->where('jam_mulai', '>=', $jamMulai)
                          ->where('jam_selesai', '<=', $jamSelesai);
                    });
                })
                ->lockForUpdate()
                ->first();
            
            if ($conflictBooking) {
                DB::rollBack();
                
                session()->flash('error', 'Maaf, slot waktu ini sudah dibooking oleh pengguna lain. Silakan pilih waktu lain.');
                
                // Refresh time slots to show updated availability
                $this->generateTimeSlots();
                
                return;
            }
            
            // Calculate points for authenticated users
            $pointsEarned = 0;
            $pointsRedeemed = 0;
            $discount = 0;
            
            // Calculate dynamic price
            $priceData = $this->lapangan->calculatePrice($this->selectedDate, $jamMulai, $jamSelesai);
            $finalPrice = $priceData['total_price'];
            
            if (Auth::check()) {
                // Calculate points earned (1% of price)
                // TAPI JANGAN LANGSUNG KASIH! Tunggu admin approve dulu
                $pointsEarned = floor($finalPrice * 0.01);
            }
            
            // Create booking
            $booking = Booking::create([
                'lapangan_id' => $this->lapanganId,
                'user_id' => Auth::id(),
                'tanggal' => $this->selectedDate,
                'jam_mulai' => $jamMulai,
                'jam_selesai' => $jamSelesai,
                'nama_pemesan' => $this->nama_pemesan,
                'nomor_telepon' => $this->no_telepon,
                'email' => $this->email,
                'harga' => $finalPrice,
                'points_earned' => $pointsEarned, // Simpan berapa poin yang akan didapat
                'points_redeemed' => $pointsRedeemed,
                'payment_status' => 'unpaid',
                'status' => 'pending', // Status pending sampai admin approve payment
            ]);
            
            // JANGAN KASIH POIN DULU! Tunggu admin approve payment di Filament
            // Points akan diberikan di BookingResource saat admin klik "Terima Pembayaran"
            
            DB::commit();
            
            session()->flash('success', 'Booking berhasil dibuat! Silakan lakukan pembayaran untuk mengkonfirmasi booking Anda.');
            
            return redirect()->route('dashboard');
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Booking Error: ' . $e->getMessage(), [
                'lapangan_id' => $this->lapanganId,
                'tanggal' => $this->selectedDate,
                'jam' => $jamMulai . '-' . $jamSelesai,
                'user' => Auth::id(),
            ]);
            
            session()->flash('error', 'Terjadi kesalahan saat memproses booking. Silakan coba lagi.');
            
            return;
        }
    }

    public function render()
    {
        return view('livewire.booking-form-new');
    }
}

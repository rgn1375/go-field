<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Booking;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class PaymentForm extends Component
{
    use WithFileUploads;

    public $bookingId;
    public $booking;
    public $showModal = false;
    
    public $paymentMethod = '';
    public $paymentProof;
    public $paymentNotes = '';
    
    protected $rules = [
        'paymentMethod' => 'required|in:cash,bank_transfer,qris,e_wallet',
        'paymentProof' => 'nullable|image|max:2048', // 2MB max
        'paymentNotes' => 'nullable|string|max:500',
    ];
    
    protected $messages = [
        'paymentMethod.required' => 'Metode pembayaran harus dipilih.',
        'paymentProof.image' => 'File harus berupa gambar.',
        'paymentProof.max' => 'Ukuran file maksimal 2MB.',
    ];
    
    public function mount($bookingId)
    {
        $this->bookingId = $bookingId;
        $this->loadBooking();
    }
    
    public function loadBooking()
    {
        $this->booking = Booking::with('lapangan')->find($this->bookingId);
        
        if (!$this->booking) {
            session()->flash('error', 'Booking tidak ditemukan.');
            return redirect()->route('dashboard');
        }
        
        // Load existing payment data if any
        if ($this->booking->payment_method) {
            $this->paymentMethod = $this->booking->payment_method;
            $this->paymentNotes = $this->booking->payment_notes ?? '';
        }
    }
    
    public function openModal()
    {
        // Check if booking can be paid
        if (!in_array($this->booking->status, ['pending', 'confirmed'])) {
            session()->flash('error', 'Booking tidak dapat dibayar.');
            return;
        }
        
        if (in_array($this->booking->payment_status, ['paid', 'refunded'])) {
            session()->flash('error', 'Pembayaran sudah selesai.');
            return;
        }
        
        $this->showModal = true;
    }
    
    public function closeModal()
    {
        $this->showModal = false;
        $this->paymentProof = null;
        $this->resetValidation();
    }
    
    public function updatedPaymentMethod($value)
    {
        // Clear payment proof if method is cash
        if ($value === 'cash') {
            $this->paymentProof = null;
        }
    }
    
    public function submitPayment()
    {
        // Validate
        $rules = $this->rules;
        
        // Payment proof required for non-cash methods
        if ($this->paymentMethod !== 'cash') {
            $rules['paymentProof'] = 'required|image|max:2048';
            $this->messages['paymentProof.required'] = 'Bukti pembayaran harus diunggah.';
        }
        
        $this->validate($rules);
        
        try {
            // Upload payment proof if exists
            $proofPath = null;
            if ($this->paymentProof) {
                $proofPath = $this->paymentProof->store('payment-proofs', 'public');
            }
            
            // Update booking
            $this->booking->payment_method = $this->paymentMethod;
            $this->booking->payment_notes = $this->paymentNotes;
            
            if ($this->paymentMethod === 'cash') {
                // Cash payment: mark as paid, but still need admin to confirm booking
                $this->booking->payment_status = 'paid';
                $this->booking->paid_at = now();
                $this->booking->payment_confirmed_at = now();
                // Status tetap pending, tunggu admin confirm booking
                $message = 'Metode pembayaran berhasil disimpan. Silakan bayar di tempat saat kedatangan.';
            } else {
                // Other methods: waiting for admin confirmation
                $this->booking->payment_status = 'waiting_confirmation';
                $this->booking->paid_at = now();
                
                if ($proofPath) {
                    // Delete old proof if exists
                    if ($this->booking->payment_proof) {
                        Storage::disk('public')->delete($this->booking->payment_proof);
                    }
                    $this->booking->payment_proof = $proofPath;
                }
                
                // Status tetap pending sampai admin approve payment
                $message = 'Bukti pembayaran berhasil diunggah. Menunggu konfirmasi admin.';
            }
            
            $this->booking->save();
            
            session()->flash('success', $message);
            $this->closeModal();
            $this->dispatch('payment-submitted');
            
            return redirect()->route('dashboard');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan saat memproses pembayaran. Silakan coba lagi.');
        }
    }
    
    public function getPaymentMethodLabelProperty()
    {
        $labels = [
            'cash' => 'Bayar di Tempat',
            'bank_transfer' => 'Transfer Bank',
            'qris' => 'QRIS',
            'e_wallet' => 'E-Wallet (Dana, OVO, GoPay)',
        ];
        
        return $labels[$this->paymentMethod] ?? '-';
    }
    
    public function render()
    {
        return view('livewire.payment-form');
    }
}

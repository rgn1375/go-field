<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Booking;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class PaymentForm extends Component
{
    use WithFileUploads;

    public $bookingId;
    public $booking;
    public $showModal = false;
    
    public $paymentMethodId = '';
    public $paymentMethods = [];
    public $paymentProof;
    public $paymentNotes = '';
    
    protected $rules = [
        'paymentMethodId' => 'required|exists:payment_methods,id',
        'paymentProof' => 'nullable|image|max:2048', // 2MB max
        'paymentNotes' => 'nullable|string|max:500',
    ];
    
    protected $messages = [
        'paymentMethodId.required' => 'Metode pembayaran harus dipilih.',
        'paymentMethodId.exists' => 'Metode pembayaran tidak valid.',
        'paymentProof.image' => 'File harus berupa gambar.',
        'paymentProof.max' => 'Ukuran file maksimal 2MB.',
    ];
    
    public function mount($bookingId)
    {
        $this->bookingId = $bookingId;
        $this->loadBooking();
        $this->loadPaymentMethods();
    }
    
    public function loadBooking()
    {
        $this->booking = Booking::with(['lapangan', 'paymentMethod'])->find($this->bookingId);
        
        if (!$this->booking) {
            session()->flash('error', 'Booking tidak ditemukan.');
            return redirect()->route('dashboard');
        }
        
        // Load existing payment data if any
        if ($this->booking->payment_method_id) {
            $this->paymentMethodId = $this->booking->payment_method_id;
            $this->paymentNotes = $this->booking->payment_notes ?? '';
        }
    }
    
    public function loadPaymentMethods()
    {
        // Load active payment methods
        $this->paymentMethods = PaymentMethod::active()->ordered()->get();
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
    
    public function updatedPaymentMethodId($value)
    {
        // Get selected payment method
        $paymentMethod = PaymentMethod::find($value);
        
        // Clear payment proof if method is cash
        if ($paymentMethod && $paymentMethod->code === 'cash') {
            $this->paymentProof = null;
        }
    }
    
    public function submitPayment()
    {
        // Validate
        $rules = $this->rules;
        
        // Get selected payment method
        $paymentMethod = PaymentMethod::find($this->paymentMethodId);
        
        if (!$paymentMethod) {
            session()->flash('error', 'Metode pembayaran tidak valid.');
            return;
        }
        
        // Payment proof required for non-cash methods
        if ($paymentMethod->code !== 'cash') {
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
            $this->booking->payment_method_id = $this->paymentMethodId;
            $this->booking->payment_notes = $this->paymentNotes;
            
            if ($paymentMethod->code === 'cash') {
                // Cash payment: tetap unpaid sampai customer bayar di tempat
                $this->booking->payment_status = 'unpaid';
                // Status tetap pending, tunggu customer datang & bayar, lalu admin confirm
                $message = 'Metode pembayaran berhasil disimpan. Silakan bayar di tempat saat kedatangan. Poin akan diberikan setelah pembayaran dikonfirmasi admin.';
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
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    
    public function getSelectedPaymentMethodProperty()
    {
        if ($this->paymentMethodId) {
            return PaymentMethod::find($this->paymentMethodId);
        }
        return null;
    }
    
    public function render()
    {
        return view('livewire.payment-form');
    }
}

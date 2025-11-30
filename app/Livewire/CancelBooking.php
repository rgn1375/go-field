<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Booking;
use App\Services\CancellationService;
use Illuminate\Support\Facades\Auth;

class CancelBooking extends Component
{
    public $bookingId;
    public $booking;
    public $showModal = false;
    public $cancellationReason = '';
    public $refundInfo = null;
    
    protected $rules = [
        'cancellationReason' => 'required|string|min:10|max:500',
    ];
    
    protected $messages = [
        'cancellationReason.required' => 'Alasan pembatalan harus diisi.',
        'cancellationReason.min' => 'Alasan pembatalan minimal 10 karakter.',
        'cancellationReason.max' => 'Alasan pembatalan maksimal 500 karakter.',
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
        
        // Calculate refund info
        $cancellationService = app(CancellationService::class);
        $this->refundInfo = $cancellationService->calculateRefund($this->booking);
    }
    
    public function openModal()
    {
        // Verify user can cancel
        $cancellationService = app(CancellationService::class);
        $canCancel = $cancellationService->canUserCancelBooking($this->booking, Auth::id());
        
        if (!$canCancel['can_cancel']) {
            session()->flash('error', $canCancel['reason']);
            $this->dispatch('booking-cancelled');
            return;
        }
        
        $this->showModal = true;
    }
    
    public function closeModal()
    {
        $this->showModal = false;
        $this->cancellationReason = '';
        $this->resetValidation();
    }
    
    public function cancelBooking()
    {
        $this->validate();
        
        $cancellationService = app(CancellationService::class);
        
        // Process cancellation
        $result = $cancellationService->cancelBooking(
            $this->booking,
            $this->cancellationReason,
            Auth::id()
        );
        
        if ($result['success']) {
            session()->flash('success', $result['message']);
            $this->closeModal();
            $this->dispatch('booking-cancelled');
            return redirect()->route('dashboard');
        } else {
            session()->flash('error', $result['message']);
        }
    }
    
    public function render()
    {
        return view('livewire.cancel-booking');
    }
}

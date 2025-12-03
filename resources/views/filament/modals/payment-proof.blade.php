<div class="space-y-4">
    {{-- Payment Details --}}
    <div class="grid grid-cols-2 gap-4 p-4 bg-gray-50 rounded-lg">
        <div>
            <p class="text-xs text-gray-500 mb-1">Metode Pembayaran:</p>
                <p class="font-semibold">
                @if($record->paymentMethod)
                    {{ $record->paymentMethod->name }}
                @else
                    -
                @endif
            </p>
        </div>
        
        <div>
            <p class="text-xs text-gray-500 mb-1">Total Pembayaran:</p>
            <p class="font-semibold">Rp {{ number_format($record->harga, 0, ',', '.') }}</p>
        </div>
        
        @if($record->payment_notes)
        <div class="col-span-2">
            <p class="text-xs text-gray-500 mb-1">Catatan:</p>
            <p class="text-sm">{{ $record->payment_notes }}</p>
        </div>
        @endif
        
        @if($record->paid_at)
        <div class="col-span-2">
            <p class="text-xs text-gray-500 mb-1">Waktu Upload:</p>
            <p class="text-sm">{{ $record->paid_at->format('d M Y, H:i') }}</p>
        </div>
        @endif
    </div>
    
    {{-- Payment Proof Image --}}
    @if($record->payment_proof)
    <div class="border rounded-lg overflow-hidden bg-gray-100">
        <img
            src="{{ asset('storage/' . $record->payment_proof) }}"
            alt="Bukti Pembayaran"
            class="w-full h-auto max-h-[400px] object-contain">
    </div>
    @else
    <div class="p-8 text-center text-gray-500">
        <p>Tidak ada bukti pembayaran</p>
    </div>
    @endif
</div>

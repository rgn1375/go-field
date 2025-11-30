<div>
    @if($booking && in_array($booking->status, ['pending', 'confirmed']))
        {{-- Cancel Button --}}
        <button 
            wire:click="openModal" 
            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors flex items-center gap-2">
            <i class="ai-circle-x"></i>
            Batalkan Booking
        </button>

        {{-- Cancellation Modal with Teleport --}}
        @if($showModal)
            @teleport('body')
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4" 
                style="z-index: 99999;"
                wire:click.self="closeModal">
                <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl" @click.stop>
                    {{-- Header --}}
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-2xl font-bold text-gray-900">Batalkan Booking</h3>
                        <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600">
                            <i class="ai-cross text-xl"></i>
                        </button>
                    </div>

                    {{-- Booking Info --}}
                    <div class="bg-gray-50 rounded-xl p-4 mb-6">
                        <h4 class="font-semibold text-gray-900 mb-2">Detail Booking:</h4>
                        <div class="text-sm space-y-1 text-gray-600">
                            <p><strong>Lapangan:</strong> {{ $booking->lapangan->title }}</p>
                            <p><strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($booking->tanggal)->format('d F Y') }}</p>
                            <p><strong>Waktu:</strong> {{ $booking->jam_mulai }} - {{ $booking->jam_selesai }}</p>
                            <p><strong>Harga:</strong> Rp {{ number_format($booking->harga, 0, ',', '.') }}</p>
                        </div>
                    </div>

                    {{-- Refund Info (ONLY if payment is complete) --}}
                    @if($booking->payment_status === 'paid' && $refundInfo && $refundInfo['can_cancel'])
                        <div class="mb-6 p-4 rounded-xl {{ $refundInfo['refund_percentage'] === 100 ? 'bg-green-50 border-2 border-green-200' : 'bg-yellow-50 border-2 border-yellow-200' }}">
                            <div class="flex items-start gap-3">
                                <i class="ai-info-circle {{ $refundInfo['refund_percentage'] === 100 ? 'text-green-600' : 'text-yellow-600' }} text-xl mt-0.5"></i>
                                <div class="flex-1">
                                    <h4 class="font-bold {{ $refundInfo['refund_percentage'] === 100 ? 'text-green-900' : 'text-yellow-900' }} mb-1">
                                        Refund {{ $refundInfo['refund_percentage'] }}%
                                    </h4>
                                    <p class="text-sm {{ $refundInfo['refund_percentage'] === 100 ? 'text-green-700' : 'text-yellow-700' }}">
                                        {{ $refundInfo['reason'] }}
                                    </p>
                                    <p class="text-sm font-semibold {{ $refundInfo['refund_percentage'] === 100 ? 'text-green-900' : 'text-yellow-900' }} mt-2">
                                        Anda akan menerima: <span class="text-lg">Rp {{ number_format($refundInfo['refund_amount'], 0, ',', '.') }}</span>
                                    </p>
                                    <p class="text-xs {{ $refundInfo['refund_percentage'] === 100 ? 'text-green-600' : 'text-yellow-600' }} mt-1">
                                        Refund akan ditambahkan ke poin Anda ({{ number_format(floor($refundInfo['refund_amount'] / 1000)) }} poin)
                                    </p>
                                </div>
                            </div>
                        </div>
                    @elseif($booking->payment_status !== 'paid')
                        {{-- User belum bayar - tidak ada refund --}}
                        <div class="mb-6 p-4 rounded-xl bg-blue-50 border-2 border-blue-200">
                            <div class="flex items-start gap-3">
                                <i class="ai-info-circle text-blue-600 text-xl mt-0.5"></i>
                                <div class="flex-1">
                                    <h4 class="font-bold text-blue-900 mb-1">
                                        Pembatalan Gratis
                                    </h4>
                                    <p class="text-sm text-blue-700">
                                        Karena Anda belum melakukan pembayaran, tidak ada biaya pembatalan.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Cancellation Reason --}}
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Alasan Pembatalan <span class="text-red-500">*</span>
                        </label>
                        <textarea 
                            wire:model="cancellationReason"
                            rows="4"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-emerald-500 focus:outline-none resize-none"
                            placeholder="Jelaskan alasan Anda membatalkan booking ini (minimal 10 karakter)..."></textarea>
                        @error('cancellationReason') 
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p> 
                        @enderror
                        <p class="text-xs text-gray-500 mt-1">
                            {{ strlen($cancellationReason) }}/500 karakter
                        </p>
                    </div>

                    {{-- Warning --}}
                    <div class="bg-red-50 border-2 border-red-200 rounded-xl p-4 mb-6">
                        <div class="flex items-start gap-3">
                            <i class="ai-alert-triangle text-red-600 text-xl mt-0.5"></i>
                            <div class="flex-1">
                                <h4 class="font-bold text-red-900 mb-1">Peringatan</h4>
                                <p class="text-sm text-red-700">
                                    Tindakan ini tidak dapat dibatalkan. Pastikan Anda benar-benar ingin membatalkan booking ini.
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex gap-3">
                        <button 
                            wire:click="closeModal"
                            class="flex-1 px-4 py-3 border-2 border-gray-300 text-gray-700 rounded-xl font-semibold hover:bg-gray-50 transition-colors">
                            Tidak Jadi
                        </button>
                        <button 
                            wire:click="cancelBooking"
                            class="flex-1 px-4 py-3 bg-red-600 text-white rounded-xl font-semibold hover:bg-red-700 transition-colors flex items-center justify-center gap-2">
                            <i class="ai-check"></i>
                            Ya, Batalkan
                        </button>
                    </div>
                </div>
            </div>
            @endteleport
        @endif
    @endif
</div>

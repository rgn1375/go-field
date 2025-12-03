<div><div>

    @if($booking && in_array($booking->payment_status, ['unpaid', 'waiting_confirmation']))    {{-- Success is as dangerous as failure. --}}

        {{-- Payment Button --}}</div>

        @if($booking->payment_status === 'unpaid')
            <button
                wire:click="openModal"
                class="w-full px-6 py-3 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors flex items-center justify-center gap-2 font-semibold">
                <i class="ai-credit-card"></i>
                Bayar Sekarang
            </button>
        @else
            <button
                wire:click="openModal"
                class="w-full px-6 py-3 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-colors flex items-center justify-center gap-2 font-semibold">
                <i class="ai-edit"></i>
                Ubah Bukti Pembayaran
            </button>
        @endif

        {{-- Payment Modal --}}
        @if($showModal)
            @teleport('body')
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4"
                style="z-index: 99999;"
                wire:click.self="closeModal">
                <div class="bg-white rounded-2xl max-w-2xl w-full shadow-2xl" style="max-height: 90vh; display: flex; flex-direction: column;" @click.stop>
                    {{-- Header (Fixed) --}}
                    <div class="flex items-center justify-between p-6 pb-4 border-b border-gray-200" style="flex-shrink: 0;">
                        <h3 class="text-2xl font-bold text-gray-900">💳 Pembayaran</h3>
                        <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600">
                            <i class="ai-cross text-xl"></i>
                        </button>
                    </div>

                    {{-- Scrollable Content --}}
                    <div class="p-6" style="overflow-y: auto; flex: 1; min-height: 0;">
                    {{-- Booking Summary --}}
                    <div class="bg-gradient-to-r from-emerald-50 to-emerald-100 border-2 border-emerald-200 rounded-xl p-5 mb-6">
                        <h4 class="font-bold text-emerald-900 mb-3">Ringkasan Booking:</h4>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-emerald-600 mb-1">Lapangan:</p>
                                <p class="font-semibold text-emerald-900">{{ $booking->lapangan->title }}</p>
                            </div>
                            <div>
                                <p class="text-emerald-600 mb-1">Kategori:</p>
                                <p class="font-semibold text-emerald-900">{{ $booking->lapangan->sportType?->name ?? 'Sport' }}</p>
                            </div>
                            <div>
                                <p class="text-emerald-600 mb-1">Tanggal:</p>
                                <p class="font-semibold text-emerald-900">{{ \Carbon\Carbon::parse($booking->tanggal)->format('d M Y') }}</p>
                            </div>
                            <div>
                                <p class="text-emerald-600 mb-1">Waktu:</p>
                                <p class="font-semibold text-emerald-900">{{ substr($booking->jam_mulai, 0, 5) }} - {{ substr($booking->jam_selesai, 0, 5) }}</p>
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t-2 border-emerald-200">
                            <div class="flex items-center justify-between">
                                <span class="text-emerald-700 font-semibold">Total Pembayaran:</span>
                                <span class="text-2xl font-bold text-emerald-900">Rp {{ number_format($booking->harga, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Payment Method Selection --}}
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-3">
                            Pilih Metode Pembayaran <span class="text-red-500">*</span>
                        </label>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @foreach($paymentMethods as $method)
                            <label class="relative cursor-pointer">
                                <input type="radio" wire:model.live="paymentMethodId" value="{{ $method->id }}" class="peer sr-only">
                                <div class="p-4 border-2 rounded-xl transition-all peer-checked:border-emerald-500 peer-checked:bg-emerald-50 hover:border-emerald-300">
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 rounded-lg bg-{{ $method->code === 'cash' ? 'emerald' : ($method->code === 'bank_transfer' ? 'blue' : ($method->code === 'qris' ? 'purple' : 'orange')) }}-100 flex items-center justify-center text-2xl">
                                            @if($method->code === 'cash')
                                                💵
                                            @elseif($method->code === 'bank_transfer')
                                                🏦
                                            @elseif($method->code === 'qris')
                                                📱
                                            @else
                                                💳
                                            @endif
                                        </div>
                                        <div>
                                            <p class="font-bold text-gray-900">{{ $method->name }}</p>
                                            <p class="text-xs text-gray-600">{{ $method->description }}</p>
                                            @if($method->admin_fee > 0 || $method->admin_fee_percentage > 0)
                                            <p class="text-xs text-orange-600 mt-0.5">
                                                +Biaya:
                                                @if($method->admin_fee > 0)
                                                    Rp {{ number_format($method->admin_fee, 0, ',', '.') }}
                                                @endif
                                                @if($method->admin_fee_percentage > 0)
                                                    {{ $method->admin_fee > 0 ? '+' : '' }}{{ $method->admin_fee_percentage }}%
                                                @endif
                                            </p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </label>
                            @endforeach
                        </div>
                        
                        @error('paymentMethodId')
                            <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Payment Instructions (shown when method selected) --}}
                    @if($paymentMethodId)
                        @php
                            $selectedMethod = $paymentMethods->firstWhere('id', $paymentMethodId);
                        @endphp
                        
                        @if($selectedMethod && $selectedMethod->code !== 'cash')
                        <div class="mb-6 p-5 bg-blue-50 border-2 border-blue-200 rounded-xl">
                            <div class="flex items-start gap-3">
                                <i class="ai-info-circle text-blue-600 text-xl mt-0.5"></i>
                                <div class="flex-1">
                                    <h4 class="font-bold text-blue-900 mb-2">Instruksi Pembayaran</h4>
                                    
                                    @php
                                        $config = $selectedMethod->config ?? [];
                                    @endphp
                                    
                                    @if($selectedMethod->code === 'bank_transfer')
                                        <div class="text-sm text-blue-800 space-y-2">
                                            <p class="font-semibold">Transfer ke rekening berikut:</p>
                                            <div class="bg-white rounded-lg p-3 space-y-1">
                                                @if(isset($config['bank_accounts']))
                                                    @foreach($config['bank_accounts'] as $account)
                                                    <p><strong>{{ $account['bank'] ?? 'Bank' }}:</strong> {{ $account['account_number'] ?? '-' }} a.n. {{ $account['account_name'] ?? 'GoField' }}</p>
                                                    @endforeach
                                                @else
                                                    <p><strong>Bank BCA:</strong> 1234567890 a.n. GoField</p>
                                                @endif
                                            </div>
                                            <p class="text-xs">Upload bukti transfer setelah membayar</p>
                                        </div>
                                    @elseif($selectedMethod->code === 'qris')
                                        <div class="text-sm text-blue-800 space-y-2">
                                            <p class="font-semibold">Scan QR Code berikut:</p>
                                            <div class="bg-white rounded-lg p-3 text-center">
                                                @if(isset($config['qr_image']) && $config['qr_image'])
                                                    <img src="{{ asset('storage/' . $config['qr_image']) }}" alt="QRIS Code" class="w-48 h-48 mx-auto rounded-lg">
                                                @else
                                                    <div class="w-48 h-48 bg-gray-200 rounded-lg mx-auto flex items-center justify-center">
                                                        <p class="text-gray-500 text-sm">[QRIS Code]</p>
                                                    </div>
                                                @endif
                                            </div>
                                            <p class="text-xs">Upload screenshot pembayaran</p>
                                        </div>
                                    @elseif($selectedMethod->code === 'e_wallet')
                                        <div class="text-sm text-blue-800 space-y-2">
                                            <p class="font-semibold">Transfer ke nomor berikut:</p>
                                            <div class="bg-white rounded-lg p-3 space-y-1">
                                                @if(isset($config['wallets']))
                                                    @foreach($config['wallets'] as $wallet)
                                                    <p><strong>{{ $wallet['provider'] ?? 'E-Wallet' }}:</strong> {{ $wallet['phone'] ?? '-' }}</p>
                                                    @endforeach
                                                @else
                                                    <p><strong>Dana:</strong> 08123456789 a.n. GoField</p>
                                                    <p><strong>OVO:</strong> 08123456789</p>
                                                    <p><strong>GoPay:</strong> 08123456789</p>
                                                @endif
                                            </div>
                                            <p class="text-xs">Upload bukti transfer setelah membayar</p>
                                        </div>
                                    @else
                                        <div class="text-sm text-blue-800">
                                            <p>Silakan lakukan pembayaran dan upload bukti pembayaran.</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($selectedMethod && $selectedMethod->code !== 'cash')
                        {{-- Upload Payment Proof --}}
                        <div class="mb-6">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Bukti Pembayaran <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="file"
                                wire:model="paymentProof"
                                accept="image/*"
                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-emerald-500 focus:outline-none">
                            @error('paymentProof')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                            
                            @if($paymentProof)
                                <div class="mt-3 p-3 bg-green-50 border-2 border-green-200 rounded-lg flex items-center gap-2">
                                    <i class="ai-check-circle text-green-600"></i>
                                    <span class="text-sm text-green-800">File siap diunggah: {{ $paymentProof->getClientOriginalName() }}</span>
                                </div>
                            @endif
                            
                            <div wire:loading wire:target="paymentProof" class="mt-2 text-sm text-blue-600">
                                <i class="ai-loading animate-spin"></i> Mengupload file...
                            </div>
                        </div>
                        @endif
                    @endif

                    {{-- Payment Notes --}}
                    <div class="mb-0">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Catatan (Opsional)
                        </label>
                        <textarea
                            wire:model="paymentNotes"
                            rows="3"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-emerald-500 focus:outline-none resize-none"
                            placeholder="Contoh: Transfer dari rekening BCA atas nama John Doe"></textarea>
                        @error('paymentNotes')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    </div>

                    {{-- Actions (Fixed at Bottom) --}}
                    <div class="flex gap-3 p-6 pt-4 border-t border-gray-200 bg-white" style="flex-shrink: 0;">
                        <button
                            wire:click="closeModal"
                            class="flex-1 px-6 py-3 border-2 border-gray-300 text-gray-700 rounded-xl font-semibold hover:bg-gray-50 transition-colors">
                            Batal
                        </button>
                        <button
                            wire:click="submitPayment"
                            wire:loading.attr="disabled"
                            class="flex-1 px-6 py-3 bg-emerald-600 text-white rounded-xl font-semibold hover:bg-emerald-700 transition-colors flex items-center justify-center gap-2 disabled:opacity-50">
                            <span wire:loading.remove wire:target="submitPayment">
                                <i class="ai-check"></i>
                                Konfirmasi Pembayaran
                            </span>
                            <span wire:loading wire:target="submitPayment">
                                <i class="ai-loading animate-spin"></i>
                                Memproses...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
            @endteleport
        @endif
    @endif
</div>

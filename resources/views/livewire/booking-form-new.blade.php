<div wire:poll.30s="refreshAvailability">
    {{-- Auto-refresh every 30 seconds to update slot availability --}}
    
    {{-- Lapangan Info --}}
    <div class="bg-white rounded-2xl p-8 shadow-lg border-2 border-gray-100 mb-8">
        <h2 class="text-3xl font-bold text-gray-900 mb-2">{{ $lapangan->title }}</h2>
        <div class="flex items-center gap-4 mb-6">
            <span class="px-4 py-2 bg-emerald-50 text-emerald-700 rounded-lg font-semibold">
                {{ $lapangan->category }}
            </span>
            <span class="text-2xl font-bold text-emerald-600">
                @if($lapangan->weekday_price && $lapangan->weekend_price)
                    Rp {{ number_format($lapangan->weekday_price, 0, ',', '.') }} - {{ number_format($lapangan->weekend_price, 0, ',', '.') }}
                @elseif($lapangan->weekday_price)
                    Rp {{ number_format($lapangan->weekday_price, 0, ',', '.') }}
                @elseif($lapangan->weekend_price)
                    Rp {{ number_format($lapangan->weekend_price, 0, ',', '.') }}
                @else
                    Rp {{ number_format($lapangan->price, 0, ',', '.') }}
                @endif
                <span class="text-sm text-gray-500">/jam</span>
            </span>
            @if($lapangan->peak_hour_start && $lapangan->peak_hour_end)
                <span class="px-3 py-1 bg-orange-50 text-orange-700 rounded-lg text-xs font-semibold">
                    ⚡ Peak {{ substr($lapangan->peak_hour_start, 0, 5) }}-{{ substr($lapangan->peak_hour_end, 0, 5) }} ({{ $lapangan->peak_hour_multiplier }}x)
                </span>
            @endif
        </div>

        @if($lapangan->description)
        <div class="prose max-w-none text-gray-600">
            {!! $lapangan->description !!}
        </div>
        @endif
    </div>

    {{-- Success/Error Messages --}}
    @if (session()->has('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-6 py-4 rounded-xl mb-6">
            <i class="ai-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 px-6 py-4 rounded-xl mb-6">
            <i class="ai-circle-x"></i> {{ session('error') }}
        </div>
    @endif

    {{-- Pilih Tanggal --}}
    <div class="bg-white rounded-2xl p-8 shadow-lg border-2 border-gray-100 mb-8">
        <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
            <i class="ai-calendar text-emerald-600"></i>
            Pilih Tanggal
        </h3>
        
        <div class="w-full">
            <div style="overflow-x: auto; overflow-y: hidden; -webkit-overflow-scrolling: touch; scrollbar-width: thin; scrollbar-color: #10b981 #f3f4f6;">
                <div style="display: flex; gap: 12px; padding-bottom: 16px; min-width: min-content;">
                    @foreach ($availableDates as $date)
                        <button type="button" 
                            wire:click="selectDate('{{ $date['date'] }}')"
                            style="flex-shrink: 0; width: 100px;"
                            class="p-4 rounded-xl text-center transition-all border-2 {{ $selectedDate === $date['date'] ? 'bg-emerald-600 border-emerald-600 text-white shadow-lg' : 'bg-white border-gray-200 text-gray-700 hover:border-emerald-500 hover:bg-emerald-50' }}">
                            <p class="text-xs font-semibold">{{ $date['day'] }}</p>
                            <p class="text-base font-bold mt-1">{{ $date['formatted'] }}</p>
                        </button>
                    @endforeach
                </div>
            </div>
            
            {{-- Scroll hint --}}
            <div class="text-center mt-2 text-sm text-gray-500">
                <i class="ai-arrow-left text-xs"></i> Geser untuk melihat tanggal lain <i class="ai-arrow-right text-xs"></i>
            </div>
        </div>
    </div>

    {{-- Pilih Waktu --}}
    @if ($selectedDate)
        <div class="bg-white rounded-2xl p-8 shadow-lg border-2 border-gray-100 mb-8">
            <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                <i class="ai-clock text-emerald-600"></i>
                Pilih Jam Main
            </h3>
            
            <div class="grid grid-cols-3 md:grid-cols-4 gap-3">
                @foreach ($availableTimeSlots as $slot)
                    @if ($slot['is_booked'])
                        <div class="p-4 rounded-xl text-center bg-gray-100 text-gray-400 cursor-not-allowed opacity-60">
                            <i class="ai-lock text-2xl mb-2"></i>
                            <p class="font-semibold">{{ $slot['display'] }}</p>
                            <p class="text-xs">
                                @if(isset($slot['is_past']) && $slot['is_past'])
                                    Sudah Lewat
                                @else
                                    Sudah Dipesan
                                @endif
                            </p>
                        </div>
                    @else
                        <button type="button"
                            wire:click="selectTimeSlot('{{ $slot['start'] }}', '{{ $slot['end'] }}')"
                            class="p-4 rounded-xl text-center transition-all {{ $selectedTimeSlot === $slot['start'] . '-' . $slot['end'] ? 'bg-emerald-600 text-white shadow-lg' : 'bg-gray-50 text-gray-700 hover:bg-emerald-50' }}">
                            <i class="ai-check-circle text-2xl mb-2"></i>
                            <p class="font-semibold">{{ $slot['display'] }}</p>
                            <p class="text-xs">Tersedia</p>
                        </button>
                    @endif
                @endforeach
            </div>
        </div>
    @endif

    {{-- Form Booking --}}
    @if ($selectedTimeSlot)
        <div class="bg-white rounded-2xl p-8 shadow-lg border-2 border-gray-100">
            <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                <i class="ai-person text-emerald-600"></i>
                Data Pemesan
            </h3>

            <form wire:submit.prevent="submitBooking" class="space-y-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Lengkap</label>
                    <input type="text" wire:model="nama_pemesan" 
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-emerald-500 focus:outline-none"
                        placeholder="Masukkan nama lengkap">
                    @error('nama_pemesan') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nomor Telepon</label>
                    <input type="text" wire:model="no_telepon" 
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-emerald-500 focus:outline-none"
                        placeholder="08xx xxxx xxxx">
                    @error('no_telepon') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                    <input type="email" wire:model="email" 
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-emerald-500 focus:outline-none"
                        placeholder="email@example.com">
                    @error('email') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="bg-emerald-50 rounded-xl p-6 border-2 border-emerald-200">
                    <h4 class="font-bold text-gray-900 mb-3">Ringkasan Booking</h4>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Lapangan:</span>
                            <span class="font-semibold">{{ $lapangan->title }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Tanggal:</span>
                            <span class="font-semibold">{{ Carbon\Carbon::parse($selectedDate)->format('d F Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Waktu:</span>
                            <span class="font-semibold">{{ str_replace('-', ' - ', $selectedTimeSlot) }}</span>
                        </div>
                        
                        @php
                            $times = explode('-', $selectedTimeSlot);
                            $priceData = $lapangan->calculatePrice($selectedDate, $times[0], $times[1]);
                        @endphp
                        
                        {{-- Show price breakdown --}}
                        <div class="border-t-2 border-emerald-200 mt-3 pt-3 space-y-1">
                            <div class="flex justify-between text-xs text-gray-600">
                                <span>
                                    {{ $priceData['is_weekend'] ? '🌴 Weekend' : '📅 Weekday' }} 
                                    ({{ $priceData['duration_hours'] }} jam)
                                </span>
                                <span>Rp {{ number_format($priceData['price_breakdown']['base'], 0, ',', '.') }}</span>
                            </div>
                            
                            @if($priceData['is_peak_hour'])
                                <div class="flex justify-between text-xs text-orange-600 font-semibold">
                                    <span>⚡ Peak Hour ({{ $priceData['peak_multiplier'] }}x)</span>
                                    <span>+ Rp {{ number_format($priceData['price_breakdown']['peak_additional'], 0, ',', '.') }}</span>
                                </div>
                            @endif
                        </div>
                        
                        <div class="border-t-2 border-emerald-300 mt-3 pt-3 flex justify-between">
                            <span class="text-gray-900 font-bold">Total:</span>
                            <span class="text-2xl font-bold text-emerald-600">Rp {{ number_format($totalPrice, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                <button type="submit" 
                    class="w-full bg-emerald-600 text-white px-8 py-4 rounded-xl font-bold text-lg hover:bg-emerald-700 transition-colors flex items-center justify-center gap-2">
                    <i class="ai-check"></i>
                    Konfirmasi Booking
                </button>
            </form>
        </div>
    @endif
</div>

<div>
    <div class="flex flex-col md:flex-row gap-6 animate-fade-in">
        <div class="w-full md:w-8/12">
            <div class="bg-white rounded-2xl p-8 shadow-lg border-2 border-gray-100">
                <span class="badge-standard inline-flex items-center gap-2 text-base">
                    <i class="ai-star-fill text-yellow-400"></i>
                    {{ $lapangan->category }}
                </span>
                <h2 class="text-3xl md:text-4xl font-extrabold mt-4 text-gray-900">{{ $lapangan->title }}</h2>
                <div class="h-1 w-20 bg-gradient-to-r from-primary to-primary-light rounded-full mt-4 mb-8"></div>
                
                <div class="mb-6">
                    <h3 class="text-xl font-bold mb-4 flex items-center gap-2 text-gray-900">
                        <i class="ai-info-circle text-primary"></i>
                        Deskripsi Lapangan
                    </h3>
                    <div class="text-gray-600 rich-text leading-relaxed bg-gray-50 rounded-xl p-6">
                        {!! $lapangan->description
                            ? strip_tags($lapangan->description, '<p><ol><ul><li><strong><em><b><i><u><br>')
                            : 'Belum ada deskripsi.' !!}
                    </div>
                </div>
            </div>
        </div>
        
        <div class="w-full md:w-4/12">
            <div class="bg-gradient-to-br from-white to-gray-50 rounded-2xl p-8 shadow-xl border-2 border-primary/20 sticky top-4 animate-fade-in" style="animation-delay: 0.2s;">
                <div class="text-center mb-6">
                    <p class="text-gray-600 text-sm mb-2 font-medium">Harga Mulai Dari</p>
                    <div class="flex items-center justify-center gap-2">
                        <span class="text-4xl font-extrabold text-primary">Rp {{ number_format($lapangan->price, 0, ',', '.') }}</span>
                    </div>
                    <span class="text-sm text-gray-500 font-medium">per sesi (1 jam)</span>
                </div>

                <div class="bg-primary/10 rounded-xl p-4 mb-6">
                    <div class="flex items-center gap-3 text-sm text-gray-700">
                        <i class="ai-check-circle text-primary text-lg"></i>
                        <span>Fasilitas Lengkap</span>
                    </div>
                    <div class="flex items-center gap-3 text-sm text-gray-700 mt-2">
                        <i class="ai-check-circle text-primary text-lg"></i>
                        <span>Area Parkir Luas</span>
                    </div>
                    <div class="flex items-center gap-3 text-sm text-gray-700 mt-2">
                        <i class="ai-check-circle text-primary text-lg"></i>
                        <span>Toilet & Ruang Ganti</span>
                    </div>
                </div>

                <a href="#booking-form" class="btn-primary w-full text-center text-lg flex items-center justify-center gap-2 group">
                    <i class="ai-calendar group-hover:scale-110 transition-transform"></i>
                    Pilih Jadwal
                </a>
            </div>
        </div>
    </div>

    <div class="h-px bg-gradient-to-r from-transparent via-gray-300 to-transparent my-12"></div>

    <div class="mb-10 animate-fade-in" id="booking-form" style="animation-delay: 0.3s;">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 bg-primary/10 rounded-xl flex items-center justify-center">
                <i class="ai-calendar text-primary text-xl"></i>
            </div>
            <h3 class="text-2xl font-bold text-gray-900">Pilih Tanggal</h3>
        </div>
        <div class="grid grid-cols-3 md:grid-cols-8 gap-3">
            @foreach ($availableDates as $index => $date)
                <button type="button" wire:click="selectDate('{{ $date['date'] }}')"
                    class="group relative cursor-pointer rounded-2xl text-center p-5 transition-all duration-300 transform hover:scale-105 {{ $selectedDate === $date['date'] ? 'bg-gradient-to-br from-primary to-primary-dark border-2 border-primary text-white shadow-xl' : 'bg-white border-2 border-gray-200 text-gray-700 hover:border-primary hover:shadow-lg' }}"
                    style="animation: fadeIn 0.5s ease-out {{ $index * 0.05 }}s forwards; opacity: 0;">
                    <p class="font-semibold text-sm">{{ $date['day'] }}</p>
                    <p class="font-bold text-lg mt-1">{{ $date['formatted'] }}</p>
                    @if($selectedDate === $date['date'])
                        <div class="absolute -top-2 -right-2 w-6 h-6 bg-white rounded-full flex items-center justify-center shadow-lg">
                            <i class="ai-check text-primary text-sm"></i>
                        </div>
                    @endif
                </button>
            @endforeach
            <button type="button"
                class="cursor-pointer border-2 border-dashed rounded-2xl text-center p-5 transition-all duration-300 bg-gray-50 border-gray-300 text-gray-400 hover:border-primary hover:bg-primary/5 hover:text-primary hover:scale-105">
                <i class="ai-calendar text-3xl"></i>
            </button>
        </div>
    </div>

    @if ($selectedDate)
        <div class="mb-10 animate-fade-in">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 bg-primary/10 rounded-xl flex items-center justify-center">
                    <i class="ai-clock text-primary text-xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-900">Pilih Jam Main</h3>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-6 gap-3">
                @foreach ($availableTimeSlots as $index => $slot)
                    @if ($slot['is_booked'])
                        <div class="relative bg-gray-100 border-2 border-gray-200 rounded-2xl text-center p-5 opacity-60 animate-fade-in" style="animation-delay: {{ $index * 0.03 }}s;">
                            <div class="absolute top-2 right-2">
                                <span class="bg-red-100 text-red-600 text-xs px-2 py-1 rounded-full font-semibold">Booked</span>
                            </div>
                            <i class="ai-lock text-gray-400 text-xl mb-2"></i>
                            <p class="font-semibold text-gray-500 text-lg">{{ $slot['label'] }}</p>
                            <p class="text-xs text-gray-400 mt-1">Tidak Tersedia</p>
                        </div>
                    @else
                        <button 
                            wire:click="selectTimeSlot('{{ $slot['slot_key'] }}')"
                            class="group relative cursor-pointer rounded-2xl text-center p-5 transition-all duration-300 transform hover:scale-105 animate-fade-in {{ $selectedTimeSlot === $slot['slot_key'] ? 'bg-gradient-to-br from-primary to-primary-dark border-2 border-primary text-white shadow-xl' : 'bg-white border-2 border-gray-200 text-gray-700 hover:border-primary hover:shadow-lg' }}"
                            style="animation-delay: {{ $index * 0.03 }}s;">
                            @if ($selectedTimeSlot === $slot['slot_key'])
                                <div class="absolute -top-2 -right-2 w-6 h-6 bg-white rounded-full flex items-center justify-center shadow-lg">
                                    <i class="ai-check text-primary text-sm"></i>
                                </div>
                            @endif
                            <div class="flex items-center justify-center gap-1 mb-2">
                                <i class="ai-clock {{ $selectedTimeSlot === $slot['slot_key'] ? 'text-white' : 'text-primary' }} group-hover:rotate-180 transition-transform duration-500"></i>
                                <span class="text-xs font-medium">60 Menit</span>
                            </div>
                            <p class="font-bold text-lg">{{ $slot['label'] }}</p>
                            <div class="mt-2 pt-2 border-t {{ $selectedTimeSlot === $slot['slot_key'] ? 'border-white/30' : 'border-gray-200' }}">
                                <p class="font-bold text-sm">Rp {{ number_format($slot['price'], 0, ',', '.') }}</p>
                            </div>
                        </button>
                    @endif
                @endforeach
            </div>
        </div>
    @endif
    
    @if ($selectedDate && $selectedTimeSlot)
        <div class="animate-fade-in">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 bg-primary/10 rounded-xl flex items-center justify-center">
                    <i class="ai-person text-primary text-xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-900">Data Pemesan</h3>
            </div>
            
            @if (session()->has('success'))
                <div class="mb-6 p-4 rounded-2xl bg-gradient-to-r from-green-50 to-green-100 border-2 border-green-200 flex items-center gap-3 animate-scale-in">
                    <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center flex-shrink-0">
                        <i class="ai-check text-white text-xl"></i>
                    </div>
                    <div>
                        <p class="font-bold text-green-900">Booking Berhasil!</p>
                        <p class="text-sm text-green-700">{{ session('success') }}</p>
                    </div>
                </div>
            @endif
            
            @if (session()->has('error'))
                <div class="mb-6 p-4 rounded-2xl bg-gradient-to-r from-red-50 to-red-100 border-2 border-red-200 flex items-center gap-3 animate-scale-in">
                    <div class="w-10 h-10 bg-red-500 rounded-full flex items-center justify-center flex-shrink-0">
                        <i class="ai-circle-x text-white text-xl"></i>
                    </div>
                    <div>
                        <p class="font-bold text-red-900">Booking Gagal!</p>
                        <p class="text-sm text-red-700">{{ session('error') }}</p>
                    </div>
                </div>
            @endif
            
            <form wire:submit.prevent="submit" class="bg-gradient-to-br from-white to-gray-50 rounded-2xl p-8 shadow-xl border-2 border-gray-100">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="name" class="text-sm font-semibold text-gray-700 mb-2 flex items-center gap-2">
                            <i class="ai-person text-primary"></i>
                            Nama Lengkap
                        </label>
                        <input type="text" wire:model="nama_pemesan" placeholder="Masukkan nama lengkap"
                        class="py-3.5 px-4 block w-full rounded-xl border-2 border-gray-200 focus:border-primary outline-none transition-all duration-300 bg-white hover:border-gray-300 focus:shadow-lg">
                        @error('nama_pemesan')
                            <p class="text-sm text-red-600 mt-2 flex items-center gap-1">
                                <i class="ai-circle-alert"></i>{{ $message }}
                            </p>
                        @enderror
                    </div>
                    <div>
                        <label for="email" class="text-sm font-semibold text-gray-700 mb-2 flex items-center gap-2">
                            <i class="ai-envelope text-primary"></i>
                            Email
                        </label>
                        <input type="email" wire:model="email" placeholder="email@example.com"
                        class="py-3.5 px-4 block w-full rounded-xl border-2 border-gray-200 focus:border-primary outline-none transition-all duration-300 bg-white hover:border-gray-300 focus:shadow-lg">
                        @error('email')
                            <p class="text-sm text-red-600 mt-2 flex items-center gap-1">
                                <i class="ai-circle-alert"></i>{{ $message }}
                            </p>
                        @enderror
                    </div>
                    <div>
                        <label for="phone" class="text-sm font-semibold text-gray-700 mb-2 flex items-center gap-2">
                            <i class="ai-phone text-primary"></i>
                            Nomor Telepon / WhatsApp
                        </label>
                        <input type="text" wire:model="no_telepon" placeholder="08123456789"
                        class="py-3.5 px-4 block w-full rounded-xl border-2 border-gray-200 focus:border-primary outline-none transition-all duration-300 bg-white hover:border-gray-300 focus:shadow-lg">
                        @error('no_telepon')
                            <p class="text-sm text-red-600 mt-2 flex items-center gap-1">
                                <i class="ai-circle-alert"></i>{{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>
                
                @error('selectedTimeSlot')
                    <p class="text-sm text-red-600 mb-4 flex items-center gap-1 bg-red-50 p-3 rounded-xl">
                        <i class="ai-circle-alert"></i>{{ $message }}
                    </p>
                @enderror

                <!-- Points Redemption (Authenticated Users Only) -->
                @auth
                    <div class="bg-gradient-to-br from-yellow-50 to-orange-50 rounded-xl p-6 mb-6 border-2 border-yellow-200">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="font-bold text-gray-900 flex items-center gap-2">
                                <i class="ai-star text-yellow-600 text-xl"></i>
                                Gunakan Poin
                            </h4>
                            <div class="text-right">
                                <p class="text-sm text-gray-600">Poin Tersedia</p>
                                <p class="text-2xl font-bold text-yellow-600">{{ number_format($userPointsBalance) }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 mb-4">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" wire:model.live="usePoints" class="sr-only peer">
                                <div class="w-14 h-7 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-yellow-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-yellow-600"></div>
                            </label>
                            <span class="text-sm font-semibold text-gray-700">Gunakan poin untuk diskon</span>
                        </div>

                        @if($usePoints)
                            <div class="space-y-3 animate-fade-in">
                                <div>
                                    <label class="text-sm font-semibold text-gray-700 mb-2 block">
                                        Jumlah Poin (Max: {{ number_format($maxRedeemablePoints) }})
                                    </label>
                                    <div class="flex items-center gap-3">
                                        <input type="number" wire:model.live="pointsToRedeem" 
                                               min="0" max="{{ $maxRedeemablePoints }}" step="10"
                                               class="flex-1 py-3 px-4 rounded-xl border-2 border-yellow-300 focus:border-yellow-500 outline-none transition-all bg-white">
                                        <button type="button" wire:click="$set('pointsToRedeem', {{ $maxRedeemablePoints }})"
                                                class="px-4 py-3 bg-yellow-600 text-white rounded-xl font-semibold hover:bg-yellow-700 transition-colors">
                                            Max
                                        </button>
                                    </div>
                                    <p class="text-xs text-gray-600 mt-2">100 poin = Rp 1.000 diskon</p>
                                </div>

                                @if($pointsToRedeem > 0)
                                    <div class="bg-white rounded-lg p-4 border border-yellow-300">
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm text-gray-600">Diskon dari poin:</span>
                                            <span class="text-lg font-bold text-green-600">- Rp {{ number_format($discount, 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                @endauth
                
                <!-- Booking Summary -->
                <div class="bg-primary/5 rounded-xl p-6 mb-6 border border-primary/20">
                    <h4 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <i class="ai-clipboard text-primary"></i>
                        Ringkasan Booking
                    </h4>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center py-2 border-b border-primary/10">
                            <span class="text-gray-600">Lapangan</span>
                            <span class="font-semibold text-gray-900">{{ $lapangan->title }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-primary/10">
                            <span class="text-gray-600">Tanggal</span>
                            <span class="font-semibold text-gray-900">{{ \Carbon\Carbon::parse($selectedDate)->format('d M Y') }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-primary/10">
                            <span class="text-gray-600">Jam</span>
                            <span class="font-semibold text-gray-900">{{ str_replace('-', ' - ', $selectedTimeSlot) }} WIB</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-primary/10">
                            <span class="text-gray-600">Harga</span>
                            <span class="font-semibold text-gray-900">Rp {{ number_format($lapangan->price, 0, ',', '.') }}</span>
                        </div>
                        @if($discount > 0)
                            <div class="flex justify-between items-center py-2 border-b border-primary/10">
                                <span class="text-green-600">Diskon Poin</span>
                                <span class="font-semibold text-green-600">- Rp {{ number_format($discount, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between items-center py-3 pt-4">
                            <span class="text-lg font-bold text-gray-900">Total Bayar</span>
                            <span class="text-2xl font-extrabold text-primary">
                                Rp {{ number_format($finalPrice > 0 ? $finalPrice : $lapangan->price, 0, ',', '.') }}
                            </span>
                        </div>
                        @auth
                            @if($lapangan->price > 0)
                                <div class="bg-yellow-50 rounded-lg p-3 mt-3 border border-yellow-200">
                                    <p class="text-sm text-yellow-800 flex items-center gap-2">
                                        <i class="ai-star text-yellow-600"></i>
                                        <span>Dapatkan <strong>+{{ floor($lapangan->price * 0.01) }} poin</strong> dari booking ini!</span>
                                    </p>
                                </div>
                            @endif
                        @endauth
                    </div>
                </div>
                
                <div class="flex flex-col sm:flex-row gap-3">
                    <button type="submit" wire:loading.attr="disabled"
                        class="flex-1 group cursor-pointer py-4 px-8 rounded-xl bg-gradient-to-r from-primary to-primary-dark text-white font-bold text-lg transition-all duration-300 hover:shadow-2xl hover:-translate-y-1 flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span wire:loading.remove>
                            <i class="ai-check-circle group-hover:scale-110 transition-transform"></i>
                            Konfirmasi Booking
                        </span>
                        <span wire:loading class="flex items-center gap-2">
                            <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Memproses...
                        </span>
                    </button>
                    <button type="button" onclick="window.location.reload()"
                        class="py-4 px-8 rounded-xl bg-white border-2 border-gray-200 text-gray-700 font-semibold text-lg transition-all duration-300 hover:border-primary hover:text-primary hover:shadow-lg flex items-center justify-center gap-2">
                        <i class="ai-refresh"></i>
                        Reset
                    </button>
                </div>
            </form>
        </div>
    @endif
</div>

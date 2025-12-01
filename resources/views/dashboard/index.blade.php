@extends('layouts.app')

@section('header')
    <div class="mt-16 flex flex-col items-center text-center max-w-4xl mx-auto">
        <h1 class="text-4xl md:text-5xl text-white font-bold mb-4 animate-fade-in">
            Dashboard Saya
        </h1>
        <p class="text-white/95 text-lg mt-2 animate-fade-in" style="animation-delay: 0.1s;">
            Kelola booking dan poin reward Anda
        </p>
    </div>
@endsection

@section('content')
    <section class="pt-8 pb-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Points Balance Card -->
            <div class="bg-gradient-to-r from-emerald-600 to-emerald-700 rounded-xl shadow-lg p-8 mb-8 text-white animate-fade-in">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-emerald-100 text-sm mb-2">Poin Saya</p>
                        <h3 class="text-4xl font-bold mb-2">{{ number_format($user->points_balance) }}</h3>
                        <p class="text-emerald-100 text-sm">Setara dengan Rp {{ number_format(($user->points_balance / 100) * 1000, 0, ',', '.') }}</p>
                    </div>
                </div>
                <div class="mt-6 flex flex-wrap items-center gap-3">
                    <a href="{{ route('home') }}" class="bg-white text-emerald-700 px-6 py-3 rounded-lg font-semibold hover:bg-emerald-50 transition-colors inline-flex items-center gap-2">
                        <i class="ai-compass"></i>
                        Booking Sekarang
                    </a>
                    <a href="{{ route('profile.edit') }}" class="bg-white/20 backdrop-blur-sm text-white px-6 py-3 rounded-lg font-semibold hover:bg-white/30 transition-colors inline-flex items-center gap-2">
                        <i class="ai-person"></i>
                        Lihat Profil
                    </a>
                </div>
            </div>

            <!-- Tabs -->
            <div class="bg-white rounded-xl shadow-lg mb-6 overflow-hidden animate-fade-in" style="animation-delay: 0.1s;">
                <div class="flex border-b border-gray-200">
                    <a href="{{ route('dashboard', ['tab' => 'upcoming']) }}" 
                       class="flex-1 px-6 py-4 text-center font-semibold transition-colors {{ $tab === 'upcoming' ? 'bg-emerald-600 text-white' : 'text-gray-600 hover:bg-gray-50' }}">
                        <i class="ai-calendar {{ $tab === 'upcoming' ? '' : 'text-gray-400' }}"></i>
                        <span class="ml-2">Mendatang</span>
                    </a>
                    <a href="{{ route('dashboard', ['tab' => 'past']) }}" 
                       class="flex-1 px-6 py-4 text-center font-semibold transition-colors {{ $tab === 'past' ? 'bg-emerald-600 text-white' : 'text-gray-600 hover:bg-gray-50' }}">
                        <i class="ai-clock {{ $tab === 'past' ? '' : 'text-gray-400' }}"></i>
                        <span class="ml-2">Riwayat</span>
                    </a>
                    <a href="{{ route('dashboard', ['tab' => 'cancelled']) }}" 
                       class="flex-1 px-6 py-4 text-center font-semibold transition-colors {{ $tab === 'cancelled' ? 'bg-emerald-600 text-white' : 'text-gray-600 hover:bg-gray-50' }}">
                        <i class="ai-circle-x {{ $tab === 'cancelled' ? '' : 'text-gray-400' }}"></i>
                        <span class="ml-2">Dibatalkan</span>
                    </a>
                </div>
            </div>

            <!-- Bookings List -->
            @if ($bookings->isEmpty())
                <div class="bg-white rounded-xl shadow-lg p-16 text-center animate-fade-in" style="animation-delay: 0.2s;">
                    <div class="bg-emerald-50 w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="ai-calendar text-5xl text-emerald-600"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Belum Ada Booking</h3>
                    <p class="text-gray-600 text-lg mb-8">
                        @if ($tab === 'upcoming')
                            Anda belum memiliki booking yang akan datang
                        @elseif ($tab === 'past')
                            Anda belum memiliki riwayat booking
                        @else
                            Anda tidak memiliki booking yang dibatalkan
                        @endif
                    </p>
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-2 bg-emerald-600 text-white px-8 py-4 rounded-lg font-semibold hover:bg-emerald-700 transition-colors">
                        <i class="ai-plus"></i>
                        Buat Booking Baru
                    </a>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach ($bookings as $index => $booking)
                        <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transition-all overflow-hidden border border-gray-200 animate-fade-in" style="animation-delay: {{ ($index * 0.1) }}s;">
                            <!-- Booking Header with Gradient -->
                            <div class="bg-gradient-to-r from-emerald-600 to-emerald-700 p-5">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-2">
                                            <span class="text-emerald-100 text-xs font-medium">Booking ID:</span>
                                            <span class="bg-white/20 px-3 py-1 rounded-lg text-white font-mono text-sm font-bold">
                                                {{ $booking->booking_code }}
                                            </span>
                                        </div>
                                        <h4 class="font-bold text-xl text-white mb-1">{{ $booking->lapangan->title }}</h4>
                                        <p class="text-sm text-emerald-100 flex items-center gap-1">
                                            <i class="ai-tag"></i>
                                            {{ $booking->lapangan->category }}
                                        </p>
                                    </div>
                                    <div>
                                        @php
                                            // Check booking & payment status
                                            $isCancelled = $booking->status === 'cancelled';
                                            $isWaitingRefund = $isCancelled && $booking->cancelled_at && !$booking->refund_processed_at;
                                            $isPending = $booking->status === 'pending';
                                            $isConfirmed = $booking->status === 'confirmed';
                                        @endphp
                                        
                                        <span class="inline-block px-4 py-2 rounded-lg text-sm font-semibold shadow
                                            {{ $isPending ? 'bg-yellow-500 text-white' : '' }}
                                            {{ $isConfirmed ? 'bg-green-500 text-white' : '' }}
                                            {{ $isCancelled && $isWaitingRefund ? 'bg-orange-500 text-white' : '' }}
                                            {{ $isCancelled && !$isWaitingRefund ? 'bg-red-500 text-white' : '' }}
                                            {{ $booking->status === 'completed' ? 'bg-emerald-500 text-white' : '' }}">
                                            @if ($isPending) 
                                                ⏱ Pending
                                            @elseif ($isConfirmed) 
                                                ✓ Dikonfirmasi
                                            @elseif ($isCancelled && $isWaitingRefund) 
                                                ⏳ Menunggu Pembatalan
                                            @elseif ($isCancelled) 
                                                ✗ Dibatalkan
                                            @elseif ($booking->status === 'completed') 
                                                ✓ Selesai
                                            @else 
                                                ⏱ Menunggu
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Booking Details -->
                            <div class="p-6">
                                <div class="space-y-4 mb-5">
                                    <div class="flex items-center gap-3 text-gray-800">
                                        <div class="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center">
                                            <i class="ai-calendar text-emerald-600 text-lg"></i>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500">Tanggal</p>
                                            <p class="font-semibold">{{ \Carbon\Carbon::parse($booking->tanggal)->locale('id')->isoFormat('dddd, D MMM YYYY') }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3 text-gray-800">
                                        <div class="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center">
                                            <i class="ai-clock text-emerald-600 text-lg"></i>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500">Waktu</p>
                                            <p class="font-semibold">{{ substr($booking->jam_mulai, 0, 5) }} - {{ substr($booking->jam_selesai, 0, 5) }} WIB</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3 text-gray-800">
                                        <div class="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center">
                                            <i class="ai-credit-card text-emerald-600 text-lg"></i>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500">Harga</p>
                                            <p class="font-bold text-lg text-emerald-700">Rp {{ number_format($booking->lapangan->price, 0, ',', '.') }}</p>
                                        </div>
                                    </div>
                                </div>

                                @if ($booking->points_earned > 0)
                                    <div class="bg-gradient-to-r from-yellow-50 to-yellow-100 border-2 border-yellow-200 rounded-xl p-4 mb-4">
                                        <div class="flex items-center gap-2 text-yellow-800">
                                            <i class="ai-star text-yellow-600 text-xl"></i>
                                            <span class="text-sm font-bold">+{{ number_format($booking->points_earned) }} poin diperoleh! 🎉</span>
                                        </div>
                                    </div>
                                @endif

                                @if ($booking->points_redeemed > 0)
                                    <div class="bg-gradient-to-r from-green-50 to-green-100 border-2 border-green-200 rounded-xl p-4 mb-4">
                                        <div class="flex items-center gap-2 text-green-800">
                                            <i class="ai-check text-green-600 text-xl"></i>
                                            <span class="text-sm font-bold">{{ number_format($booking->points_redeemed) }} poin digunakan (-Rp {{ number_format(($booking->points_redeemed / 100) * 1000, 0, ',', '.') }})</span>
                                        </div>
                                    </div>
                                @endif

                                {{-- Payment Status --}}
                                @if($booking->payment_status)
                                    @if($booking->payment_status === 'unpaid')
                                        <div class="bg-gradient-to-r from-red-50 to-red-100 border-2 border-red-300 rounded-xl p-4 mb-4">
                                            <div class="flex items-start gap-3">
                                                <i class="ai-alert-triangle text-red-600 text-xl mt-0.5"></i>
                                                <div class="flex-1">
                                                    <h4 class="font-bold text-red-900 mb-1">❌ Belum Dibayar</h4>
                                                    <p class="text-sm text-red-700">
                                                        Silakan lakukan pembayaran untuk mengkonfirmasi booking Anda
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    @elseif($booking->payment_status === 'waiting_confirmation')
                                        <div class="bg-gradient-to-r from-yellow-50 to-yellow-100 border-2 border-yellow-300 rounded-xl p-4 mb-4">
                                            <div class="flex items-start gap-3">
                                                <i class="ai-clock text-yellow-600 text-xl mt-0.5"></i>
                                                <div class="flex-1">
                                                    <h4 class="font-bold text-yellow-900 mb-1">⏳ Menunggu Konfirmasi</h4>
                                                    <p class="text-sm text-yellow-700 mb-2">
                                                        Pembayaran Anda sedang diverifikasi oleh admin
                                                    </p>
                                                    @if($booking->payment_method)
                                                        <div class="bg-white rounded-lg p-3">
                                                            <p class="text-xs text-yellow-600 font-semibold mb-1">Metode Pembayaran:</p>
                                                            <p class="text-sm font-bold text-yellow-900">
                                                                @if($booking->payment_method === 'cash') 💵 Bayar di Tempat
                                                                @elseif($booking->payment_method === 'bank_transfer') 🏦 Transfer Bank
                                                                @elseif($booking->payment_method === 'qris') 📱 QRIS
                                                                @elseif($booking->payment_method === 'e_wallet') 💳 E-Wallet
                                                                @endif
                                                            </p>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @elseif($booking->payment_status === 'paid')
                                        <div class="bg-gradient-to-r from-green-50 to-green-100 border-2 border-green-300 rounded-xl p-4 mb-4">
                                            <div class="flex items-start gap-3">
                                                <i class="ai-check-circle text-green-600 text-xl mt-0.5"></i>
                                                <div class="flex-1">
                                                    <h4 class="font-bold text-green-900 mb-1">✅ Sudah Dibayar</h4>
                                                    <p class="text-sm text-green-700 mb-2">
                                                        Pembayaran berhasil dikonfirmasi
                                                    </p>
                                                    @if($booking->payment_method)
                                                        <div class="bg-white rounded-lg p-3">
                                                            <p class="text-xs text-green-600 font-semibold mb-1">Metode Pembayaran:</p>
                                                            <p class="text-sm font-bold text-green-900">
                                                                @if($booking->payment_method === 'cash') 💵 Bayar di Tempat
                                                                @elseif($booking->payment_method === 'bank_transfer') 🏦 Transfer Bank
                                                                @elseif($booking->payment_method === 'qris') 📱 QRIS
                                                                @elseif($booking->payment_method === 'e_wallet') 💳 E-Wallet
                                                                @endif
                                                            </p>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endif

                                @if ($booking->status === 'cancelled')
                                    @php
                                        $isWaitingRefund = $booking->cancelled_at && !$booking->refund_processed_at;
                                    @endphp
                                    
                                    @if ($isWaitingRefund)
                                        <!-- Waiting for Refund -->
                                        <div class="bg-gradient-to-r from-orange-50 to-orange-100 border-2 border-orange-300 rounded-xl p-4 mb-4">
                                            <div class="flex items-start gap-3">
                                                <i class="ai-clock text-orange-600 text-xl mt-0.5"></i>
                                                <div class="flex-1">
                                                    <h4 class="font-bold text-orange-900 mb-1">⏳ Menunggu Pembatalan</h4>
                                                    <p class="text-sm text-orange-700 mb-2">
                                                        Refund {{ $booking->refund_percentage }}% sedang diproses
                                                    </p>
                                                    <div class="bg-white rounded-lg p-3 mb-2">
                                                        <p class="text-xs text-orange-600 font-semibold mb-1">Jumlah Refund:</p>
                                                        <p class="text-lg font-bold text-orange-900">Rp {{ number_format($booking->refund_amount, 0, ',', '.') }}</p>
                                                        <p class="text-xs text-orange-600 mt-1">≈ {{ number_format(floor($booking->refund_amount / 1000)) }} poin</p>
                                                    </div>
                                                    @if ($booking->cancellation_reason)
                                                        <p class="text-xs text-orange-600 italic">
                                                            Alasan: {{ Str::limit($booking->cancellation_reason, 100) }}
                                                        </p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <!-- Refund Processed -->
                                        <div class="bg-gradient-to-r from-gray-50 to-gray-100 border-2 border-gray-300 rounded-xl p-4 mb-4">
                                            <div class="flex items-start gap-3">
                                                <i class="ai-check-circle text-gray-600 text-xl mt-0.5"></i>
                                                <div class="flex-1">
                                                    <h4 class="font-bold text-gray-900 mb-1">✓ Pembatalan Selesai</h4>
                                                    <p class="text-sm text-gray-700 mb-2">
                                                        Refund {{ $booking->refund_percentage }}% telah dikembalikan
                                                    </p>
                                                    <div class="bg-white rounded-lg p-3">
                                                        <p class="text-xs text-gray-600 font-semibold mb-1">Jumlah yang Dikembalikan:</p>
                                                        <p class="text-lg font-bold text-gray-900">Rp {{ number_format($booking->refund_amount, 0, ',', '.') }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endif

                                <!-- Actions -->
                                @if ($tab === 'upcoming' && in_array($booking->status, ['pending', 'confirmed']))
                                    <div class="mt-5 space-y-3">
                                        {{-- Download Invoice Button (for paid bookings) --}}
                                        @if($booking->payment_status === 'paid' && $booking->invoice)
                                            <div class="flex gap-2">
                                                <a href="{{ route('invoice.view', $booking->invoice->id) }}" 
                                                   class="flex-1 inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-black px-6 py-3 rounded-lg font-semibold transition-all shadow-md hover:shadow-lg">
                                                    <i class="ai-eye"></i>
                                                    Lihat Invoice
                                                </a>
                                                <a href="{{ route('invoice.download', $booking->invoice->id) }}" 
                                                   class="flex-1 inline-flex items-center justify-center gap-2 bg-green-600 hover:bg-green-700 text-black px-6 py-3 rounded-lg font-semibold transition-all shadow-md hover:shadow-lg">
                                                    <i class="ai-download"></i>
                                                    Download PDF
                                                </a>
                                            </div>
                                        @endif
                                        
                                        {{-- Payment Button --}}
                                        @if($booking->payment_status !== 'paid')
                                            @livewire('payment-form', ['bookingId' => $booking->id], key('payment-' . $booking->id))
                                        @endif
                                        
                                        {{-- Cancel Button --}}
                                        @livewire('cancel-booking', ['bookingId' => $booking->id], key('cancel-' . $booking->id))
                                    </div>
                                @elseif($tab === 'past' && $booking->invoice)
                                    {{-- Past bookings: show download invoice button --}}
                                    <div class="mt-5 flex gap-2">
                                        <a href="{{ route('invoice.view', $booking->invoice->id) }}" 
                                           class="flex-1 inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition-all shadow-md hover:shadow-lg">
                                            <i class="ai-eye"></i>
                                            Lihat Invoice
                                        </a>
                                        <a href="{{ route('invoice.download', $booking->invoice->id) }}" 
                                           class="flex-1 inline-flex items-center justify-center gap-2 bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold transition-all shadow-md hover:shadow-lg">
                                            <i class="ai-download"></i>
                                            Download PDF
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
                
                {{-- Pagination Links --}}
                <div class="mt-8 flex justify-center">
                    {{ $bookings->appends(['tab' => $tab])->links() }}
                </div>
            @endif
        </div>
    </section>
@endsection
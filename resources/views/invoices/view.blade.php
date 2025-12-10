@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-emerald-50 via-white to-blue-50 py-8 sm:py-12">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header Actions -->
        <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 text-emerald-600 hover:text-emerald-800 font-semibold transition-colors">
                <i class="ai-arrow-left text-xl"></i>
                Kembali ke Dashboard
            </a>
            
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('invoice.stream', $invoice->id) }}"
                   target="_blank"
                   class="inline-flex items-center gap-2 px-5 py-2.5 bg-white border-2 border-emerald-500 text-emerald-700 rounded-xl hover:bg-emerald-50 transition-all shadow-sm hover:shadow font-semibold">
                    <i class="ai-eye"></i>
                    Preview PDF
                </a>
                
                <a href="{{ route('invoice.download', $invoice->id) }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 text-white rounded-xl hover:bg-emerald-700 transition-all shadow-md hover:shadow-lg font-semibold">
                    <i class="ai-download"></i>
                    Download PDF
                </a>
            </div>
        </div>

        <!-- Invoice Preview Card -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
            <!-- Header dengan Gradient -->
            <div class="bg-gradient-to-r from-emerald-600 to-emerald-700 p-6 sm:p-8">
                <div class="flex flex-col sm:flex-row justify-between items-start gap-6">
                    <div class="text-white">
                        <h1 class="text-3xl sm:text-4xl font-bold mb-2">GoField</h1>
                        <p class="text-emerald-100 text-sm mb-4">Platform Booking Lapangan Olahraga</p>
                        <div class="space-y-1.5 text-sm text-emerald-50">
                            <p class="flex items-center gap-2">
                                <i class="ai-location"></i>
                                Jl. Olahraga No. 123, Jakarta
                            </p>
                            <p class="flex items-center gap-2">
                                <i class="ai-phone"></i>
                                (021) 1234-5678
                            </p>
                            <p class="flex items-center gap-2">
                                <i class="ai-envelope"></i>
                                info@gofield.com
                            </p>
                        </div>
                    </div>
                    <div class="text-right bg-white/10 backdrop-blur-sm rounded-xl p-5 border border-white/20">
                        <h2 class="text-2xl font-bold text-white mb-2">INVOICE</h2>
                        <p class="text-white font-mono text-lg font-bold mb-1">{{ $invoice->invoice_number }}</p>
                        <p class="text-sm text-emerald-100 mb-3">{{ \Carbon\Carbon::parse($invoice->created_at)->locale('id')->isoFormat('D MMMM YYYY') }}</p>
                        <div>
                            @if($invoice->status === 'paid')
                                <span class="inline-flex items-center gap-1.5 px-4 py-1.5 bg-green-500 text-white text-sm font-bold rounded-lg shadow-lg">
                                    <i class="ai-check-circle"></i>
                                    LUNAS
                                </span>
                            @elseif($invoice->status === 'pending')
                                <span class="inline-flex items-center gap-1.5 px-4 py-1.5 bg-yellow-500 text-white text-sm font-bold rounded-lg shadow-lg">
                                    <i class="ai-clock"></i>
                                    PENDING
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-4 py-1.5 bg-red-500 text-white text-sm font-bold rounded-lg shadow-lg">
                                    <i class="ai-cross-circle"></i>
                                    DIBATALKAN
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-6 sm:p-8">
                <!-- Customer & Payment Info -->
                <div class="grid md:grid-cols-2 gap-6 mb-8">
                    <div class="bg-gradient-to-br from-emerald-50 to-blue-50 p-6 rounded-xl border border-emerald-100">
                        <div class="flex items-center gap-2 mb-4">
                            <div class="w-10 h-10 bg-emerald-600 rounded-lg flex items-center justify-center">
                                <i class="ai-person text-white text-xl"></i>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900">Informasi Pemesan</h3>
                        </div>
                        <div class="space-y-3">
                            <div>
                                <p class="text-xs text-gray-500 font-semibold uppercase mb-1">Nama</p>
                                <p class="text-gray-900 font-semibold">{{ $invoice->booking->nama_pemesan }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 font-semibold uppercase mb-1">Email</p>
                                <p class="text-gray-900">{{ $invoice->booking->email }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 font-semibold uppercase mb-1">Telepon</p>
                                <p class="text-gray-900 font-mono">{{ $invoice->booking->nomor_telepon }}</p>
                            </div>
                            @if($invoice->booking->user)
                            <div>
                                <p class="text-xs text-gray-500 font-semibold uppercase mb-1">User ID</p>
                                <p class="text-gray-900">#{{ $invoice->booking->user_id }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                    
                    <div class="bg-gradient-to-br from-blue-50 to-purple-50 p-6 rounded-xl border border-blue-100">
                        <div class="flex items-center gap-2 mb-4">
                            <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                                <i class="ai-credit-card text-white text-xl"></i>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900">Informasi Pembayaran</h3>
                        </div>
                        <div class="space-y-3">
                            @if($invoice->payment_date)
                            <div>
                                <p class="text-xs text-gray-500 font-semibold uppercase mb-1">Tanggal Bayar</p>
                                <p class="text-gray-900 font-semibold">{{ \Carbon\Carbon::parse($invoice->payment_date)->locale('id')->isoFormat('D MMMM YYYY, HH:mm') }}</p>
                            </div>
                            @endif
                            @if($invoice->payment_method)
                            <div>
                                <p class="text-xs text-gray-500 font-semibold uppercase mb-1">Metode Pembayaran</p>
                                <p class="text-gray-900">
                                    @if($invoice->payment_method === 'cash') 💵 Bayar di Tempat
                                    @elseif($invoice->payment_method === 'bank_transfer') 🏦 Transfer Bank
                                    @elseif($invoice->payment_method === 'qris') 📱 QRIS
                                    @elseif($invoice->payment_method === 'e_wallet') 💳 E-Wallet
                                    @else {{ ucfirst(str_replace('_', ' ', $invoice->payment_method)) }}
                                    @endif
                                </p>
                            </div>
                            @endif
                            <div>
                                <p class="text-xs text-gray-500 font-semibold uppercase mb-1">Status Pembayaran</p>
                                <p class="font-bold text-lg {{ $invoice->status === 'paid' ? 'text-green-600' : 'text-yellow-600' }}">
                                    {{ $invoice->status === 'paid' ? '✓ LUNAS' : '⏱ PENDING' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Booking Details -->
                <div class="mb-8">
                    <div class="flex items-center gap-2 mb-5">
                        <div class="w-10 h-10 bg-emerald-600 rounded-lg flex items-center justify-center">
                            <i class="ai-calendar text-white text-xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900">Detail Booking</h3>
                    </div>
                    
                    <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl p-6 border border-gray-200">
                        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-4">
                            <div class="md:col-span-2">
                                <p class="text-xs text-gray-500 font-semibold uppercase mb-2">Kode Booking</p>
                                <div class="flex items-center gap-2">
                                    <i class="ai-ticket text-emerald-600 text-xl"></i>
                                    <p class="font-mono font-bold text-lg text-emerald-600">{{ $invoice->booking->booking_code }}</p>
                                </div>
                            </div>
                            <div class="md:col-span-3">
                                <p class="text-xs text-gray-500 font-semibold uppercase mb-2">Lapangan</p>
                                <div class="flex items-center gap-2">
                                    <i class="ai-location text-gray-600 text-xl"></i>
                                    <p class="font-bold text-lg text-gray-900">{{ $invoice->booking->lapangan->title }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-4 border-t border-gray-300">
                            <div>
                                <p class="text-xs text-gray-500 font-semibold uppercase mb-2">Tanggal</p>
                                <div class="flex items-center gap-2">
                                    <i class="ai-calendar text-blue-600"></i>
                                    <p class="text-gray-900 font-semibold">{{ \Carbon\Carbon::parse($invoice->booking->tanggal)->locale('id')->isoFormat('dddd, D MMMM YYYY') }}</p>
                                </div>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 font-semibold uppercase mb-2">Waktu</p>
                                <div class="flex items-center gap-2">
                                    <i class="ai-clock text-orange-600"></i>
                                    <p class="text-gray-900 font-semibold">{{ \Carbon\Carbon::parse($invoice->booking->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($invoice->booking->jam_selesai)->format('H:i') }} WIB</p>
                                </div>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 font-semibold uppercase mb-2">Durasi</p>
                                <div class="flex items-center gap-2">
                                    <i class="ai-hourglass text-purple-600"></i>
                                    <p class="text-gray-900 font-semibold">{{ $invoice->booking->duration }} Jam</p>
                                </div>
                            </div>
                        </div>

                        @if($invoice->booking->lapangan->sportType || $invoice->booking->lapangan->location)
                        <div class="mt-4 pt-4 border-t border-gray-300 space-y-2">
                            @if($invoice->booking->lapangan->location)
                            <p class="text-sm text-gray-700">
                                <i class="ai-location text-emerald-600"></i>
                                <strong>Lokasi:</strong> {{ $invoice->booking->lapangan->location }}
                            </p>
                            @endif
                            @if($invoice->booking->lapangan->sportType)
                            <p class="text-sm text-gray-700">
                                <i class="ai-tag text-blue-600"></i>
                                <strong>Kategori:</strong> {{ $invoice->booking->lapangan->sportType->name }}
                            </p>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Payment Summary -->
                <div class="mb-8">
                    <div class="flex items-center gap-2 mb-5">
                        <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                            <i class="ai-credit-card text-white text-xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900">Rincian Pembayaran</h3>
                    </div>
                    
                    <div class="bg-gradient-to-br from-blue-50 to-purple-50 rounded-xl p-6 border border-blue-200">
                        <div class="space-y-3">
                            <div class="flex justify-between items-center py-3 border-b border-gray-300">
                                <span class="text-gray-700 font-semibold">Subtotal</span>
                                <span class="text-xl font-bold text-gray-900">Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</span>
                            </div>
                            
                            @if($invoice->discount > 0)
                            <div class="flex justify-between items-center py-3 border-b border-gray-300">
                                <div>
                                    <p class="text-gray-700 font-semibold">Diskon</p>
                                </div>
                                <span class="text-xl font-bold text-green-600">- Rp {{ number_format($invoice->discount, 0, ',', '.') }}</span>
                            </div>
                            @endif
                            
                            <div class="flex justify-between items-center py-4 bg-gradient-to-r from-emerald-600 to-emerald-700 rounded-xl px-6 mt-4 shadow-lg">
                                <span class="text-white font-bold text-lg">TOTAL PEMBAYARAN</span>
                                <span class="text-white font-bold text-2xl">Rp {{ number_format($invoice->total, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Booking Code Section -->
                <div class="mb-8">
                    <div class="bg-emerald-600 rounded-2xl p-8 text-center shadow-2xl border-4 border-white">
                        <div class="bg-white/20 backdrop-blur-sm rounded-xl p-6 border-2 border-white/30">
                            <div class="flex items-center justify-center gap-3 mb-4">
                                <i class="ai-ticket text-white text-3xl"></i>
                                <h3 class="text-2xl font-bold text-white">KODE BOOKING</h3>
                            </div>
                            <div class="bg-white rounded-xl p-5 mb-4 shadow-xl">
                                <p class="text-4xl sm:text-5xl font-bold text-emerald-600 font-mono tracking-wider">
                                    {{ $invoice->booking->booking_code }}
                                </p>
                            </div>
                            <p class="text-white text-sm sm:text-base leading-relaxed">
                                <i class="ai-info-circle"></i>
                                Tunjukkan kode booking ini kepada petugas saat Anda tiba di lokasi.<br>
                                Simpan invoice ini sebagai bukti pembayaran yang sah.
                            </p>
                        </div>
                    </div>
                </div>

                @if($invoice->notes)
                <!-- Notes -->
                <div class="mb-6 bg-gradient-to-r from-yellow-50 to-orange-50 border-l-4 border-yellow-500 rounded-xl p-5 shadow-sm">
                    <div class="flex items-start gap-3">
                        <i class="ai-info text-yellow-600 text-2xl mt-0.5"></i>
                        <div>
                            <h3 class="font-bold text-yellow-900 mb-2 text-lg">Catatan Penting</h3>
                            <p class="text-yellow-800">{{ $invoice->notes }}</p>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Terms & Conditions -->
                <div class="mb-6 bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl p-6 border border-gray-200 shadow-sm">
                    <div class="flex items-center gap-2 mb-4">
                        <i class="ai-alert-circle text-gray-700 text-2xl"></i>
                        <h3 class="font-bold text-gray-800 text-lg">Syarat & Ketentuan</h3>
                    </div>
                    <ul class="space-y-3 text-sm text-gray-700">
                        <li class="flex items-start gap-3 bg-white p-3 rounded-lg">
                            <i class="ai-check text-emerald-600 mt-0.5 flex-shrink-0"></i>
                            <span>Invoice ini adalah bukti pembayaran yang sah untuk booking lapangan.</span>
                        </li>
                        <li class="flex items-start gap-3 bg-white p-3 rounded-lg">
                            <i class="ai-check text-emerald-600 mt-0.5 flex-shrink-0"></i>
                            <span>Harap datang <strong>15 menit sebelum</strong> waktu booking untuk check-in.</span>
                        </li>
                        <li class="flex items-start gap-3 bg-white p-3 rounded-lg">
                            <i class="ai-check text-emerald-600 mt-0.5 flex-shrink-0"></i>
                            <span>Bawa kartu identitas dan tunjukkan invoice ini kepada petugas.</span>
                        </li>
                        <li class="flex items-start gap-3 bg-white p-3 rounded-lg">
                            <i class="ai-check text-emerald-600 mt-0.5 flex-shrink-0"></i>
                            <span>Pembatalan booking dapat dilakukan maksimal <strong>H-1</strong> untuk pengembalian dana.</span>
                        </li>
                        <li class="flex items-start gap-3 bg-white p-3 rounded-lg">
                            <i class="ai-check text-emerald-600 mt-0.5 flex-shrink-0"></i>
                            <span>Untuk pertanyaan, hubungi customer service: <strong>(021) 1234-5678</strong></span>
                        </li>
                    </ul>
                </div>

                <!-- Footer -->
                <div class="border-t-2 border-gray-200 pt-6 text-center">
                    <div class="bg-gradient-to-r from-emerald-50 to-blue-50 rounded-xl p-5 mb-4">
                        <p class="text-gray-700 mb-2 font-semibold">
                            <i class="ai-heart text-red-500"></i>
                            Terima kasih telah menggunakan layanan GoField!
                        </p>
                        <p class="text-sm text-gray-600">
                            Invoice ini digenerate secara otomatis pada {{ now()->locale('id')->isoFormat('D MMMM YYYY, HH:mm') }} WIB
                        </p>
                    </div>
                    <div class="space-y-2">
                        <div class="flex items-center justify-center gap-6 text-sm text-gray-600 flex-wrap">
                            <a href="#" class="hover:text-emerald-600 transition-colors flex items-center gap-1">
                                <i class="ai-globe"></i>
                                www.gofield.com
                            </a>
                            <a href="mailto:info@gofield.com" class="hover:text-emerald-600 transition-colors flex items-center gap-1">
                                <i class="ai-envelope"></i>
                                info@gofield.com
                            </a>
                            <a href="tel:02112345678" class="hover:text-emerald-600 transition-colors flex items-center gap-1">
                                <i class="ai-phone"></i>
                                (021) 1234-5678
                            </a>
                        </div>
                        <p class="text-xs text-black mt-3">
                            GoField © {{ date('Y') }} - Platform Booking Lapangan Olahraga Terpercaya
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

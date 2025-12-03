@extends('layouts.app')

@section('header')
    <div class="mt-16 flex flex-col items-center text-center max-w-4xl mx-auto">
        <div class="inline-block mb-6">
            <span class="px-4 py-2 bg-white/10 backdrop-blur-sm text-white rounded-full text-sm font-medium">
                Platform Booking Lapangan #1 di Indonesia
            </span>
        </div>
        <h1 class="text-4xl md:text-6xl text-white font-bold mb-6 animate-fade-in">
            Booking Lapangan Olahraga<br>
            <span class="text-emerald-200">Cepat & Mudah</span>
        </h1>
        <p class="text-white/90 text-lg md:text-xl mb-8 max-w-2xl animate-fade-in" style="animation-delay: 0.1s;">
            Temukan dan booking lapangan futsal, basket, badminton, tenis, dan voli dengan mudah. Harga terjangkau, fasilitas lengkap.
        </p>
        <div class="flex flex-wrap justify-center gap-4 animate-fade-in" style="animation-delay: 0.2s;">
            <a href="#lapangan" class="px-6 py-3 bg-white text-emerald-700 font-semibold rounded-lg hover:bg-gray-50 transition-colors inline-flex items-center gap-2">
                <i class="ai-compass"></i>
                Lihat Lapangan
            </a>
            <a href="#cara-booking" class="px-6 py-3 bg-white/10 backdrop-blur-sm text-white font-semibold rounded-lg border border-white/20 hover:bg-white/20 transition-colors inline-flex items-center gap-2">
                <i class="ai-book"></i>
                Cara Booking
            </a>
        </div>
    </div>
@endsection

@section('content')
    <section class="pt-16 md:pt-24 pb-10" id="lapangan">
        <div class="container mx-auto">
            <div class="text-center mb-12">
                <span class="badge inline-flex items-center gap-2 text-base animate-fade-in">
                    <i class="ai-grid"></i> Pilih Lapangan Favorit
                </span>
                <h2 class="section-title text-center mx-auto animate-fade-in" style="animation-delay: 0.1s;">Pilih Lapangan Olahraga</h2>
                <p class="text-gray-600 mt-4 text-lg max-w-2xl mx-auto animate-fade-in" style="animation-delay: 0.2s;">
                    Temukan lapangan olahraga terbaik dengan fasilitas lengkap dan harga bersaing. Futsal, Basket, Voli, Badminton, Tennis, dan masih banyak lagi.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-10">
                @foreach ($lapangan as $index => $item)
                    @php
                        $itemImages = $item->images;
                        $primaryImage = is_array($itemImages) && count($itemImages) > 0 ? $itemImages[0] : 'laravel/public/storage/default-lapangan.png';
                        
                        // Icon mapping untuk setiap kategori
                        $categoryIcons = [
                            'Futsal' => 'ai-circle',
                            'Badminton' => 'ai-grid',
                            'Basket' => 'ai-circle',
                            'Volly' => 'ai-grid',
                            'Tennis' => 'ai-circle',
                        ];
                        $categoryIcon = $categoryIcons[$item->sportType?->name ?? ''] ?? 'ai-star-fill';
                    @endphp
                    <a href="{{ route('detail', $item->id) }}"
                        class="group bg-white border-2 border-gray-100 rounded-2xl transition-all duration-500 hover:border-primary overflow-hidden hover:shadow-2xl hover:-translate-y-2 animate-fade-in"
                        style="animation-delay: {{ $index * 0.1 }}s;">
                        
                        <!-- Image with overlay -->
                        <div class="relative overflow-hidden">
                            <img src="{{ asset('storage/' . $primaryImage) }}"
                                 alt="Foto {{ $item->title }}"
                                 class="w-full h-64 object-cover transition-transform duration-500 group-hover:scale-110">
                            
                            <!-- Gradient overlay -->
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            
                            <!-- Category badge on image -->
                            <div class="absolute top-4 right-4 bg-white/95 backdrop-blur-sm px-3 py-1.5 rounded-full text-sm font-semibold text-primary flex items-center gap-1 shadow-lg">
                                <i class="{{ $categoryIcon }} text-yellow-400"></i>
                                {{ $item->sportType?->name ?? 'Sport' }}
                            </div>
                            
                            <!-- Quick view button -->
                            <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                <span class="bg-white text-primary px-6 py-3 rounded-xl font-semibold shadow-xl transform scale-90 group-hover:scale-100 transition-transform duration-300 flex items-center gap-2">
                                    <i class="ai-eye"></i>
                                    Lihat Detail
                                </span>
                            </div>
                        </div>
                        
                        <div class="p-6">
                            <h4 class="text-xl font-bold mt-2 text-gray-900 group-hover:text-primary transition-colors duration-300">
                                {{ $item->title }}
                            </h4>
                            <div class="flex items-center justify-between mt-4">
                                <div>
                                    <p class="text-gray-500 text-sm">
                                        @if($item->weekday_price || $item->weekend_price)
                                            <span class="text-xs">Weekday/Weekend</span>
                                        @else
                                            <span>Mulai dari</span>
                                        @endif
                                    </p>
                                    <p class="text-2xl font-bold text-primary">
                                        @if($item->weekday_price && $item->weekend_price)
                                            Rp {{ number_format($item->weekday_price, 0, ',', '.') }} - {{ number_format($item->weekend_price, 0, ',', '.') }}
                                        @elseif($item->weekday_price)
                                            Rp {{ number_format($item->weekday_price, 0, ',', '.') }}
                                        @elseif($item->weekend_price)
                                            Rp {{ number_format($item->weekend_price, 0, ',', '.') }}
                                        @else
                                            Rp {{ number_format($item->price, 0, ',', '.') }}
                                        @endif
                                        <span class="text-sm font-normal text-gray-500">/jam</span>
                                    </p>
                                    @if($item->peak_hour_start && $item->peak_hour_end)
                                        <p class="text-xs text-orange-600 mt-1">
                                            ⚡ Peak {{ substr($item->peak_hour_start, 0, 5) }}-{{ substr($item->peak_hour_end, 0, 5) }} ({{ $item->peak_hour_multiplier }}x)
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-12 flex justify-center animate-fade-in">
                {{ $lapangan->links() }}
            </div>
        </div>
    </section>

    <section class="pt-16 md:pt-24 pb-10 bg-gradient-to-b from-gray-50 to-white" id="cara-booking">
        <div class="container mx-auto">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Cara Booking</h2>
                <p class="text-gray-600 text-lg max-w-2xl mx-auto">
                    Hanya 3 langkah mudah untuk booking lapangan impian Anda
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-10">
                <div class="bg-white rounded-xl p-8 text-center shadow-md border border-gray-200">
                    <div class="text-5xl font-bold text-emerald-600 mb-4">1</div>
                    <h5 class="font-bold text-xl text-gray-900 mb-2">Pilih Lapangan</h5>
                    <p class="text-gray-600">Pilih lapangan sesuai kebutuhan dan budget Anda</p>
                </div>
                
                <div class="bg-white rounded-xl p-8 text-center shadow-md border border-gray-200">
                    <div class="text-5xl font-bold text-emerald-600 mb-4">2</div>
                    <h5 class="font-bold text-xl text-gray-900 mb-2">Pilih Tanggal & Jam</h5>
                    <p class="text-gray-600">Tentukan waktu yang sesuai dengan jadwal Anda</p>
                </div>
                
                <div class="bg-white rounded-xl p-8 text-center shadow-md border border-gray-200">
                    <div class="text-5xl font-bold text-emerald-600 mb-4">3</div>
                    <h5 class="font-bold text-xl text-gray-900 mb-2">Isi Data Diri</h5>
                    <p class="text-gray-600">Lengkapi data dan konfirmasi booking Anda</p>
                </div>
            </div>
        </div>
    </section>

    <section class="pt-16 md:pt-24 pb-10">
        <div class="container mx-auto">
            <div class="text-center mb-12">
                <span class="badge inline-flex items-center gap-2 text-base animate-fade-in">
                    <i class="ai-image"></i> Galeri Lapangan
                </span>
                <h2 class="section-title text-center mx-auto animate-fade-in" style="animation-delay: 0.1s;">Galeri Kami</h2>
                <p class="text-gray-600 mt-4 text-lg max-w-2xl mx-auto animate-fade-in" style="animation-delay: 0.2s;">
                    Lihat fasilitas dan suasana lapangan kami
                </p>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-10">
                @foreach(['galeri-1.jpg', 'galeri-2.jpg', 'galeri-3.jpg', 'galeri-4.jpg', 'galeri-5.jpg', 'galeri-6.jpg', 'lapangan-1.png', 'lapangan-2.png'] as $index => $img)
                    <div class="group relative overflow-hidden rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-500 animate-fade-in cursor-pointer" style="animation-delay: {{ $index * 0.05 }}s;">
                        <img src="{{ asset('images/' . $img) }}"
                             alt="Galeri {{ $index + 1 }}"
                             class="w-full h-32 md:h-48 object-cover transition-transform duration-700 group-hover:scale-125">
                        
                        <!-- Overlay with zoom icon -->
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent opacity-0 group-hover:opacity-100 transition-all duration-300 flex items-center justify-center">
                            <div class="transform scale-75 group-hover:scale-100 transition-transform duration-300">
                                <i class="ai-search text-white text-3xl"></i>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="py-16 md:py-24 bg-gradient-to-br from-gray-50 via-white to-gray-50">
        <div class="container mx-auto">
            <div class="text-center mb-12">
                <span class="badge inline-flex items-center gap-2 text-base animate-fade-in">
                    <i class="ai-phone"></i> Hubungi Kami
                </span>
                <h2 class="section-title text-center mx-auto animate-fade-in" style="animation-delay: 0.1s;">Kontak Kami</h2>
                <p class="text-gray-600 mt-4 text-lg max-w-2xl mx-auto animate-fade-in" style="animation-delay: 0.2s;">
                    Ada pertanyaan? Kami siap membantu Anda kapan saja
                </p>
            </div>

            <div class="flex flex-col md:flex-row gap-8 mt-10">
                <div class="w-full md:w-5/12 space-y-4">
                    <!-- WhatsApp -->
                    <div class="group bg-white rounded-2xl p-6 shadow-lg hover:shadow-2xl transition-all duration-500 hover:-translate-y-1 border-2 border-transparent hover:border-primary/20 animate-fade-in cursor-pointer" style="animation-delay: 0.1s;">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-green-400 to-green-600 text-white flex items-center justify-center group-hover:scale-110 group-hover:rotate-6 transition-all duration-300 shadow-lg">
                                <i class="ai-whatsapp-fill text-2xl"></i>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-gray-600 mb-1">WhatsApp</p>
                                <p class="text-lg font-bold text-gray-900 group-hover:text-primary transition-colors duration-300">+62 812 3456 789</p>
                            </div>
                            <i class="ai-arrow-right text-gray-400 group-hover:text-primary group-hover:translate-x-1 transition-all duration-300"></i>
                        </div>
                    </div>
                    
                    <!-- Email -->
                    <div class="group bg-white rounded-xl p-6 shadow-lg hover:shadow-xl transition-all border border-gray-200 animate-fade-in cursor-pointer" style="animation-delay: 0.2s;">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-lg bg-gradient-to-br from-emerald-400 to-emerald-600 text-white flex items-center justify-center transition-all shadow">
                                <i class="ai-envelope text-2xl"></i>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-gray-600 mb-1">Email</p>
                                <p class="text-lg font-semibold text-gray-900">info@gofield.com</p>
                            </div>
                            <i class="ai-arrow-right text-gray-400"></i>
                        </div>
                    </div>
                    
                    <!-- Address -->
                    <div class="group bg-white rounded-2xl p-6 shadow-lg hover:shadow-2xl transition-all duration-500 hover:-translate-y-1 border-2 border-transparent hover:border-primary/20 animate-fade-in cursor-pointer" style="animation-delay: 0.3s;">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-red-400 to-red-600 text-white flex items-center justify-center group-hover:scale-110 group-hover:rotate-6 transition-all duration-300 shadow-lg">
                                <i class="ai-location text-2xl"></i>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-gray-600 mb-1">Alamat</p>
                                <p class="text-lg font-bold text-gray-900 group-hover:text-primary transition-colors duration-300">Paskal Hyper Square, Jl. Pasir Kaliki No.25-27</p>
                                <p class="text-sm text-gray-600">Ciroyom, Kec. Andir, Kota Bandung</p>
                            </div>
                            <i class="ai-arrow-right text-gray-400 group-hover:text-primary group-hover:translate-x-1 transition-all duration-300"></i>
                        </div>
                    </div>
                    
                    <!-- Operating Hours -->
                    <div class="bg-gradient-to-br from-primary/10 to-primary/5 rounded-2xl p-6 border-2 border-primary/20 animate-fade-in" style="animation-delay: 0.4s;">
                        <div class="flex items-start gap-3">
                            <div class="w-12 h-12 rounded-xl bg-primary/20 text-primary flex items-center justify-center mt-1">
                                <i class="ai-clock text-xl"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-700 mb-2">Jam Operasional</p>
                                <p class="text-base font-bold text-gray-900">Senin - Minggu</p>
                                <p class="text-lg font-bold text-primary">06:00 - 21:00 WIB</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="w-full md:w-7/12 animate-fade-in" style="animation-delay: 0.5s;">
                    <div class="rounded-2xl overflow-hidden shadow-2xl border-4 border-white hover:scale-[1.02] transition-transform duration-500">
                        <iframe
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3960.9007442850597!2d107.59746431431803!3d-6.902301269491937!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e68e64c7e8ed5d1%3A0x4a5d5e8c8c8c8c8c!2sBINUS%20%40%20Bandung!5e0!3m2!1sen!2sid!4v1733025600000!5m2!1sen!2sid"
                            class="w-full h-[500px]" style="border:0;" allowfullscreen="" loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
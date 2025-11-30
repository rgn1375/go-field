@extends('layouts.app')

@section('header')
    <div class="mt-16 md:mt-24 mb-10 animate-fade-in">
        <div class="inline-block mb-4">
            <span class="px-4 py-2 bg-white/20 backdrop-blur-sm text-white rounded-full text-sm font-semibold border border-white/30">
                <i class="ai-location mr-1"></i> Detail Lapangan
            </span>
        </div>
        <h1 class="text-center text-4xl md:text-6xl text-white font-extrabold leading-tight" style="text-shadow: 0 4px 20px rgba(0,0,0,0.3);">
            Booking <span class="text-emerald-200">Sekarang</span>
        </h1>
        <p class="text-white/90 text-lg mt-4 max-w-2xl mx-auto" style="text-shadow: 0 2px 10px rgba(0,0,0,0.3);">
            Pilih jadwal dan konfirmasi booking Anda
        </p>
    </div>
@endsection

@section('content')
    <style>
        .rich-text ol { list-style: decimal; padding-left: 1.25rem; }
        .rich-text ul { list-style: disc; padding-left: 1.25rem; }
        .rich-text li { margin: 0.25rem 0; }
        .rich-text p { margin: 0.5rem 0; line-height: 1.7; }
        .rich-text strong { font-weight: 600; color: #1f2937; }
        
        /* Force container width and prevent body overflow */
        body { overflow-x: hidden !important; }
        .booking-container { max-width: 100vw; overflow-x: hidden; }
    </style>
    <section class="py-12 md:py-20 booking-container">
        <div class="max-w-7xl mx-auto px-4">
            @php
                $imagesRaw = $lapangan->images ?? $lapangan->image ?? [];
                if (is_string($imagesRaw)) {
                    $decoded = json_decode($imagesRaw, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $imagesRaw = $decoded;
                    } else {
                        $imagesRaw = [trim($imagesRaw)];
                    }
                }
                $all = is_array($imagesRaw) ? array_values(array_filter($imagesRaw)) : [];
                $images = array_slice($all, 0, 3);
                $primaryImage = $images[0] ?? 'default-lapangan.png';
            @endphp
            
            <!-- Image Gallery -->
            <div class="flex flex-col md:flex-row gap-4 mb-10 animate-fade-in">
                <div class="w-full md:w-8/12 group relative overflow-hidden rounded-2xl shadow-2xl">
                    <img src="{{ asset('storage/' . $primaryImage) }}" alt="Primary"
                        class="rounded-2xl h-64 md:h-[450px] w-full object-cover transition-transform duration-700 group-hover:scale-110">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                </div>
                <div class="w-full md:w-4/12">
                    <div class="grid grid-cols-2 md:grid-cols-1 gap-4">
                        @if (count($images) > 1)
                            @foreach (array_slice($images, 1) as $index => $img)
                                <div class="group relative overflow-hidden rounded-2xl shadow-lg animate-fade-in" style="animation-delay: {{ ($index + 1) * 0.1 }}s;">
                                    <img src="{{ asset('storage/' . $img) }}" alt="Gallery {{ $index + 2 }}" 
                                        class="rounded-2xl w-full h-32 md:h-[213px] object-cover transition-transform duration-700 group-hover:scale-110">
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>

            <livewire:booking-form-new :lapanganId="$lapangan->id"/>
        </div>
    </section>
@endsection
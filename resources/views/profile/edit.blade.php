@extends('layouts.app')

@section('header')
    <div class="mt-16 flex flex-col items-center text-center max-w-4xl mx-auto">
        <h1 class="text-4xl md:text-5xl text-white font-bold mb-4 animate-fade-in">
            Profil Saya
        </h1>
        <p class="text-white/95 text-lg mt-2 animate-fade-in" style="animation-delay: 0.1s;">
            Kelola informasi pribadi dan riwayat poin Anda
        </p>
    </div>
@endsection

@section('content')
    <section class="pt-8 pb-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="p-8 bg-white shadow-lg rounded-xl animate-fade-in">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-8 bg-white shadow-lg rounded-2xl animate-fade-in" style="animation-delay: 0.1s;">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            @if(!auth()->user()->is_admin)
                <div class="p-8 bg-white shadow-lg rounded-2xl animate-fade-in" style="animation-delay: 0.4s;">
                    <div class="max-w-xl">
                        @include('profile.partials.delete-user-form')
                    </div>
                </div>
            @endif
        </div>
    </section>
@endsection

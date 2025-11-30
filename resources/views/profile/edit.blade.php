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
            <!-- Points Balance Card -->
            <div class="bg-gradient-to-r from-emerald-600 to-emerald-700 rounded-xl shadow-lg p-8 animate-fade-in">
                <div class="flex items-center justify-between text-white">
                    <div>
                        <p class="text-emerald-100 text-sm mb-2">Total Poin Saya</p>
                        <h3 class="text-4xl font-bold mb-2">{{ number_format(auth()->user()->points_balance) }}</h3>
                        <p class="text-emerald-100 text-sm">Setara dengan Rp {{ number_format((auth()->user()->points_balance / 100) * 1000, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            <div class="p-8 bg-white shadow-lg rounded-xl animate-fade-in" style="animation-delay: 0.1s;">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <!-- Points History -->
            <div class="p-8 bg-white shadow-lg rounded-xl animate-fade-in" style="animation-delay: 0.2s;">
                <div class="max-w-4xl">
                    <h3 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                        <i class="ai-clock text-emerald-600"></i>
                        Riwayat Poin
                    </h3>

                    @php
                        $transactions = auth()->user()->pointTransactions()->with('booking.lapangan')->orderBy('created_at', 'desc')->limit(10)->get();
                    @endphp

                    @if ($transactions->isEmpty())
                        <div class="text-center py-12">
                            <div class="bg-emerald-50 w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="ai-clock text-4xl text-emerald-600"></i>
                            </div>
                            <p class="text-gray-600 text-lg">Belum ada riwayat transaksi poin</p>
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach ($transactions as $transaction)
                                <div class="flex items-center justify-between p-5 bg-gradient-to-r from-gray-50 to-white rounded-xl border border-gray-200 hover:border-emerald-300 hover:shadow-md transition-all">
                                    <div class="flex items-center gap-4">
                                        <div class="p-3 rounded-lg {{ $transaction->points > 0 ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' }}">
                                            <i class="ai-{{ $transaction->points > 0 ? 'arrow-up' : 'arrow-down' }} text-xl"></i>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-900">{{ $transaction->description }}</p>
                                            <p class="text-sm text-gray-600">{{ $transaction->created_at->locale('id')->diffForHumans() }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold text-xl {{ $transaction->points > 0 ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $transaction->points > 0 ? '+' : '' }}{{ number_format($transaction->points) }}
                                        </p>
                                        <p class="text-sm text-gray-500">Saldo: {{ number_format($transaction->balance_after) }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="p-8 bg-white shadow-lg rounded-2xl animate-fade-in" style="animation-delay: 0.3s;">
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

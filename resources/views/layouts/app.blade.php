<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'GoField') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet" />

    <!-- Akar Icons -->
    <link rel="stylesheet" href="https://unpkg.com/akar-icons@latest/dist/akar-icons.css">

    <!-- Styles -->
    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
        
        @keyframes fade-in {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in { animation: fade-in 0.4s ease-out forwards; }
        
        .bg-primary { background-color: #047857; }
        .bg-primary-light { background-color: #10b981; }
        .text-primary { color: #047857; }
        .border-primary { border-color: #047857; }
        
        /* Mobile optimizations */
        @media (max-width: 768px) {
            /* Larger touch targets */
            button, a {
                min-height: 44px;
                min-width: 44px;
            }
            
            /* Prevent text size adjustment on orientation change */
            html {
                -webkit-text-size-adjust: 100%;
                text-size-adjust: 100%;
            }
            
            /* Smooth scrolling */
            html {
                scroll-behavior: smooth;
            }
            
            /* Better readability on small screens */
            body {
                font-size: 16px;
                line-height: 1.6;
            }
            
            /* Table overflow scroll */
            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
        }
        
        /* Prevent horizontal scroll */
        body {
            overflow-x: hidden;
        }
        
        /* Image optimization */
        img {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-50">
        <!-- Navigation -->
        <nav class="fixed top-0 left-0 right-0 z-50 bg-white shadow-sm transition-all duration-200" x-data="{ mobileMenuOpen: false }">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <a href="{{ route('home') }}" class="flex items-center gap-2 group">
                            <div class="bg-emerald-600 p-2 rounded-lg transition-all duration-200 group-hover:bg-emerald-700">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"></path>
                                </svg>
                            </div>
                            <span class="text-xl font-bold text-gray-900">
                                GoField
                            </span>
                        </a>
                    </div>

                    <!-- Desktop Menu -->
                    <div class="hidden md:flex items-center gap-4">
                        @auth
                            <!-- Authenticated User Menu -->
                            <div class="relative" x-data="{ open: false }" @click.away="open = false">
                                <button @click="open = !open" type="button" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-50 transition-colors">
                                    <div class="w-9 h-9 rounded-full bg-emerald-600 flex items-center justify-center text-white font-semibold text-sm">
                                        {{ substr(Auth::user()->name, 0, 1) }}
                                    </div>
                                    <div class="text-left hidden sm:block">
                                        <div class="text-sm font-semibold text-gray-900">{{ Auth::user()->name }}</div>
                                        <div class="text-xs text-gray-500">{{ number_format(Auth::user()->points_balance) }} Poin</div>
                                    </div>
                                    <svg class="w-4 h-4 text-gray-600 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>

                                <div x-show="open" 
                                     x-cloak
                                     @click.away="open = false"
                                     x-transition:enter="transition ease-out duration-100"
                                     x-transition:enter-start="opacity-0 scale-95"
                                     x-transition:enter-end="opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-75"
                                     x-transition:leave-start="opacity-100 scale-100"
                                     x-transition:leave-end="opacity-0 scale-95"
                                     class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50">
                                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                        <i class="ai-dashboard"></i>
                                        <span>Dashboard</span>
                                    </a>
                                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                        <i class="ai-person"></i>
                                        <span>Profil</span>
                                    </a>
                                    <div class="border-t border-gray-200 my-1"></div>
                                    <form id="logout-form-desktop" method="POST" action="{{ route('logout') }}" class="m-0">
                                        @csrf
                                        <button type="submit" class="flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors w-full text-left">
                                            <i class="ai-sign-out"></i>
                                            <span>Keluar</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @else
                            <!-- Guest Buttons -->
                            <a href="{{ route('login') }}" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-emerald-600 transition-colors">
                                Masuk
                            </a>
                            <a href="{{ route('register') }}" class="px-5 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition-colors">
                                Daftar
                            </a>
                        @endauth
                    </div>

                    <!-- Mobile Menu Button -->
                    <div class="md:hidden flex items-center">
                        <button @click="mobileMenuOpen = !mobileMenuOpen" class="p-2 rounded-lg hover:bg-gray-100 transition-colors">
                            <svg x-show="!mobileMenuOpen" class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                            <svg x-show="mobileMenuOpen" x-cloak class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile Menu -->
            <div x-show="mobileMenuOpen" 
                 x-cloak
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 transform -translate-y-2"
                 x-transition:enter-end="opacity-100 transform translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 transform translate-y-0"
                 x-transition:leave-end="opacity-0 transform -translate-y-2"
                 class="md:hidden border-t border-gray-200 bg-white">
                <div class="px-4 py-4 space-y-2">
                    @auth
                        <!-- User Info -->
                        <div class="flex items-center gap-3 p-3 bg-emerald-50 rounded-lg mb-3">
                            <div class="w-12 h-12 rounded-full bg-emerald-600 flex items-center justify-center text-white font-bold">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </div>
                            <div>
                                <div class="text-sm font-bold text-gray-900">{{ Auth::user()->name }}</div>
                                <div class="text-xs text-emerald-700">{{ number_format(Auth::user()->points_balance) }} Poin</div>
                            </div>
                        </div>
                        
                        <a href="{{ route('home') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-50 transition-colors text-gray-700">
                            <i class="ai-home text-lg"></i>
                            <span class="font-medium">Beranda</span>
                        </a>
                        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-50 transition-colors text-gray-700">
                            <i class="ai-dashboard text-lg"></i>
                            <span class="font-medium">Dashboard</span>
                        </a>
                        <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-50 transition-colors text-gray-700">
                            <i class="ai-person text-lg"></i>
                            <span class="font-medium">Profil</span>
                        </a>
                        
                        <div class="border-t border-gray-200 my-2"></div>
                        
                        <form id="logout-form-mobile" method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-red-50 transition-colors text-red-600 w-full text-left">
                                <i class="ai-sign-out text-lg"></i>
                                <span class="font-medium">Keluar</span>
                            </button>
                        </form>
                    @else
                        <a href="{{ route('home') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-50 transition-colors text-gray-700">
                            <i class="ai-home text-lg"></i>
                            <span class="font-medium">Beranda</span>
                        </a>
                        <a href="{{ route('login') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-50 transition-colors text-gray-700">
                            <i class="ai-sign-in text-lg"></i>
                            <span class="font-medium">Masuk</span>
                        </a>
                        <a href="{{ route('register') }}" class="flex items-center gap-3 px-4 py-3 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors">
                            <i class="ai-person-add text-lg"></i>
                            <span class="font-medium">Daftar Sekarang</span>
                        </a>
                    @endauth
                </div>
            </div>
        </nav>

        <!-- Header -->
        <header class="relative overflow-hidden bg-gradient-to-br from-emerald-700 via-emerald-600 to-emerald-800 pt-16 pb-24">
            <div class="absolute inset-0 opacity-5">
                <div class="absolute top-0 left-0 w-96 h-96 bg-white rounded-full blur-3xl"></div>
                <div class="absolute bottom-0 right-0 w-96 h-96 bg-emerald-300 rounded-full blur-3xl"></div>
            </div>

            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                @yield('header')
            </div>
        </header>

        <!-- Main Content -->
        <main class="-mt-16 relative z-10">
            @if (session('success'))
                <div class="max-w-7xl mx-auto px-4 mb-6">
                    <div class="bg-green-50 border border-green-200 text-green-800 px-6 py-4 rounded-lg flex items-center gap-3">
                        <i class="ai-circle-check text-green-600 text-2xl"></i>
                        <span class="font-semibold">{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="max-w-7xl mx-auto px-4 mb-6">
                    <div class="bg-red-50 border border-red-200 text-red-800 px-6 py-4 rounded-lg flex items-center gap-3">
                        <i class="ai-circle-x text-red-600 text-2xl"></i>
                        <span class="font-semibold">{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="bg-gray-900 text-white mt-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                    <div class="col-span-1 sm:col-span-2 lg:col-span-2">
                        <div class="flex items-center gap-2 mb-3">
                            <div class="bg-emerald-600 p-2 rounded-lg">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"></path>
                                </svg>
                            </div>
                            <span class="text-xl font-bold">GoField</span>
                        </div>
                        <p class="text-gray-400 text-sm leading-relaxed max-w-md">
                            Platform booking lapangan olahraga modern untuk semua kebutuhan Anda.
                        </p>
                    </div>
                    <div>
                        <h3 class="font-semibold text-sm mb-3">Layanan</h3>
                        <ul class="space-y-2 text-sm text-gray-400">
                            <li><a href="{{ route('home') }}" class="hover:text-white transition-colors">Semua Lapangan</a></li>
                            <li><a href="{{ route('home') }}#futsal" class="hover:text-white transition-colors">Futsal</a></li>
                            <li><a href="{{ route('home') }}#basket" class="hover:text-white transition-colors">Basket</a></li>
                            <li><a href="{{ route('home') }}#badminton" class="hover:text-white transition-colors">Badminton</a></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="font-semibold text-sm mb-3">Kontak</h3>
                        <ul class="space-y-2 text-sm text-gray-400">
                            <li class="flex items-start gap-2">
                                <i class="ai-envelope text-xs mt-1 flex-shrink-0"></i>
                                <span class="break-all">info@gofield.com</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <i class="ai-phone text-xs mt-1 flex-shrink-0"></i>
                                <span>+62 812-3456-7890</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <i class="ai-location text-xs mt-1 flex-shrink-0"></i>
                                <span>Jakarta, Indonesia</span>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="border-t border-gray-800 mt-8 pt-6 text-center text-gray-500 text-sm">
                    <p>&copy; {{ date('Y') }} GoField. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>

    @livewireScripts

    <script>
        // Alpine.js for dropdown
        document.addEventListener('alpine:init', () => {
            // Auto-hide alerts after 5 seconds
            setTimeout(() => {
                const alerts = document.querySelectorAll('[class*="bg-green-50"], [class*="bg-red-50"]');
                alerts.forEach(alert => {
                    alert.style.transition = 'opacity 0.5s';
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 500);
                });
            }, 5000);
        });

        // Handle logout forms explicitly
        document.addEventListener('DOMContentLoaded', function() {
            const logoutForms = ['logout-form-desktop', 'logout-form-mobile'];
            
            logoutForms.forEach(formId => {
                const form = document.getElementById(formId);
                if (form) {
                    form.addEventListener('submit', function(e) {
                        e.preventDefault();
                        
                        // Debug: Log form submission
                        console.log('Logout form submitted:', formId);
                        
                        // Submit form via fetch for better control
                        const formData = new FormData(form);
                        
                        fetch(form.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            credentials: 'same-origin'
                        })
                        .then(response => {
                            console.log('Logout response:', response.status);
                            // Force redirect to homepage
                            window.location.href = '/';
                        })
                        .catch(error => {
                            console.error('Logout error:', error);
                            // Even if error, try to redirect
                            window.location.href = '/';
                        });
                    });
                }
            });
        });
    </script>
</body>
</html>

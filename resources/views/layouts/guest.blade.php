<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'GoField') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <style>
            @keyframes float {
                0%, 100% { transform: translateY(0px); }
                50% { transform: translateY(-20px); }
            }
            @keyframes slideIn {
                from { opacity: 0; transform: translateY(30px); }
                to { opacity: 1; transform: translateY(0); }
            }
            .animate-float {
                animation: float 6s ease-in-out infinite;
            }
            .animate-slide-in {
                animation: slideIn 0.6s ease-out;
            }
        </style>
    </head>
    <body class="font-sans antialiased">
        <!-- Gradient Background -->
        <div class="min-h-screen relative overflow-hidden bg-gradient-to-br from-emerald-50 via-teal-50 to-cyan-50">
            <!-- Animated Background Shapes -->
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <div class="absolute -top-40 -right-40 w-80 h-80 bg-emerald-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-float"></div>
                <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-teal-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-float" style="animation-delay: 2s;"></div>
                <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-80 h-80 bg-cyan-200 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-float" style="animation-delay: 4s;"></div>
            </div>

            <!-- Content -->
            <div class="relative min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 px-4">
                <!-- Logo / Brand -->
                <div class="animate-slide-in mb-8">
                    <a href="/" class="flex flex-col items-center group">
                        <span class="mt-4 text-3xl font-bold bg-gradient-to-r text-emerald-600 bg-clip-text">GoField</span>
                        <span class="text-sm text-gray-600 mt-1">Booking Lapangan Olahraga</span>
                    </a>
                </div>

                <!-- Auth Card -->
                <div class="w-full sm:max-w-md animate-slide-in" style="animation-delay: 0.2s;">
                    <div class="bg-white/80 backdrop-blur-xl shadow-2xl rounded-3xl border border-white/20 overflow-hidden">
                        <!-- Card Header Gradient -->
                        <div class="h-2 bg-gradient-to-r from-emerald-500 via-teal-500 to-cyan-500"></div>
                        
                        <!-- Card Content -->
                        <div class="px-8 py-8">
                            {{ $slot }}
                        </div>
                    </div>
                    
                    <!-- Footer Links -->
                    <div class="mt-6 text-center">
                        <p class="text-sm text-gray-600">
                            &copy; {{ date('Y') }} GoField. All rights reserved.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'GeoLens') }} — AI Satellite Annotation</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            body { font-family: 'Cairo', 'Inter', sans-serif; }
            .auth-gradient {
                background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 50%, #0f172a 100%);
            }
            .auth-glow {
                box-shadow: 0 0 60px rgba(34, 211, 238, 0.1);
            }
            .auth-card {
                background: rgba(255, 255, 255, 0.98);
                backdrop-filter: blur(20px);
            }
        </style>
    </head>
    <body class="auth-gradient min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 font-sans text-gray-900 antialiased">

        {{-- Background decorative elements --}}
        <div class="fixed inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-40 -right-40 w-96 h-96 bg-cyan-500/5 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-emerald-500/5 rounded-full blur-3xl"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-cyan-400/3 rounded-full blur-3xl"></div>
        </div>

        {{-- Logo + Brand --}}
        <div class="relative z-10 flex flex-col items-center mb-8">
            <a href="/" class="flex flex-col items-center gap-3 group">
                <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-cyan-500 to-emerald-500 p-0.5 auth-glow group-hover:scale-105 transition-transform duration-300">
                    <div class="w-full h-full rounded-2xl bg-gray-900 flex items-center justify-center">
                        <x-application-logo class="w-12 h-12 text-cyan-400" />
                    </div>
                </div>
                <div class="text-center">
                    <h1 class="text-2xl font-bold text-white tracking-tight">GeoLens</h1>
                    <p class="text-sm text-cyan-300/70">AI Satellite Intelligence</p>
                </div>
            </a>
        </div>

        {{-- Card --}}
        <div class="relative z-10 w-full sm:max-w-md px-6 py-8 bg-white/95 backdrop-blur-sm shadow-2xl auth-glow rounded-2xl sm:rounded-3xl border border-white/10">
            {{ $slot }}

            <div class="mt-6 pt-4 border-t border-gray-100 text-center">
                <p class="text-xs text-gray-400">
                    &copy; {{ date('Y') }} GeoLens. All rights reserved.
                </p>
            </div>
        </div>

        {{-- Decorative dots at bottom --}}
        <div class="relative z-10 mt-8 flex gap-2">
            <span class="w-2 h-2 rounded-full bg-cyan-500/30"></span>
            <span class="w-2 h-2 rounded-full bg-emerald-500/30"></span>
            <span class="w-2 h-2 rounded-full bg-cyan-500/30"></span>
        </div>

    </body>
</html>

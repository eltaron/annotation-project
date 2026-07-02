<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Geo Annotate — AI Annotation Tool for Satellite Images</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .auth-glow {
            box-shadow: 0 0 80px rgba(6, 182, 212, 0.08);
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-20px);
            }
        }

        @keyframes float-delayed {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-15px);
            }
        }

        @keyframes shimmer {
            0% {
                background-position: -200% center;
            }

            100% {
                background-position: 200% center;
            }
        }

        .floating-orb {
            position: fixed;
            border-radius: 50%;
            pointer-events: none;
            filter: blur(100px);
            opacity: 0.15;
            z-index: 0;
        }

        .orb-1 {
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(6, 182, 212, 0.6), transparent);
            top: -100px;
            left: -100px;
            animation: float 8s ease-in-out infinite;
        }

        .orb-2 {
            width: 350px;
            height: 350px;
            background: radial-gradient(circle, rgba(16, 185, 129, 0.5), transparent);
            bottom: -80px;
            right: -80px;
            animation: float-delayed 10s ease-in-out infinite;
        }

        .orb-3 {
            width: 250px;
            height: 250px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.4), transparent);
            top: 50%;
            left: 50%;
            animation: float 12s ease-in-out infinite;
        }
    </style>
</head>

<body
    style="background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 50%, #0f172a 100%); background-image: radial-gradient(rgba(255,255,255,0.03) 1px, transparent 1px); background-size: 30px 30px; font-family: 'Cairo', sans-serif;background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 50%, #0f172a 100%);"
    class="py-5 min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 antialiased overflow-x-hidden">

    {{-- Floating orbs --}}
    <div class="floating-orb orb-1"></div>
    <div class="floating-orb orb-2"></div>
    <div class="floating-orb orb-3"></div>

    {{-- Scan line overlay --}}
    <div style="background: repeating-linear-gradient(0deg, transparent, transparent 2px, rgba(255,255,255,0.008) 2px, rgba(255,255,255,0.008) 4px);"
        class="fixed inset-0 pointer-events-none z-0"></div>

    {{-- <div class="relative z-10 flex flex-col items-center mb-8">
        <a href="/" class="flex flex-col items-center gap-3 group">
            <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-cyan-500/20 to-emerald-500/20 p-0.5 auth-glow group-hover:scale-105 group-hover:shadow-lg group-hover:shadow-cyan-500/10 transition-all duration-500">
                <div class="w-full h-full rounded-2xl bg-slate-900/80 backdrop-blur-sm flex items-center justify-center">
                    <x-application-logo class="w-12 h-12 text-cyan-400" />
                </div>
            </div>
            <div class="text-center">
                <h1 class="text-2xl font-bold text-white tracking-tight">Geo Annotate</h1>
                <p class="text-sm text-cyan-400/50">AI Annotation Tool for Satellite Images</p>
            </div>
        </a>
    </div> --}}

    <div
        class="relative z-10 w-full sm:max-w-md px-8 py-8 bg-slate-900/60 backdrop-blur-xl shadow-2xl auth-glow rounded-3xl border border-white/10 transition-all duration-500">
        <div class="absolute inset-0 rounded-3xl bg-gradient-to-b from-white/[0.03] to-transparent pointer-events-none">
        </div>

        <div class="relative">
            {{ $slot }}
        </div>

        <div class="mt-6 pt-4 border-t border-white/5 text-center">
            <p class="text-xs text-slate-500">Geo Annotate &copy; {{ date('Y') }} — All rights reserved</p>
        </div>
    </div>

    <div class="relative z-10 mt-8 flex gap-3">
        <span class="w-2 h-2 rounded-full bg-cyan-500/40 animate-pulse"></span>
        <span class="w-2 h-2 rounded-full bg-emerald-500/40 animate-pulse" style="animation-delay: 0.3s"></span>
        <span class="w-2 h-2 rounded-full bg-cyan-500/40 animate-pulse" style="animation-delay: 0.6s"></span>
    </div>

</body>

</html>

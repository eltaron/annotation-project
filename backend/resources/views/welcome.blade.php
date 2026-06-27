<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GeoLens - AI Satellite Annotation Tool</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .gradient-bg { background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 50%, #0f172a 100%); }
        .glow { box-shadow: 0 0 40px rgba(34, 211, 238, 0.15); }
    </style>
</head>
<body class="gradient-bg min-h-screen text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <nav class="flex items-center justify-between py-6">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-cyan-500/20 flex items-center justify-center">
                    <svg class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <span class="text-xl font-bold">GeoLens</span>
            </div>
            <div class="flex items-center gap-4">
                @auth
                    <a href="{{ route('dashboard') }}" class="px-6 py-2 bg-cyan-500 hover:bg-cyan-400 text-gray-900 font-semibold rounded-lg transition">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="text-gray-300 hover:text-white transition">Login</a>
                    <a href="{{ route('register') }}" class="px-6 py-2 bg-cyan-500 hover:bg-cyan-400 text-gray-900 font-semibold rounded-lg transition">Get Started</a>
                @endauth
            </div>
        </nav>

        <main class="py-20">
            <div class="text-center max-w-4xl mx-auto">
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-cyan-500/10 border border-cyan-500/20 rounded-full text-cyan-400 text-sm mb-8">
                    <span class="w-2 h-2 bg-cyan-400 rounded-full animate-pulse"></span>
                    AI-Powered Satellite Intelligence
                </div>

                <h1 class="text-5xl md:text-7xl font-extrabold leading-tight mb-6">
                    Smart Annotation
                    <span class="bg-gradient-to-r from-cyan-400 to-emerald-400 text-transparent bg-clip-text">Powered by AI</span>
                </h1>

                <p class="text-xl text-gray-300 leading-relaxed mb-12 max-w-3xl mx-auto">
                    A smart web platform that combines remote sensing image annotation with AI-powered crop health analysis, 
                    helping users generate accurate datasets and valuable agricultural insights.
                </p>

                <div class="flex flex-wrap justify-center gap-4 mb-20">
                    @auth
                        <a href="{{ route('dashboard') }}" class="px-8 py-4 bg-cyan-500 hover:bg-cyan-400 text-gray-900 font-bold text-lg rounded-xl transition transform hover:scale-105 glow">
                            Go to Dashboard →
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="px-8 py-4 bg-cyan-500 hover:bg-cyan-400 text-gray-900 font-bold text-lg rounded-xl transition transform hover:scale-105 glow">
                            Get Started →
                        </a>
                        <a href="{{ route('login') }}" class="px-8 py-4 border border-gray-600 hover:border-gray-500 text-gray-300 font-semibold text-lg rounded-xl transition">
                            Sign In
                        </a>
                    @endauth
                </div>
            </div>

            <div class="grid md:grid-cols-3 gap-8 max-w-5xl mx-auto">
                <div class="p-8 rounded-2xl bg-white/5 border border-white/10 glow">
                    <div class="w-14 h-14 rounded-xl bg-emerald-500/20 flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Smart Annotation Made Simple</h3>
                    <p class="text-gray-400 leading-relaxed">Upload images, create accurate annotations, manage projects efficiently, and let AI assist you throughout the process.</p>
                </div>

                <div class="p-8 rounded-2xl bg-white/5 border border-white/10 glow">
                    <div class="w-14 h-14 rounded-xl bg-cyan-500/20 flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-3">AI-Powered Segmentation</h3>
                    <p class="text-gray-400 leading-relaxed">Leverage Meta's SAM model for zero-shot segmentation. Click once and let AI accurately delineate objects.</p>
                </div>

                <div class="p-8 rounded-2xl bg-white/5 border border-white/10 glow">
                    <div class="w-14 h-14 rounded-xl bg-emerald-500/20 flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Crop Health Analytics</h3>
                    <p class="text-gray-400 leading-relaxed">Transform multi-spectral satellite imagery into actionable crop health insights and localized stress maps.</p>
                </div>
            </div>
        </main>

        <footer class="py-8 border-t border-white/10 text-center text-gray-500 text-sm">
            &copy; {{ date('Y') }} GeoLens. AI Satellite Annotation &amp; Crop Analytics Tool
        </footer>
    </div>
</body>
</html>

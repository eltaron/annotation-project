<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Geo Annotate — AI Annotation Tool for Satellite Images</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <style>
        body {
            font-family: 'Cairo', sans-serif;
        }

        .glow {
            box-shadow: 0 0 40px rgba(34, 211, 238, 0.1);
        }

        .grid-pattern {
            background-image: radial-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 1px);
            background-size: 30px 30px;
        }

        .float {
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-20px);
            }
        }

        .hero-overlay {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.85) 0%, rgba(30, 58, 95, 0.75) 50%, rgba(15, 23, 42, 0.85) 100%);
        }

        .hero-bg {
            background-image: url('/bg.jpeg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }

        .help-btn {
            position: fixed;
            bottom: 24px;
            right: 24px;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: linear-gradient(135deg, #06b6d4, #10b981);
            border: none;
            color: white;
            cursor: pointer;
            z-index: 100;
            box-shadow: 0 4px 24px rgba(6, 182, 212, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.2s;
        }
        .help-btn:hover { transform: scale(1.1); }
        .help-btn svg { width: 28px; height: 28px; }

        .help-panel {
            position: fixed;
            bottom: 92px;
            right: 24px;
            width: 380px;
            max-height: 70vh;
            background: #1e293b;
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.5);
            z-index: 99;
            overflow-y: auto;
            transform: translateY(20px);
            opacity: 0;
            pointer-events: none;
            transition: all 0.3s ease;
        }
        .help-panel.open {
            transform: translateY(0);
            opacity: 1;
            pointer-events: all;
        }
        .help-panel::-webkit-scrollbar { width: 4px; }
        .help-panel::-webkit-scrollbar-track { background: transparent; }
        .help-panel::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }

        .help-section { padding: 16px 20px; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .help-section:last-child { border-bottom: none; }
        .help-section h4 { font-size: 13px; font-weight: 700; color: #06b6d4; margin-bottom: 10px; display: flex; align-items: center; gap: 8px; }
        .help-section p, .help-section li { font-size: 12px; color: #94a3b8; line-height: 1.6; }
        .help-section ul { list-style: none; padding: 0; margin: 0; }
        .help-section li { padding: 4px 0; display: flex; align-items: center; gap: 8px; }
        .help-section li::before { content: ''; width: 4px; height: 4px; border-radius: 50%; background: #06b6d4; flex-shrink: 0; }
        .help-section code { background: #0f172a; color: #06b6d4; padding: 2px 6px; border-radius: 4px; font-size: 11px; }
        .help-section .stat-row { display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid rgba(255,255,255,0.03); }
        .help-section .stat-row:last-child { border-bottom: none; }
        .help-section .stat-row span:first-child { color: #64748b; }
        .help-section .stat-row span:last-child { color: #e2e8f0; font-weight: 600; }
    </style>
</head>

<body style="font-family: 'Cairo', sans-serif;" class="min-h-screen text-white">
    <div class="hero-bg min-h-screen">
        <div class=" hero-overlay">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                {{-- Nav --}}
                <nav class="flex items-center justify-between py-6">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-gradient-to-br from-cyan-500/20 to-emerald-500/20 flex items-center justify-center">
                            <svg class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <span
                            class="text-xl font-bold bg-gradient-to-l from-cyan-400 to-emerald-400 text-transparent bg-clip-text">Geo
                            Annotate</span>
                    </div>
                    <div class="flex items-center gap-4">
                        @auth
                            <a href="{{ route('dashboard') }}"
                                class="px-6 py-2.5 bg-gradient-to-l from-cyan-500 to-emerald-500 hover:from-cyan-400 hover:to-emerald-400 text-white font-semibold rounded-xl transition-all glow">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}"
                                class="text-gray-300 hover:text-white transition font-medium">Sign
                                in</a>
                            <a href="{{ route('register') }}"
                                class="px-6 py-2.5 bg-gradient-to-l from-cyan-500 to-emerald-500 hover:from-cyan-400 hover:to-emerald-400 text-white font-semibold rounded-xl transition-all glow">Get
                                Started</a>
                        @endauth
                    </div>
                </nav>

                <main class="py-16 md:py-24">
                    <div class="text-center max-w-4xl mx-auto">
                        {{-- Badge --}}
                        <div
                            class="inline-flex items-center gap-2 px-4 py-2 bg-cyan-500/10 border border-cyan-500/20 rounded-full text-cyan-400 text-sm mb-8">
                            <span class="w-2 h-2 bg-cyan-400 rounded-full animate-pulse"></span>
                            AI-powered satellite imagery analysis
                        </div>

                        {{-- Title --}}
                        <h1 class="text-5xl md:text-7xl font-extrabold leading-tight mb-6">
                            <span class="bg-gradient-to-l from-cyan-400 to-emerald-400 text-transparent bg-clip-text">AI
                                annotation tool</span>
                            <br>for satellite images
                        </h1>

                        <p class="text-xl text-gray-300 leading-relaxed mb-12 max-w-3xl mx-auto">
                            A powerful platform for annotating satellite imagery and analyzing crop health using AI,
                            helping researchers and agronomists create precise datasets and actionable insights
                        </p>

                        <div class="flex flex-wrap justify-center gap-4 mb-20">
                            @auth
                                <a href="{{ route('dashboard') }}"
                                    class="px-8 py-4 bg-gradient-to-l from-cyan-500 to-emerald-500 hover:from-cyan-400 hover:to-emerald-400 text-white font-bold text-lg rounded-xl transition-all glow inline-flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                    </svg>
                                    Dashboard
                                </a>
                            @else
                                <a href="{{ route('register') }}"
                                    class="px-8 py-4 bg-gradient-to-l from-cyan-500 to-emerald-500 hover:from-cyan-400 hover:to-emerald-400 text-white font-bold text-lg rounded-xl transition-all glow inline-flex items-center gap-2">
                                    Create Free Account
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                    </svg>
                                </a>
                                <a href="{{ route('login') }}"
                                    class="px-8 py-4 border border-gray-600 hover:border-gray-500 text-gray-300 font-semibold text-lg rounded-xl transition">Sign
                                    in</a>
                            @endauth
                        </div>
                    </div>

                    {{-- Features --}}
                    <div class="grid md:grid-cols-3 gap-8 max-w-5xl mx-auto">
                        <div
                            class="p-8 rounded-2xl bg-white/5 border border-white/10 glow hover:bg-white/[0.07] transition-all">
                            <div
                                class="w-14 h-14 rounded-xl bg-gradient-to-br from-emerald-500/20 to-green-500/20 flex items-center justify-center mb-6">
                                <svg class="w-7 h-7 text-emerald-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold mb-3">Smart Annotation</h3>
                            <p class="text-gray-400 leading-relaxed">Upload images, create precise annotations, and
                                manage
                                your projects efficiently with AI assistance</p>
                        </div>

                        <div
                            class="p-8 rounded-2xl bg-white/5 border border-white/10 glow hover:bg-white/[0.07] transition-all">
                            <div
                                class="w-14 h-14 rounded-xl bg-gradient-to-br from-cyan-500/20 to-blue-500/20 flex items-center justify-center mb-6">
                                <svg class="w-7 h-7 text-cyan-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold mb-3">SAM Segmentation</h3>
                            <p class="text-gray-400 leading-relaxed">Use Meta's SAM model for one-click segmentation —
                                high
                                accuracy with zero training</p>
                        </div>

                        <div
                            class="p-8 rounded-2xl bg-white/5 border border-white/10 glow hover:bg-white/[0.07] transition-all">
                            <div
                                class="w-14 h-14 rounded-xl bg-gradient-to-br from-emerald-500/20 to-green-500/20 flex items-center justify-center mb-6">
                                <svg class="w-7 h-7 text-emerald-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold mb-3">Crop Health Analysis</h3>
                            <p class="text-gray-400 leading-relaxed">Convert multispectral imagery into actionable crop
                                health reports and stress maps</p>
                        </div>
                    </div>
                </main>

                <footer class="py-8 border-t border-white/10 text-center text-gray-500 text-sm">
                    &copy; {{ date('Y') }} Geo Annotate — AI Annotation Tool for Satellite Images
                </footer>
            </div>
        </div>
    </div>
    {{-- Floating Help Button --}}
    <button class="help-btn" id="helpBtn" onclick="toggleHelp()" aria-label="Help">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
    </button>

    {{-- Help Panel --}}
    <div class="help-panel" id="helpPanel">
        <div class="help-section">
            <h4><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>About Geo Annotate</h4>
            <p><strong>Geo Annotate</strong> is an AI-powered platform for annotating satellite imagery and analyzing crop health. It uses <strong>Meta's SAM</strong> for one-click image segmentation and <strong>EuroSAT ResNet-50</strong> for land-cover classification.</p>
            <ul class="mt-2">
                <li>Upload GeoTIFF images (4+ bands for NDVI)</li>
                <li>Create annotation classes and segment objects with SAM</li>
                <li>Classify segments using the built-in AI model</li>
                <li>Generate crop health reports from multispectral bands</li>
                <li>Export annotations as GeoJSON</li>
            </ul>
        </div>

        @auth
        <div class="help-section">
            <h4><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>Your Account</h4>
            @php
                $user = Auth::user();
                $projectCount = $user->projects()->count();
                $imageCount = \App\Models\ImageUpload::whereIn('project_id', $user->projects()->pluck('id'))->count();
                $annotationCount = \App\Models\Annotation::whereIn('image_upload_id', function($q) use ($user) {
                    $q->select('id')->from('image_uploads')->whereIn('project_id', $user->projects()->pluck('id'));
                })->count();
            @endphp
            <div class="stat-row"><span>Projects</span><span>{{ $projectCount }}</span></div>
            <div class="stat-row"><span>Images</span><span>{{ $imageCount }}</span></div>
            <div class="stat-row"><span>Annotations</span><span>{{ $annotationCount }}</span></div>
            <div class="mt-3">
                <a href="{{ route('dashboard') }}" class="text-xs text-cyan-400 hover:text-cyan-300 transition">Go to Dashboard →</a>
            </div>
        </div>
        @endauth

        <div class="help-section">
            <h4><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>Quick Setup Guide</h4>
            <p class="mb-2">To run the system, configure these in <strong>Settings</strong> (after login):</p>
            <ul>
                <li><code>Python Executable</code> — usually <code>python</code> or <code>python3</code></li>
                <li><code>Python Scripts Base Path</code> — the root folder containing the AI scripts</li>
                <li><code>SAM Checkpoint Path</code> — path to <code>sam_vit_b_01ec64.pth</code> (~350MB)</li>
                <li><code>Classifier Weights Path</code> — path to <code>classifier_weights.pth</code></li>
                <li><code>Max Upload Size</code> — max MB for GeoTIFF uploads</li>
            </ul>
            <p class="mt-2">After configuring, go to <strong>Settings → Run Full Diagnostic</strong> to verify everything is connected.</p>
            @auth
            <div class="mt-3">
                <a href="{{ route('settings.index') }}" class="text-xs text-cyan-400 hover:text-cyan-300 transition">Open Settings →</a>
            </div>
            @endauth
        </div>
    </div>

    <script>
    function toggleHelp() {
        document.getElementById('helpPanel').classList.toggle('open');
    }
    document.addEventListener('click', function(e) {
        const panel = document.getElementById('helpPanel');
        const btn = document.getElementById('helpBtn');
        if (!panel.contains(e.target) && !btn.contains(e.target) && panel.classList.contains('open')) {
            panel.classList.remove('open');
        }
    });
    </script>

</body>

</html>

<x-app-layout>
    <x-slot name="header">
        <div class="page-header">
            <h1 class="page-title">System Settings</h1>
            <p class="page-subtitle">Configure Python AI paths and system parameters</p>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="alert-success mb-6 animate-fade-in">{{ session('success') }}</div>
            @endif

            <form method="POST" action="{{ route('settings.update') }}" class="space-y-6">
                @csrf
                @method('PATCH')

                {{-- Python Path --}}
                <div class="card p-6 animate-fade-in">
                    <h3 class="section-title mb-4">Python Environment</h3>
                    <div class="space-y-5">
                        <div>
                            <label class="input-label">Python Executable Path</label>
                            <p class="text-xs text-slate-500 mb-2">The python command used to run AI scripts. Usually <code class="text-cyan-400">python</code> on Windows or <code class="text-cyan-400">python3</code> on Linux.</p>
                            <input type="text" name="python_path" class="input-field" value="{{ old('python_path', $settings['python_path']->value ?? 'python') }}" placeholder="python">
                            @error('python_path') <p class="input-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="input-label">Python Scripts Base Path</label>
                            <p class="text-xs text-slate-500 mb-2">The absolute path to the directory containing the Python AI scripts (sam__predectorr.py, classifier.py, geo_processor.py).</p>
                            <input type="text" name="python_base_path" class="input-field font-mono text-xs" value="{{ old('python_base_path', $settings['python_base_path']->value ?? '') }}" placeholder="/var/www/project">
                            @error('python_base_path') <p class="input-error">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                {{-- AI Model Paths --}}
                <div class="card p-6 animate-fade-in">
                    <h3 class="section-title mb-4">AI Model Checkpoints</h3>
                    <div class="space-y-5">
                        <div>
                            <label class="input-label">SAM Checkpoint Path</label>
                            <p class="text-xs text-slate-500 mb-2">Path to the SAM model checkpoint file (<code class="text-cyan-400">sam_vit_b_01ec64.pth</code>). Relative to the base path or absolute.</p>
                            <input type="text" name="sam_checkpoint_path" class="input-field font-mono text-xs" value="{{ old('sam_checkpoint_path', $settings['sam_checkpoint_path']->value ?? 'checkpoint/sam_vit_b_01ec64.pth') }}" placeholder="checkpoint/sam_vit_b_01ec64.pth">
                            @error('sam_checkpoint_path') <p class="input-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="input-label">Classifier Weights Path</label>
                            <p class="text-xs text-slate-500 mb-2">Path to the EuroSAT classifier weights file (<code class="text-cyan-400">classifier_weights.pth</code>). Relative to the base path or absolute.</p>
                            <input type="text" name="classifier_weights_path" class="input-field font-mono text-xs" value="{{ old('classifier_weights_path', $settings['classifier_weights_path']->value ?? 'checkpoint/classifier_weights.pth') }}" placeholder="checkpoint/classifier_weights.pth">
                            @error('classifier_weights_path') <p class="input-error">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                {{-- Upload Settings --}}
                <div class="card p-6 animate-fade-in">
                    <h3 class="section-title mb-4">Upload Settings</h3>
                    <div>
                        <label class="input-label">Max Upload Size (MB)</label>
                        <p class="text-xs text-slate-500 mb-2">Maximum allowed size for uploaded GeoTIFF images.</p>
                        <input type="number" name="max_upload_size_mb" class="input-field w-32" value="{{ old('max_upload_size_mb', $settings['max_upload_size_mb']->value ?? '500') }}" min="1" max="2048">
                        @error('max_upload_size_mb') <p class="input-error">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="text-right">
                    <button type="submit" class="btn-primary">Save Settings</button>
                </div>
            </form>

            {{-- System Status --}}
            <div class="card p-6 animate-fade-in mt-8">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="section-title mb-0">System Status</h3>
                    <a href="{{ route('settings.diagnostic') }}" class="btn-primary text-xs px-4 py-2">
                        <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        Run Full Diagnostic
                    </a>
                </div>
                <div class="space-y-3 text-sm">
                    @php
                        $pythonPath = \App\Models\SystemSetting::getValue('python_path', 'python');
                        $basePath = \App\Models\SystemSetting::getValue('python_base_path', base_path('..'));
                        $samPath = \App\Models\SystemSetting::getValue('sam_checkpoint_path', 'checkpoint/sam_vit_b_01ec64.pth');
                        $classifierPath = \App\Models\SystemSetting::getValue('classifier_weights_path', 'checkpoint/classifier_weights.pth');
                        $samFull = $basePath . '/' . ltrim($samPath, '/');
                        $classifierFull = $basePath . '/' . ltrim($classifierPath, '/');
                    @endphp
                    <div class="flex items-center justify-between py-2 border-b border-white/5">
                        <span class="text-slate-400">SAM Checkpoint</span>
                        <span class="{{ file_exists($samFull) ? 'text-emerald-400' : 'text-red-400' }}">
                            {{ file_exists($samFull) ? 'Found' : 'Not found' }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b border-white/5">
                        <span class="text-slate-400">Classifier Weights</span>
                        <span class="{{ file_exists($classifierFull) ? 'text-emerald-400' : 'text-red-400' }}">
                            {{ file_exists($classifierFull) ? 'Found' : 'Not found' }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b border-white/5">
                        <span class="text-slate-400">Python</span>
                        <span class="text-slate-300">
                            @php
                                $pythonCheck = trim(shell_exec("where " . escapeshellarg($pythonPath) . " 2>NUL") ?? '');
                            @endphp
                            {{ $pythonCheck ? 'Available' : 'Not found' }}
                            <span class="text-xs text-slate-500 ml-2">{{ $pythonPath }}</span>
                        </span>
                    </div>
                    <div class="flex items-center justify-between py-2">
                        <span class="text-slate-400">Scripts Directory</span>
                        <span class="{{ is_dir($basePath) ? 'text-emerald-400' : 'text-red-400' }}">
                            {{ is_dir($basePath) ? 'Accessible' : 'Not found' }}
                        </span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>

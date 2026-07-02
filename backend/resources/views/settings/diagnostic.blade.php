<x-app-layout>
    <x-slot name="header">
        <div class="page-header">
            <div>
                <h1 class="page-title">System Diagnostic</h1>
                <p class="page-subtitle">Verify Python AI environment and file paths</p>
            </div>
            <a href="{{ route('settings.index') }}" class="btn-secondary text-sm">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Back to Settings
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Python --}}
            <div class="card p-6 animate-fade-in">
                <h3 class="section-title mb-4">Python Environment</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex items-center justify-between py-2 border-b border-white/5">
                        <span class="text-slate-400">Executable</span>
                        <span class="text-xs font-mono text-slate-300">{{ $pythonPath }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b border-white/5">
                        <span class="text-slate-400">Available</span>
                        <span class="{{ $results['python']['available'] ? 'text-emerald-400' : 'text-red-400' }}">
                            {{ $results['python']['available'] ? 'Yes' : 'No' }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between py-2">
                        <span class="text-slate-400">Version</span>
                        <span class="text-slate-300">{{ $results['python']['version'] }}</span>
                    </div>
                </div>
            </div>

            {{-- Python Packages --}}
            <div class="card p-6 animate-fade-in">
                <h3 class="section-title mb-4">Python Packages</h3>
                <div class="space-y-3 text-sm">
                    @foreach($results['imports'] as $pkg => $ok)
                    <div class="flex items-center justify-between py-2 border-b border-white/5 last:border-0">
                        <span class="text-slate-400 font-mono text-xs">{{ $pkg }}</span>
                        <span class="{{ $ok ? 'text-emerald-400' : 'text-red-400' }}">
                            {{ $ok ? 'Installed' : 'Missing' }}
                        </span>
                    </div>
                    @endforeach
                </div>
                @if(in_array(false, $results['imports'], true))
                <div class="mt-4 p-3 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-400 text-xs">
                    Missing packages. Run:
                    <code class="block mt-1 text-cyan-400">pip install -r .python_requirements.txt</code>
                </div>
                @endif
            </div>

            {{-- File Checkpoints --}}
            <div class="card p-6 animate-fade-in">
                <h3 class="section-title mb-4">Model Checkpoints</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex items-center justify-between py-2 border-b border-white/5">
                        <span class="text-slate-400">SAM Checkpoint</span>
                        <span class="{{ $results['sam_checkpoint']['exists'] ? 'text-emerald-400' : 'text-red-400' }}">
                            {{ $results['sam_checkpoint']['exists'] ? 'Found' : 'Not found' }}
                        </span>
                    </div>
                    <div class="text-xs text-slate-500 mb-2 font-mono break-all">{{ $results['sam_checkpoint']['path'] }}</div>
                    <div class="flex items-center justify-between py-2 border-b border-white/5">
                        <span class="text-slate-400">Classifier Weights</span>
                        <span class="{{ $results['classifier_weights']['exists'] ? 'text-emerald-400' : 'text-red-400' }}">
                            {{ $results['classifier_weights']['exists'] ? 'Found' : 'Not found' }}
                        </span>
                    </div>
                    <div class="text-xs text-slate-500 font-mono break-all">{{ $results['classifier_weights']['path'] }}</div>
                    @if(!$results['sam_checkpoint']['exists'])
                    <div class="mt-4 p-3 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-400 text-xs">
                        Download SAM checkpoint:
                        <code class="block mt-1 text-cyan-400">pip install huggingface-hub</code>
                        <code class="block mt-1 text-cyan-400">huggingface-cli download facebook/sam-vit-b --local-dir "{{ $basePath }}/checkpoint"</code>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Scripts Directory --}}
            <div class="card p-6 animate-fade-in">
                <h3 class="section-title mb-4">Python Scripts Directory</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex items-center justify-between py-2 border-b border-white/5">
                        <span class="text-slate-400">Path</span>
                        <span class="text-xs font-mono text-slate-300">{{ $results['scripts_dir_path'] }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2">
                        <span class="text-slate-400">Accessible</span>
                        <span class="{{ $results['scripts_dir'] ? 'text-emerald-400' : 'text-red-400' }}">
                            {{ $results['scripts_dir'] ? 'Yes' : 'No' }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="text-center">
                <a href="{{ route('settings.diagnostic') }}" class="btn-primary">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Re-run Diagnostic
                </a>
            </div>

        </div>
    </div>
</x-app-layout>

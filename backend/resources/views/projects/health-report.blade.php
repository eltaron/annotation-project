<x-app-layout>
    <x-slot name="header">
        <div class="page-header">
            <div>
                <h1 class="page-title">Crop Health Report</h1>
                <p class="page-subtitle">{{ $project->name }} — {{ $imageUpload->original_name }}</p>
            </div>
            <a href="{{ route('projects.show', $project) }}" class="btn-secondary text-sm">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Back to Project
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            @if($healthResult)
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="stat-card animate-fade-in">
                    <div class="stat-icon bg-gradient-to-br from-emerald-500/20 to-emerald-400/10 text-emerald-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-400">Healthy</p>
                        <p class="text-3xl font-bold text-emerald-400 mt-1">{{ $healthResult->healthy_percentage }}%</p>
                    </div>
                </div>
                <div class="stat-card animate-fade-in" style="animation-delay: 0.1s">
                    <div class="stat-icon bg-gradient-to-br from-amber-500/20 to-amber-400/10 text-amber-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-400">Stressed</p>
                        <p class="text-3xl font-bold text-amber-400 mt-1">{{ $healthResult->stressed_percentage }}%</p>
                    </div>
                </div>
                <div class="stat-card animate-fade-in" style="animation-delay: 0.2s">
                    <div class="stat-icon bg-gradient-to-br from-red-500/20 to-red-400/10 text-red-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-400">Unhealthy</p>
                        <p class="text-3xl font-bold text-red-400 mt-1">{{ $healthResult->unhealthy_percentage }}%</p>
                    </div>
                </div>
                <div class="stat-card animate-fade-in" style="animation-delay: 0.3s">
                    <div class="stat-icon bg-gradient-to-br from-cyan-500/20 to-cyan-400/10 text-cyan-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-400">Overall Status</p>
                        <p class="text-3xl font-bold text-white mt-1">{{ $healthResult->overall_status }}</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="card p-6 animate-slide-up">
                    <h3 class="section-title">Crop Health Distribution</h3>
                    <canvas id="pieChart" class="w-full" style="height: 250px"></canvas>
                </div>
                <div class="card p-6 animate-slide-up">
                    <h3 class="section-title">Percentage Comparison</h3>
                    <canvas id="barChart" class="w-full" style="height: 250px"></canvas>
                </div>
            </div>

            <div class="card p-6 animate-slide-up">
                <table class="w-full">
                    <thead>
                        <tr>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-slate-400 border-b border-white/5">Indicator</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-slate-400 border-b border-white/5">Percentage</th>
                            <th class="text-left py-3 px-4 text-sm font-semibold text-slate-400 border-b border-white/5">Bar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(['healthy' => ['Healthy', 'from-emerald-500 to-green-500', 'emerald'], 'stressed' => ['Stressed', 'from-amber-500 to-yellow-500', 'amber'], 'unhealthy' => ['Unhealthy', 'from-red-500 to-rose-500', 'red']] as $key => [$label, $gradient, $color])
                        <tr>
                            <td class="py-4 px-4 text-sm font-semibold text-white">{{ $label }}</td>
                            <td class="py-4 px-4 text-lg font-bold text-{{ $color }}-400">{{ $healthResult->{$key.'_percentage'} }}%</td>
                            <td class="py-4 px-4 w-1/2">
                                <div class="w-full h-3 bg-slate-700 rounded-full overflow-hidden">
                                    <div class="h-full bg-gradient-to-l {{ $gradient }} rounded-full transition-all" style="width: {{ $healthResult->{$key.'_percentage'} }}%"></div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                        <tr class="border-t border-white/5">
                            <td class="py-4 px-4 font-bold text-white">Overall Status</td>
                            <td colspan="2" class="py-4 px-4"><span class="badge-{{ $healthResult->overall_status === 'Good' ? 'emerald' : ($healthResult->overall_status === 'Moderate' ? 'amber' : 'red') }}">{{ $healthResult->overall_status }}</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            @else
            <div class="card p-16 text-center animate-fade-in">
                <div class="w-20 h-20 mx-auto mb-6 rounded-2xl bg-gradient-to-br from-emerald-500/20 to-green-500/20 flex items-center justify-center">
                    <svg class="w-10 h-10 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <h3 class="text-xl font-bold text-white mb-3">Health analysis not yet performed</h3>
                <p class="text-slate-400 mb-6">Use the annotation workspace to analyze the image first</p>
                <a href="{{ route('projects.annotate', [$project, $imageUpload]) }}" class="btn-primary text-base">Go to Annotation Workspace</a>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>

@if($healthResult)
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const fontCairo = { family: 'Cairo', size: 13 };
    const txtColor = '#94a3b8';
    new Chart(document.getElementById('pieChart'), {
        type: 'pie',
        data: {
            labels: ['Healthy ({{ $healthResult->healthy_percentage }}%)', 'Stressed ({{ $healthResult->stressed_percentage }}%)', 'Unhealthy ({{ $healthResult->unhealthy_percentage }}%)'],
            datasets: [{
                data: [{{ $healthResult->healthy_percentage }}, {{ $healthResult->stressed_percentage }}, {{ $healthResult->unhealthy_percentage }}],
                backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: true,
            color: txtColor,
            plugins: { 
                legend: { position: 'bottom', labels: { font: fontCairo, padding: 16, usePointStyle: true, color: txtColor } } 
            }
        }
    });
    new Chart(document.getElementById('barChart'), {
        type: 'bar',
        data: {
            labels: ['Healthy', 'Stressed', 'Unhealthy'],
            datasets: [{
                label: 'Percentage %',
                data: [{{ $healthResult->healthy_percentage }}, {{ $healthResult->stressed_percentage }}, {{ $healthResult->unhealthy_percentage }}],
                backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                borderRadius: 8, borderSkipped: false
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: true,
            color: txtColor,
            scales: { 
                y: { beginAtZero: true, max: 100, ticks: { font: fontCairo, color: txtColor }, grid: { color: '#ffffff10' } }, 
                x: { ticks: { font: fontCairo, color: txtColor } } 
            },
            plugins: { legend: { display: false } }
        }
    });
});
</script>
@endpush
@endif

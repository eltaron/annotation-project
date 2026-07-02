<x-app-layout>
    <x-slot name="header">
        <div class="page-header">
            <div>
                <h1 class="page-title">Dashboard</h1>
                <p class="page-subtitle">Welcome back, {{ Auth::user()->name }}</p>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            {{-- Stats --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="stat-card animate-fade-in">
                    <div class="stat-icon bg-gradient-to-br from-cyan-500/20 to-cyan-400/10 text-cyan-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-400">Total Projects</p>
                        <p class="text-3xl font-bold text-white mt-1">{{ $projectsCount }}</p>
                    </div>
                </div>
                <div class="stat-card animate-fade-in" style="animation-delay: 0.1s">
                    <div class="stat-icon bg-gradient-to-br from-emerald-500/20 to-emerald-400/10 text-emerald-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-400">Total Annotations</p>
                        <p class="text-3xl font-bold text-white mt-1">{{ $totalAnnotations }}</p>
                    </div>
                </div>
                <div class="stat-card animate-fade-in" style="animation-delay: 0.2s">
                    <div class="stat-icon bg-gradient-to-br from-violet-500/20 to-violet-400/10 text-violet-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-400">Active Projects</p>
                        <p class="text-3xl font-bold text-white mt-1">{{ $recentProjects->count() }}</p>
                    </div>
                </div>
            </div>

            {{-- Recent Projects --}}
            <div class="card p-6 animate-slide-up">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="section-title mb-0">Recent Projects</h3>
                    <a href="{{ route('projects.create') }}" class="btn-primary text-sm">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        New Project
                    </a>
                </div>
                @if($recentProjects->count())
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Images</th>
                                    <th>Annotations</th>
                                    <th>Created</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentProjects as $project)
                                <tr>
                                    <td class="font-semibold text-white">{{ $project->name }}</td>
                                    <td><span class="badge-cyan">{{ $project->image_uploads_count }}</span></td>
                                    <td><span class="badge-emerald">{{ $project->annotations_count }}</span></td>
                                    <td class="text-slate-400 text-xs">{{ $project->created_at->diffForHumans() }}</td>
                                    <td><a href="{{ route('projects.show', $project) }}" class="text-cyan-400 hover:text-cyan-300 font-medium text-sm transition inline-flex items-center gap-1">Open
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-12 text-slate-400">
                        <p class="text-lg mb-4">No projects yet</p>
                        <a href="{{ route('projects.create') }}" class="btn-primary">Create your first project</a>
                    </div>
                @endif
            </div>

            {{-- Health Reports --}}
            @if($latestHealthResults->count())
            <div class="card p-6 animate-slide-up">
                <h3 class="section-title">Latest Crop Health Reports</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach($latestHealthResults as $result)
                    <div class="bg-white/5 rounded-2xl p-5 border border-white/10">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs text-slate-400">Project #{{ $result->project_id }}</span>
                            <span class="text-xs font-semibold px-2.5 py-1 rounded-full 
                                {{ $result->overall_status === 'Good' ? 'bg-emerald-500/15 text-emerald-300' : '' }}
                                {{ $result->overall_status === 'Moderate' ? 'bg-amber-500/15 text-amber-300' : '' }}
                                {{ $result->overall_status === 'Critical' ? 'bg-red-500/15 text-red-300' : '' }}">
                                {{ $result->overall_status }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm text-slate-300">Healthy</span>
                            <span class="text-sm font-bold text-white">{{ $result->healthy_percentage }}%</span>
                        </div>
                        <div class="w-full h-2.5 bg-slate-700 rounded-full overflow-hidden">
                            <div class="h-full bg-gradient-to-l from-emerald-500 to-green-500 rounded-full transition-all" style="width: {{ $result->healthy_percentage }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>

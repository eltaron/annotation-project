<x-app-layout>
    <x-slot name="header">
        <div class="page-header">
            <div>
                <h1 class="page-title">My Projects</h1>
                <p class="page-subtitle">All your satellite imagery analysis projects</p>
            </div>
            <a href="{{ route('projects.create') }}" class="btn-primary">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                New Project
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="alert-success mb-6 animate-fade-in">{{ session('success') }}</div>
            @endif

            @if($projects->count())
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($projects as $project)
                    <div class="card p-0 flex flex-col animate-fade-in group">
                        <div class="h-2 bg-gradient-to-l from-cyan-500 to-emerald-500 rounded-t-2xl"></div>
                        <div class="p-6 flex-1">
                            <div class="flex items-start justify-between mb-3">
                                <h3 class="text-lg font-bold text-white group-hover:text-cyan-400 transition-colors">{{ $project->name }}</h3>
                                <span class="badge-cyan">{{ $project->image_uploads_count }} images</span>
                            </div>
                            <p class="text-sm text-slate-400 mb-5 line-clamp-2 leading-relaxed">{{ $project->description ?: 'No description' }}</p>
                            <div class="flex items-center gap-4 text-sm text-slate-400">
                                <span class="flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    {{ $project->image_uploads_count }} images
                                </span>
                                <span class="flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                    {{ $project->annotation_classes_count }} classes
                                </span>
                            </div>
                        </div>
                        <div class="px-6 pb-6 flex items-center gap-2">
                            <a href="{{ route('projects.show', $project) }}" class="btn-primary flex-1 text-center">Open Project</a>
                            <a href="{{ route('projects.edit', $project) }}" class="btn-secondary px-4">Edit</a>
                            <form action="{{ route('projects.destroy', $project) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this project?')">
                                @csrf @method('DELETE')
                                <button class="btn-danger px-4">Delete</button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="card p-16 text-center animate-fade-in">
                    <div class="w-20 h-20 mx-auto mb-6 rounded-2xl bg-gradient-to-br from-cyan-500/20 to-emerald-500/20 flex items-center justify-center">
                        <svg class="w-10 h-10 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-3">No projects yet</h3>
                    <p class="text-slate-400 mb-8 text-lg">Create your first project to start analyzing satellite imagery with AI</p>
                    <a href="{{ route('projects.create') }}" class="btn-primary text-base px-8 py-3">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Create Project
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

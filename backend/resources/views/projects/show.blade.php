<x-app-layout>
    <x-slot name="header">
        <div class="page-header">
            <div>
                <h1 class="page-title">{{ $project->name }}</h1>
                <p class="page-subtitle">{{ $project->description ?: 'Satellite imagery analysis project' }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('projects.edit', $project) }}" class="btn-secondary text-sm">Edit</a>
                <a href="{{ route('projects.index') }}" class="btn-secondary text-sm">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Projects
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-10 space-y-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Stats --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="stat-card animate-fade-in">
                    <div class="stat-icon bg-gradient-to-br from-cyan-500/20 to-cyan-400/10 text-cyan-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-400">Uploaded Images</p>
                        <p class="text-3xl font-bold text-white mt-1">{{ $project->image_uploads_count }}</p>
                    </div>
                </div>
                <div class="stat-card animate-fade-in" style="animation-delay: 0.1s">
                    <div class="stat-icon bg-gradient-to-br from-emerald-500/20 to-emerald-400/10 text-emerald-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-400">Annotation Classes</p>
                        <p class="text-3xl font-bold text-white mt-1">{{ $project->annotation_classes_count }}</p>
                    </div>
                </div>
                <div class="stat-card animate-fade-in" style="animation-delay: 0.2s">
                    <div class="stat-icon bg-gradient-to-br from-violet-500/20 to-violet-400/10 text-violet-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-400">Completed Annotations</p>
                        <p class="text-3xl font-bold text-white mt-1">{{ $project->annotations_count }}</p>
                    </div>
                </div>
            </div>

            {{-- Upload Image --}}
            <div class="card p-6 animate-slide-up">
                <h3 class="section-title">Upload Satellite Image</h3>
                <form action="{{ route('projects.images.upload', $project) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div class="border-2 border-dashed border-white/10 rounded-2xl p-10 text-center hover:border-cyan-500/50 transition-colors bg-white/5 cursor-pointer" onclick="document.getElementById('image').click()">
                        <svg class="w-12 h-12 mx-auto text-slate-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <p class="text-sm text-slate-400">Click to select a <strong class="text-slate-200">.tif</strong> image (4 bands)</p>
                        <input id="image" type="file" name="image" accept=".tif,.tiff" class="hidden" required onchange="this.form.submit()">
                    </div>
                    <x-input-error :messages="$errors->get('image')" />
                </form>
            </div>

            {{-- Uploaded Images --}}
            <div class="card p-6 animate-slide-up" style="animation-delay: 0.1s">
                <h3 class="section-title">Uploaded Images</h3>
                @if($project->imageUploads->count())
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>File Name</th>
                                    <th>Size</th>
                                    <th>Bands</th>
                                    <th>Annotations</th>
                                    <th>Uploaded</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($project->imageUploads as $img)
                                <tr>
                                    <td class="font-mono text-sm text-white">{{ $img->original_name }}</td>
                                    <td class="text-xs text-slate-400">{{ number_format($img->file_size / 1024, 1) }} KB</td>
                                    <td class="text-slate-300">{{ $img->bands ?? '—' }}</td>
                                    <td><span class="badge-emerald">{{ $img->annotations_count }}</span></td>
                                    <td class="text-xs text-slate-400">{{ $img->created_at->diffForHumans() }}</td>
                                    <td>
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('projects.annotate', [$project, $img]) }}" class="text-cyan-400 hover:text-cyan-300 text-sm font-semibold transition">Annotate</a>
                                            <a href="{{ route('projects.health-report', [$project, $img]) }}" class="text-emerald-400 hover:text-emerald-300 text-sm font-semibold transition">Health</a>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-10 text-slate-400 text-sm">No images uploaded yet</div>
                @endif
            </div>

            {{-- Annotation Classes --}}
            <div class="card p-6 animate-slide-up" style="animation-delay: 0.2s">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="section-title mb-0">Annotation Classes</h3>
                    <button onclick="document.getElementById('class-form').classList.toggle('hidden')" class="btn-primary text-sm">Add Class</button>
                </div>
                <form id="class-form" action="{{ route('projects.classes.store', $project) }}" method="POST" class="hidden mb-6">
                    @csrf
                    <div class="flex items-end gap-4">
                        <div class="flex-1">
                            <label class="input-label" for="name">Class Name</label>
                            <input id="name" class="input-field" type="text" name="name" required placeholder="e.g. Building">
                        </div>
                        <div class="w-32">
                            <label class="input-label" for="color">Color</label>
                            <input id="color" class="input-field h-11" type="color" name="color" value="#ff0000">
                        </div>
                        <button type="submit" class="btn-primary">Add</button>
                    </div>
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </form>
                @if($project->annotationClasses->count())
                    <div class="flex flex-wrap gap-3">
                        @foreach($project->annotationClasses as $class)
                        <div class="flex items-center gap-3 bg-white/5 rounded-xl px-4 py-2.5 border border-white/10">
                            <span class="w-5 h-5 rounded-full border-2 border-white/20 shadow-sm" style="background: {{ $class->color }}"></span>
                            <span class="font-medium text-sm text-slate-200">{{ $class->name }}</span>
                            <form action="{{ route('projects.classes.destroy', [$project, $class]) }}" method="POST" class="inline">
                                @csrf @method('DELETE')
                                <button class="text-slate-500 hover:text-red-400 transition p-1" onclick="return confirm('Delete this class?')">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </form>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-6 text-slate-400 text-sm">No annotation classes yet. Add a class to get started.</div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>

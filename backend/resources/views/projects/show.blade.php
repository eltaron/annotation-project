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

            {{-- Upload Image (chunked) --}}
            <div class="card p-6 animate-slide-up">
                <h3 class="section-title">Upload Satellite Image</h3>

                @if(session('error'))
                    <div class="bg-red-500/10 border border-red-500/30 text-red-300 px-4 py-3 rounded-xl mb-4 text-sm">{{ session('error') }}</div>
                @endif
                @if(session('success'))
                    <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 px-4 py-3 rounded-xl mb-4 text-sm">{{ session('success') }}</div>
                @endif

                <div id="upload-zone" class="border-2 border-dashed border-white/10 rounded-2xl p-10 text-center hover:border-cyan-500/50 transition-colors bg-white/5 cursor-pointer" onclick="document.getElementById('image').click()">
                    <svg class="w-12 h-12 mx-auto text-slate-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <p class="text-sm text-slate-400">Click to select a <strong class="text-slate-200">.tif</strong> image</p>
                    <p id="file-name" class="text-xs text-cyan-400 mt-2 hidden"></p>
                    <input id="image" type="file" accept=".tif,.tiff" class="hidden" required>
                </div>

                {{-- Progress bar --}}
                <div id="progress-wrap" class="hidden mt-4 space-y-2">
                    <div class="flex items-center justify-between text-xs text-slate-400">
                        <span id="progress-label">Uploading...</span>
                        <span id="progress-pct">0%</span>
                    </div>
                    <div class="w-full bg-white/10 rounded-full h-2 overflow-hidden">
                        <div id="progress-bar" class="h-full bg-gradient-to-r from-cyan-500 to-emerald-500 transition-all duration-300" style="width:0%"></div>
                    </div>
                    <p id="progress-error" class="text-xs text-red-400 hidden"></p>
                </div>

                <div class="flex items-center gap-3 mt-4">
                    <button type="button" id="upload-btn" class="btn-primary">
                        <span id="upload-text">Upload Image</span>
                        <span id="upload-spinner" class="hidden">
                            <svg class="animate-spin h-5 w-5 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
                        </span>
                    </button>
                </div>
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

    <script>
    (function() {
        var CHUNK_SIZE = 2 * 1024 * 1024; // 2MB per chunk

        var input = document.getElementById('image');
        var btn = document.getElementById('upload-btn');
        var nameDisplay = document.getElementById('file-name');
        var pw = document.getElementById('progress-wrap');
        var bar = document.getElementById('progress-bar');
        var pct = document.getElementById('progress-pct');
        var label = document.getElementById('progress-label');
        var errEl = document.getElementById('progress-error');
        var textEl = document.getElementById('upload-text');
        var spinnerEl = document.getElementById('upload-spinner');

        input.addEventListener('change', function() {
            var f = this.files && this.files[0];
            if (f) {
                nameDisplay.textContent = 'Selected: ' + f.name + ' (' + (f.size / 1048576).toFixed(1) + ' MB)';
                nameDisplay.classList.remove('hidden');
                errEl.classList.add('hidden');
            } else {
                nameDisplay.classList.add('hidden');
            }
        });

        function setProgress(pctVal, status) {
            bar.style.width = pctVal + '%';
            pct.textContent = pctVal + '%';
            label.textContent = status || 'Uploading...';
            pw.classList.remove('hidden');
        }

        function setError(msg) {
            errEl.textContent = msg;
            errEl.classList.remove('hidden');
            textEl.textContent = 'Upload Image';
            spinnerEl.classList.add('hidden');
            btn.disabled = false;
        }

        function uuid() {
            return 'xxxxxxxxxxxx4xxxyxxxxxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
                var r = Math.random() * 16 | 0;
                return (c === 'x' ? r : (r & 0x3 | 0x8)).toString(16);
            });
        }

        btn.addEventListener('click', function() {
            var file = input.files && input.files[0];
            if (!file) { return; }

            var ext = file.name.split('.').pop().toLowerCase();
            if (ext !== 'tif' && ext !== 'tiff') {
                setError('Only .tif/.tiff files are allowed.');
                return;
            }

            textEl.classList.add('hidden');
            spinnerEl.classList.remove('hidden');
            btn.disabled = true;
            errEl.classList.add('hidden');

            var totalChunks = Math.ceil(file.size / CHUNK_SIZE);
            if (totalChunks === 0) totalChunks = 1;
            var uploadId = uuid();
            var current = 0;

            function sendNext() {
                var start = current * CHUNK_SIZE;
                var end = Math.min(start + CHUNK_SIZE, file.size);
                var blob = file.slice(start, end);

                var formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('file', blob, 'chunk_' + current);
                formData.append('chunk', current);
                formData.append('chunks', totalChunks);
                formData.append('upload_id', uploadId);
                formData.append('original_name', file.name);

                var progress = Math.round((current + 1) / totalChunks * 100);
                setProgress(progress, 'Uploading chunk ' + (current + 1) + ' of ' + totalChunks);

                fetch('{{ route('projects.images.upload-chunk', $project) }}', {
                    method: 'POST',
                    body: formData
                })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.error) {
                        setError(data.error);
                        return;
                    }
                    if (data.done && data.redirect) {
                        setProgress(100, 'Complete!');
                        window.location.href = data.redirect;
                        return;
                    }
                    current++;
                    if (current < totalChunks) {
                        sendNext();
                    }
                })
                .catch(function(err) {
                    setError('Upload failed: ' + err.message);
                });
            }

            sendNext();
        });
    })();
    </script>
</x-app-layout>

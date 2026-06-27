<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $project->name }}</h2>
                <p class="text-sm text-gray-500">{{ $project->description }}</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('projects.health-report', $project) }}" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm hover:bg-emerald-700 transition">Crop Health Report</a>
                <a href="{{ route('projects.edit', $project) }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm hover:bg-gray-50 transition">Edit</a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-emerald-100 border border-emerald-400 text-emerald-700 px-4 py-3 rounded-lg mb-6">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">{{ session('error') }}</div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2">
                    <div class="bg-white overflow-hidden shadow-sm rounded-xl p-6 mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Upload Satellite Image</h3>
                        <form action="{{ route('projects.images.upload', $project) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-cyan-500 transition">
                                <div class="text-4xl mb-4">🛰️</div>
                                <p class="text-gray-600 mb-2">Upload a 4-band GeoTIFF satellite image</p>
                                <p class="text-sm text-gray-400 mb-4">Accepted format: .tif, .tiff (max 500MB)</p>
                                <input type="file" name="image" accept=".tif,.tiff" required class="block mx-auto text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-cyan-50 file:text-cyan-700 hover:file:bg-cyan-100">
                            </div>
                            @error('image') <p class="text-red-500 text-sm mt-2">{{ $message }}</p> @enderror
                            <div class="mt-4">
                                <button type="submit" class="px-6 py-3 bg-cyan-600 text-white rounded-lg hover:bg-cyan-700 transition font-medium">Upload & Process</button>
                            </div>
                        </form>
                    </div>

                    @if($project->imageUploads->count())
                    <div class="bg-white overflow-hidden shadow-sm rounded-xl p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Uploaded Images</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @foreach($project->imageUploads as $image)
                            <div class="border border-gray-200 rounded-lg p-4 hover:border-cyan-300 transition">
                                <div class="flex items-start justify-between mb-2">
                                    <div>
                                        <p class="font-medium text-gray-900 text-sm">{{ $image->original_name }}</p>
                                        <p class="text-xs text-gray-500">{{ $image->bands ?? '?' }} bands · {{ $image->width ?? '?' }}x{{ $image->height ?? '?' }}</p>
                                    </div>
                                    <a href="{{ route('projects.annotate', [$project, $image]) }}" class="text-cyan-600 hover:text-cyan-800 text-sm font-medium whitespace-nowrap">Annotate →</a>
                                </div>
                                <div class="text-xs text-gray-400">{{ $image->created_at->diffForHumans() }}</div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>

                <div>
                    <div class="bg-white overflow-hidden shadow-sm rounded-xl p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Classes</h3>
                            <button onclick="document.getElementById('createClassForm').classList.toggle('hidden')" class="text-cyan-600 hover:text-cyan-800 text-sm font-medium">+ Add</button>
                        </div>

                        <form id="createClassForm" action="{{ route('projects.classes.store', $project) }}" method="POST" class="hidden mb-4 p-4 bg-gray-50 rounded-lg">
                            @csrf
                            <div class="mb-3">
                                <label class="block text-xs font-medium text-gray-700 mb-1">Class Name</label>
                                <input type="text" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="e.g., Building">
                            </div>
                            <div class="mb-3">
                                <label class="block text-xs font-medium text-gray-700 mb-1">Color</label>
                                <input type="color" name="color" value="#FF00F7" class="w-full h-10 rounded-lg border border-gray-300 cursor-pointer">
                            </div>
                            <button type="submit" class="w-full px-4 py-2 bg-cyan-600 text-white rounded-lg text-sm hover:bg-cyan-700 transition">Add Class</button>
                        </form>

                        @if($project->annotationClasses->count())
                            <div class="space-y-2">
                                @foreach($project->annotationClasses as $class)
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div class="flex items-center gap-3">
                                        <span class="w-5 h-5 rounded-full border" style="background-color: {{ $class->color }}"></span>
                                        <span class="text-sm font-medium text-gray-900">{{ $class->name }}</span>
                                    </div>
                                    <form action="{{ route('projects.classes.destroy', [$project, $class]) }}" method="POST">
                                        @csrf @method('DELETE')
                                        <button class="text-red-500 hover:text-red-700 text-sm" onclick="return confirm('Delete this class?')">×</button>
                                    </form>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 text-sm text-center py-4">No classes created yet</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

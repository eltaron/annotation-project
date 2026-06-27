<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">My Projects</h2>
            <a href="{{ route('projects.create') }}" class="px-4 py-2 bg-cyan-600 text-white rounded-lg text-sm hover:bg-cyan-700 transition">+ New Project</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-emerald-100 border border-emerald-400 text-emerald-700 px-4 py-3 rounded-lg mb-6">{{ session('success') }}</div>
            @endif

            @if($projects->count())
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach($projects as $project)
                    <div class="bg-white overflow-hidden shadow-sm rounded-xl hover:shadow-md transition">
                        <div class="p-6">
                            <h3 class="text-lg font-bold text-gray-900 mb-2">{{ $project->name }}</h3>
                            <p class="text-gray-600 text-sm mb-4">{{ $project->description ?: 'No description' }}</p>
                            <div class="flex items-center gap-4 text-sm text-gray-500 mb-4">
                                <span>{{ $project->image_uploads_count }} images</span>
                                <span>{{ $project->annotation_classes_count }} classes</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('projects.show', $project) }}" class="flex-1 text-center px-4 py-2 bg-cyan-600 text-white rounded-lg text-sm hover:bg-cyan-700 transition">Open</a>
                                <a href="{{ route('projects.edit', $project) }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm hover:bg-gray-50 transition">Edit</a>
                                <form action="{{ route('projects.destroy', $project) }}" method="POST" onsubmit="return confirm('Delete this project?')">
                                    @csrf @method('DELETE')
                                    <button class="px-4 py-2 border border-red-300 text-red-600 rounded-lg text-sm hover:bg-red-50 transition">Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-20 bg-white rounded-xl shadow-sm">
                    <div class="text-6xl mb-4">📂</div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">No Projects Yet</h3>
                    <p class="text-gray-500 mb-6">Create your first project to start annotating satellite images</p>
                    <a href="{{ route('projects.create') }}" class="px-6 py-3 bg-cyan-600 text-white rounded-lg hover:bg-cyan-700 transition">Create Project</a>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

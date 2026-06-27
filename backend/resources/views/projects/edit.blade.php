<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Project</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-xl p-8">
                <form action="{{ route('projects.update', $project) }}" method="POST">
                    @csrf @method('PATCH')
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Project Name</label>
                        <input type="text" name="name" value="{{ old('name', $project->name) }}" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500">
                    </div>
                    <div class="mb-8">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea name="description" rows="4" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500">{{ old('description', $project->description) }}</textarea>
                    </div>
                    <div class="flex items-center gap-4">
                        <button type="submit" class="px-6 py-3 bg-cyan-600 text-white rounded-lg hover:bg-cyan-700 transition font-medium">Update Project</button>
                        <a href="{{ route('projects.show', $project) }}" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

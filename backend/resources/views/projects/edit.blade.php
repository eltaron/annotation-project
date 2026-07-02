<x-app-layout>
    <x-slot name="header">
        <div class="page-header">
            <div>
                <h1 class="page-title">Edit Project</h1>
                <p class="page-subtitle">Update project: {{ $project->name }}</p>
            </div>
            <a href="{{ route('projects.show', $project) }}" class="btn-secondary text-sm">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Back to Project
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="card p-8 animate-fade-in">
                <form action="{{ route('projects.update', $project) }}" method="POST" class="space-y-6">
                    @csrf @method('PUT')
                    <div>
                        <label class="input-label" for="name">Project Name</label>
                        <input id="name" class="input-field" type="text" name="name" value="{{ old('name', $project->name) }}" required>
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>
                    <div>
                        <label class="input-label" for="description">Description</label>
                        <textarea id="description" class="input-field" name="description" rows="4">{{ old('description', $project->description) }}</textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-2" />
                    </div>
                    <div class="flex items-center gap-4 pt-2">
                        <button type="submit" class="btn-primary">Save Changes</button>
                        <a href="{{ route('projects.show', $project) }}" class="btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white overflow-hidden shadow-sm rounded-xl p-6">
                    <div class="text-gray-500 text-sm font-medium">Total Projects</div>
                    <div class="text-4xl font-bold text-gray-900 mt-2">{{ $projectsCount }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm rounded-xl p-6">
                    <div class="text-gray-500 text-sm font-medium">Total Annotations</div>
                    <div class="text-4xl font-bold text-gray-900 mt-2">{{ $totalAnnotations }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm rounded-xl p-6">
                    <div class="text-gray-500 text-sm font-medium">Active Projects</div>
                    <div class="text-4xl font-bold text-gray-900 mt-2">{{ $recentProjects->count() }}</div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-xl p-6 mb-8">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Recent Projects</h3>
                    <a href="{{ route('projects.create') }}" class="px-4 py-2 bg-cyan-600 text-white rounded-lg text-sm hover:bg-cyan-700 transition">+ New Project</a>
                </div>
                @if($recentProjects->count())
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th class="py-3 text-sm font-medium text-gray-500">Name</th>
                                    <th class="py-3 text-sm font-medium text-gray-500">Images</th>
                                    <th class="py-3 text-sm font-medium text-gray-500">Annotations</th>
                                    <th class="py-3 text-sm font-medium text-gray-500">Created</th>
                                    <th class="py-3"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentProjects as $project)
                                <tr class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="py-4 font-medium text-gray-900">{{ $project->name }}</td>
                                    <td class="py-4 text-gray-600">{{ $project->image_uploads_count }}</td>
                                    <td class="py-4 text-gray-600">{{ $project->annotations_count }}</td>
                                    <td class="py-4 text-gray-500 text-sm">{{ $project->created_at->diffForHumans() }}</td>
                                    <td class="py-4">
                                        <a href="{{ route('projects.show', $project) }}" class="text-cyan-600 hover:text-cyan-800 text-sm font-medium">Open →</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-12 text-gray-500">
                        <p class="mb-4">No projects yet</p>
                        <a href="{{ route('projects.create') }}" class="text-cyan-600 hover:text-cyan-800 font-medium">Create your first project →</a>
                    </div>
                @endif
            </div>

            @if($latestHealthResults->count())
            <div class="bg-white overflow-hidden shadow-sm rounded-xl p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-6">Latest Crop Health Reports</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach($latestHealthResults as $result)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="text-sm text-gray-500 mb-1">Project #{{ $result->project_id }}</div>
                        <div class="text-lg font-bold text-gray-900">{{ $result->overall_status }}</div>
                        <div class="text-sm text-gray-600 mt-1">Health: {{ $result->healthy_percentage }}%</div>
                        <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                            <div class="bg-emerald-500 h-2 rounded-full" style="width: {{ $result->healthy_percentage }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>

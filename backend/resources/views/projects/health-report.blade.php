<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Crop Health Report: {{ $project->name }}</h2>
            <a href="{{ route('projects.show', $project) }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm hover:bg-gray-50 transition">← Back</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if($healthResult)
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-emerald-500">
                        <div class="text-sm text-gray-500">Total Area</div>
                        <div class="text-2xl font-bold text-gray-900">{{ number_format($healthResult->total_area_m2, 2) }} m²</div>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-emerald-500">
                        <div class="text-sm text-gray-500">Healthy</div>
                        <div class="text-2xl font-bold text-emerald-600">{{ $healthResult->healthy_percentage }}%</div>
                        <div class="text-xs text-gray-400">{{ number_format($healthResult->healthy_area_m2, 2) }} m²</div>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-yellow-500">
                        <div class="text-sm text-gray-500">Stressed</div>
                        <div class="text-2xl font-bold text-yellow-600">{{ $healthResult->stressed_percentage }}%</div>
                        <div class="text-xs text-gray-400">{{ number_format($healthResult->stressed_area_m2, 2) }} m²</div>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-red-500">
                        <div class="text-sm text-gray-500">Unhealthy</div>
                        <div class="text-2xl font-bold text-red-600">{{ $healthResult->unhealthy_percentage }}%</div>
                        <div class="text-xs text-gray-400">{{ number_format($healthResult->unhealthy_area_m2, 2) }} m²</div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Health Distribution</h3>
                        <canvas id="healthPieChart" height="300"></canvas>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Area by Category (m²)</h3>
                        <canvas id="healthBarChart" height="300"></canvas>
                    </div>
                </div>

                @if($healthResult->raw_stats)
                <div class="bg-white rounded-xl shadow-sm p-6 mt-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Detailed Statistics</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th class="py-3 text-sm font-medium text-gray-500">Category</th>
                                    <th class="py-3 text-sm font-medium text-gray-500">Percentage</th>
                                    <th class="py-3 text-sm font-medium text-gray-500">Area (m²)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($healthResult->raw_stats as $category => $data)
                                <tr class="border-b border-gray-100">
                                    <td class="py-3 font-medium text-gray-900">{{ $category }}</td>
                                    <td class="py-3 text-gray-600">{{ $data['percentage'] }}%</td>
                                    <td class="py-3 text-gray-600">{{ number_format($data['area_m2'], 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
            @else
                <div class="bg-white rounded-xl shadow-sm p-12 text-center">
                    <div class="text-6xl mb-4">🌾</div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">No Health Report Yet</h3>
                    <p class="text-gray-500 mb-6">Upload a satellite image and run crop health analysis from the annotation workspace.</p>
                    <a href="{{ route('projects.show', $project) }}" class="px-6 py-3 bg-cyan-600 text-white rounded-lg hover:bg-cyan-700 transition">Upload Image</a>
                </div>
            @endif
        </div>
    </div>

    @if($healthResult)
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    new Chart(document.getElementById('healthPieChart'), {
        type: 'pie',
        data: {
            labels: ['Healthy', 'Stressed', 'Unhealthy'],
            datasets: [{
                data: [
                    {{ $healthResult->healthy_percentage }},
                    {{ $healthResult->stressed_percentage }},
                    {{ $healthResult->unhealthy_percentage }}
                ],
                backgroundColor: ['#10b981', '#eab308', '#ef4444'],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });

    new Chart(document.getElementById('healthBarChart'), {
        type: 'bar',
        data: {
            labels: ['Healthy', 'Stressed', 'Unhealthy'],
            datasets: [{
                label: 'Area (m²)',
                data: [
                    {{ $healthResult->healthy_area_m2 }},
                    {{ $healthResult->stressed_area_m2 }},
                    {{ $healthResult->unhealthy_area_m2 }}
                ],
                backgroundColor: ['#10b981', '#eab308', '#ef4444'],
                borderRadius: 8,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
    </script>
    @endpush
    @endif
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">🌱 تقرير صحة المحاصيل: {{ $project->name }}</h2>
            <a href="{{ route('projects.show', $project) }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm hover:bg-gray-50 transition">→ رجوع</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if($healthResult)
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-xl shadow-sm p-6 border-r-4 border-cyan-500">
                        <div class="text-sm text-gray-500">المساحة الإجمالية</div>
                        <div class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($healthResult->total_area_m2, 2) }} م²</div>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm p-6 border-r-4 border-emerald-500">
                        <div class="text-sm text-gray-500">صحي</div>
                        <div class="text-2xl font-bold text-emerald-600 mt-1">{{ $healthResult->healthy_percentage }}%</div>
                        <div class="text-xs text-gray-400">{{ number_format($healthResult->healthy_area_m2, 2) }} م²</div>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm p-6 border-r-4 border-yellow-500">
                        <div class="text-sm text-gray-500">مجهد</div>
                        <div class="text-2xl font-bold text-yellow-600 mt-1">{{ $healthResult->stressed_percentage }}%</div>
                        <div class="text-xs text-gray-400">{{ number_format($healthResult->stressed_area_m2, 2) }} م²</div>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm p-6 border-r-4 border-red-500">
                        <div class="text-sm text-gray-500">غير صحي</div>
                        <div class="text-2xl font-bold text-red-600 mt-1">{{ $healthResult->unhealthy_percentage }}%</div>
                        <div class="text-xs text-gray-400">{{ number_format($healthResult->unhealthy_area_m2, 2) }} م²</div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">توزيع الصحة</h3>
                        <canvas id="healthPieChart" height="300"></canvas>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">المساحة حسب الفئة (م²)</h3>
                        <canvas id="healthBarChart" height="300"></canvas>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-6 mt-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">إحصائيات تفصيلية</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-right">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th class="py-3 text-sm font-medium text-gray-500">الفئة</th>
                                    <th class="py-3 text-sm font-medium text-gray-500">النسبة</th>
                                    <th class="py-3 text-sm font-medium text-gray-500">المساحة (م²)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $stats = json_decode(json_encode($healthResult->raw_stats), true) ?? [];
                                @endphp
                                @foreach($stats as $category => $data)
                                <tr class="border-b border-gray-100">
                                    <td class="py-3 font-medium text-gray-900">{{ is_string($category) ? $category : '—' }}</td>
                                    <td class="py-3 text-gray-600">{{ is_array($data) && isset($data['percentage']) ? $data['percentage'] . '%' : '—' }}</td>
                                    <td class="py-3 text-gray-600">{{ is_array($data) && isset($data['area_m2']) ? number_format($data['area_m2'], 2) : (is_numeric($data) ? number_format($data, 4) : $data) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="bg-white rounded-xl shadow-sm p-12 text-center">
                    <div class="text-7xl mb-4">🌾</div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">لا يوجد تقرير صحي بعد</h3>
                    <p class="text-gray-500 mb-6">ارفع صورة فضائية وقم بتحليل صحة المحاصيل من مساحة العمل</p>
                    <a href="{{ route('projects.show', $project) }}" class="px-6 py-3 bg-gradient-to-r from-cyan-600 to-emerald-600 text-white rounded-lg hover:from-cyan-700 hover:to-emerald-700 transition shadow-sm">رفع صورة</a>
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
            labels: ['صحي', 'مجهد', 'غير صحي'],
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
                legend: {
                    position: 'bottom',
                    labels: { font: { family: 'Cairo', size: 13 } }
                }
            }
        }
    });

    new Chart(document.getElementById('healthBarChart'), {
        type: 'bar',
        data: {
            labels: ['صحي', 'مجهد', 'غير صحي'],
            datasets: [{
                label: 'المساحة (م²)',
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
                y: {
                    beginAtZero: true,
                    ticks: { font: { family: 'Cairo' } }
                },
                x: {
                    ticks: { font: { family: 'Cairo', size: 13 } }
                }
            }
        }
    });
    </script>
    @endpush
    @endif
</x-app-layout>

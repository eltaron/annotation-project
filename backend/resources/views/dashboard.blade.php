<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">لوحة التحكم</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white overflow-hidden shadow-sm rounded-xl p-6 border-r-4 border-cyan-500">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-cyan-100 flex items-center justify-center text-cyan-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-gray-500 text-sm font-medium">إجمالي المشاريع</div>
                            <div class="text-3xl font-bold text-gray-900 mt-1">{{ $projectsCount }}</div>
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm rounded-xl p-6 border-r-4 border-emerald-500">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-emerald-100 flex items-center justify-center text-emerald-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-gray-500 text-sm font-medium">إجمالي التصنيفات</div>
                            <div class="text-3xl font-bold text-gray-900 mt-1">{{ $totalAnnotations }}</div>
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm rounded-xl p-6 border-r-4 border-violet-500">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-violet-100 flex items-center justify-center text-violet-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-gray-500 text-sm font-medium">المشاريع النشطة</div>
                            <div class="text-3xl font-bold text-gray-900 mt-1">{{ $recentProjects->count() }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-xl p-6 mb-8">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">آخر المشاريع</h3>
                    <a href="{{ route('projects.create') }}" class="px-4 py-2 bg-gradient-to-r from-cyan-600 to-emerald-600 text-white rounded-lg text-sm hover:from-cyan-700 hover:to-emerald-700 transition shadow-sm">+ مشروع جديد</a>
                </div>
                @if($recentProjects->count())
                    <div class="overflow-x-auto">
                        <table class="w-full text-right">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th class="py-3 text-sm font-medium text-gray-500">الاسم</th>
                                    <th class="py-3 text-sm font-medium text-gray-500">الصور</th>
                                    <th class="py-3 text-sm font-medium text-gray-500">التصنيفات</th>
                                    <th class="py-3 text-sm font-medium text-gray-500">تاريخ الإنشاء</th>
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
                                        <a href="{{ route('projects.show', $project) }}" class="text-cyan-600 hover:text-cyan-800 text-sm font-medium">فتح ←</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-12 text-gray-500">
                        <p class="mb-4">لا توجد مشاريع بعد</p>
                        <a href="{{ route('projects.create') }}" class="text-cyan-600 hover:text-cyan-800 font-medium">أنشئ أول مشروع لك ←</a>
                    </div>
                @endif
            </div>

            @if($latestHealthResults->count())
            <div class="bg-white overflow-hidden shadow-sm rounded-xl p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-6">آخر تقارير صحة المحاصيل</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach($latestHealthResults as $result)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="text-sm text-gray-500 mb-1">مشروع #{{ $result->project_id }}</div>
                        <div class="text-lg font-bold text-gray-900">{{ $result->overall_status }}</div>
                        <div class="text-sm text-gray-600 mt-1">الصحة: {{ $result->healthy_percentage }}%</div>
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

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">مشاريعي</h2>
            <a href="{{ route('projects.create') }}" class="px-4 py-2 bg-gradient-to-r from-cyan-600 to-emerald-600 text-white rounded-lg text-sm hover:from-cyan-700 hover:to-emerald-700 transition shadow-sm">+ مشروع جديد</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-emerald-100 border border-emerald-400 text-emerald-700 px-4 py-3 rounded-lg mb-6 text-right">{{ session('success') }}</div>
            @endif

            @if($projects->count())
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach($projects as $project)
                    <div class="bg-white overflow-hidden shadow-sm rounded-xl hover:shadow-md transition flex flex-col">
                        <div class="p-6 flex-1">
                            <div class="flex items-start justify-between mb-3">
                                <h3 class="text-lg font-bold text-gray-900">{{ $project->name }}</h3>
                                <span class="text-xs px-2 py-1 bg-cyan-100 text-cyan-700 rounded-full">{{ $project->image_uploads_count }} صور</span>
                            </div>
                            <p class="text-gray-600 text-sm mb-4 line-clamp-2">{{ $project->description ?: 'لا يوجد وصف' }}</p>
                            <div class="flex items-center gap-4 text-sm text-gray-500 mb-4">
                                <span class="flex items-center gap-1">📷 {{ $project->image_uploads_count }} صورة</span>
                                <span class="flex items-center gap-1">🏷️ {{ $project->annotation_classes_count }} كلاس</span>
                            </div>
                        </div>
                        <div class="px-6 pb-6 flex items-center gap-2">
                            <a href="{{ route('projects.show', $project) }}" class="flex-1 text-center px-4 py-2.5 bg-cyan-600 text-white rounded-lg text-sm hover:bg-cyan-700 transition font-medium">فتح</a>
                            <a href="{{ route('projects.edit', $project) }}" class="px-4 py-2.5 border border-gray-300 text-gray-700 rounded-lg text-sm hover:bg-gray-50 transition">تعديل</a>
                            <form action="{{ route('projects.destroy', $project) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا المشروع؟')">
                                @csrf @method('DELETE')
                                <button class="px-4 py-2.5 border border-red-300 text-red-600 rounded-lg text-sm hover:bg-red-50 transition">حذف</button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-20 bg-white rounded-xl shadow-sm">
                    <div class="text-7xl mb-4">📂</div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">لا توجد مشاريع بعد</h3>
                    <p class="text-gray-500 mb-6">أنشئ أول مشروع لتبدأ في تحليل الصور الفضائية</p>
                    <a href="{{ route('projects.create') }}" class="px-6 py-3 bg-gradient-to-r from-cyan-600 to-emerald-600 text-white rounded-lg hover:from-cyan-700 hover:to-emerald-700 transition shadow-sm">إنشاء مشروع</a>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

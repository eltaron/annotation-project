<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">مساحة التصنيف</h2>
                <p class="text-sm text-gray-500">{{ $project->name }} / {{ $imageUpload->original_name }}</p>
            </div>
            <a href="{{ route('projects.show', $project) }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm hover:bg-gray-50 transition">→ رجوع</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex gap-6 flex-col lg:flex-row">
                <div class="flex-1">
                    <div class="bg-white overflow-hidden shadow-sm rounded-xl p-4">
                        <div class="relative" id="imageContainer" style="max-height: 75vh; overflow: auto;">
                            <canvas id="annotationCanvas" class="w-full"></canvas>
                        </div>
                        <div class="flex items-center gap-4 mt-4 text-sm text-gray-500 flex-wrap">
                            <button id="zoomIn" class="px-3 py-1.5 bg-gray-100 rounded-lg hover:bg-gray-200 transition">🔍 تكبير</button>
                            <button id="zoomOut" class="px-3 py-1.5 bg-gray-100 rounded-lg hover:bg-gray-200 transition">🔍 تصغير</button>
                            <span id="zoomLevel" class="font-medium text-gray-700">100%</span>
                            <button id="undoBtn" class="px-3 py-1.5 bg-gray-100 rounded-lg hover:bg-gray-200 transition">↩ تراجع</button>
                            <span id="coordDisplay" class="text-xs text-gray-400 mr-auto">اضغط على الصورة للتقسيم</span>
                        </div>
                    </div>
                </div>

                <div class="w-full lg:w-80">
                    <div class="bg-white overflow-hidden shadow-sm rounded-xl p-6 mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">الكلاسات</h3>
                        @if($classes->count())
                            <div class="space-y-2" id="classesList">
                                @foreach($classes as $class)
                                <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 cursor-pointer hover:border-cyan-300 transition class-option" data-class-id="{{ $class->id }}" data-class-color="{{ $class->color }}">
                                    <input type="radio" name="activeClass" value="{{ $class->id }}" data-color="{{ $class->color }}" class="w-4 h-4 text-cyan-600">
                                    <span class="w-4 h-4 rounded-full border" style="background-color: {{ $class->color }}"></span>
                                    <span class="text-sm font-medium text-gray-900">{{ $class->name }}</span>
                                </label>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 text-sm text-center py-4">لا توجد كلاسات. <a href="{{ route('projects.show', $project) }}" class="text-cyan-600">أنشئ واحداً</a></p>
                        @endif
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm rounded-xl p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">معلومات التصنيف</h3>
                        <div id="annotationInfo" class="text-sm text-gray-600 space-y-2">
                            <p class="text-gray-400">اختر كلاس ثم اضغط على الصورة</p>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm rounded-xl p-6 mt-4">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">الإجراءات</h3>
                        <button id="exportGeoJSON" class="w-full px-4 py-2.5 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200 transition mb-2 font-medium">📥 تصدير GeoJSON</button>
                        <button id="analyzeHealth" class="w-full px-4 py-2.5 bg-gradient-to-r from-emerald-600 to-green-600 text-white rounded-lg text-sm hover:from-emerald-700 hover:to-green-700 transition font-medium shadow-sm">🌱 تحليل صحة المحاصيل</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
    $(function() {
        const canvas = document.getElementById('annotationCanvas');
        const ctx = canvas.getContext('2d');
        let img = new Image();
        let annotations = [];
        let activeClassId = null;
        let activeColor = '#22c55e';

        img.onload = function() {
            canvas.width = img.width;
            canvas.height = img.height;
            ctx.drawImage(img, 0, 0);
            drawAnnotations();
        };

        img.src = '{{ Storage::url($imageUpload->file_path) }}';

        $('input[name="activeClass"]').on('change', function() {
            activeClassId = $(this).val();
            activeColor = $(this).data('color');
            $('.class-option').removeClass('border-cyan-300 bg-cyan-50');
            $(this).closest('.class-option').addClass('border-cyan-300 bg-cyan-50');
        });

        canvas.addEventListener('click', function(e) {
            if (!activeClassId) {
                alert('الرجاء اختيار كلاس أولاً');
                return;
            }

            const rect = canvas.getBoundingClientRect();
            const scaleX = canvas.width / rect.width;
            const scaleY = canvas.height / rect.height;
            const x = Math.round((e.clientX - rect.left) * scaleX / zoom);
            const y = Math.round((e.clientY - rect.top) * scaleY / zoom);

            $.ajax({
                url: '{{ route("projects.segment", $project) }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    image_upload_id: {{ $imageUpload->id }},
                    click_x: x,
                    click_y: y,
                    click_type: 1,
                    class_id: activeClassId,
                    class_color: activeColor,
                },
                success: function(response) {
                    if (response.error) {
                        alert('فشل التقسيم: ' + response.error);
                        return;
                    }
                    annotations.push(response);
                    drawAnnotations();
                    updateAnnotationInfo(response);

                    if (response.id) {
                        $.ajax({
                            url: '{{ route("projects.classify", $project) }}',
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                image_upload_id: {{ $imageUpload->id }},
                                annotation_id: response.id,
                            },
                            success: function(clsResponse) {
                                const idx = annotations.findIndex(a => a.id === clsResponse.id);
                                if (idx !== -1) {
                                    annotations[idx] = clsResponse;
                                    updateAnnotationInfo(clsResponse);
                                }
                            }
                        });
                    }
                },
                error: function(xhr) {
                    alert('خطأ: ' + (xhr.responseJSON?.error || 'حدث خطأ غير متوقع'));
                }
            });
        });

        function drawAnnotations() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.drawImage(img, 0, 0);

            annotations.forEach(function(ann) {
                if (ann.annotation_class) {
                    ctx.fillStyle = ann.annotation_class.color + '40';
                    ctx.strokeStyle = ann.annotation_class.color;
                } else {
                    ctx.fillStyle = 'rgba(34, 197, 94, 0.25)';
                    ctx.strokeStyle = '#22c55e';
                }
                ctx.lineWidth = 2;
            });
        }

        function updateAnnotationInfo(data) {
            const label = data.classification_label || 'جارٍ التصنيف...';
            const confidence = data.classification_confidence || '--';
            const area = data.area_m2 ? parseFloat(data.area_m2).toLocaleString('ar-EG', {maximumFractionDigits: 2}) : '--';

            $('#annotationInfo').html(`
                <div class="border-b border-gray-100 pb-2 flex justify-between">
                    <span class="font-medium text-gray-900">${label}</span>
                    <span class="text-gray-400">التصنيف:</span>
                </div>
                <div class="border-b border-gray-100 pb-2 flex justify-between">
                    <span class="font-medium text-gray-900">${confidence}%</span>
                    <span class="text-gray-400">نسبة الثقة:</span>
                </div>
                <div class="border-b border-gray-100 pb-2 flex justify-between">
                    <span class="font-medium text-gray-900">${area} م²</span>
                    <span class="text-gray-400">المساحة:</span>
                </div>
                <div class="flex justify-between">
                    <span class="font-medium text-gray-900">${annotations.length}</span>
                    <span class="text-gray-400">عدد المضلعات:</span>
                </div>
            `);
        }

        let currentZoom = 1;
        $('#zoomIn').click(function() {
            currentZoom = Math.min(currentZoom + 0.2, 3);
            canvas.style.transform = 'scale(' + currentZoom + ')';
            canvas.style.transformOrigin = 'top left';
            $('#zoomLevel').text(Math.round(currentZoom * 100) + '%');
        });
        $('#zoomOut').click(function() {
            currentZoom = Math.max(currentZoom - 0.2, 0.2);
            canvas.style.transform = 'scale(' + currentZoom + ')';
            canvas.style.transformOrigin = 'top left';
            $('#zoomLevel').text(Math.round(currentZoom * 100) + '%');
        });

        $('#undoBtn').click(function() {
            if (annotations.length > 0) {
                annotations.pop();
                drawAnnotations();
                if (annotations.length > 0) {
                    updateAnnotationInfo(annotations[annotations.length - 1]);
                } else {
                    $('#annotationInfo').html('<p class="text-gray-400">اختر كلاس ثم اضغط على الصورة</p>');
                }
            }
        });

        $('#exportGeoJSON').click(function() {
            $.get('{{ route("projects.annotations.index", $project) }}', function(data) {
                const geojson = {
                    type: 'FeatureCollection',
                    features: data.map(function(ann) {
                        return {
                            type: 'Feature',
                            properties: {
                                class: ann.annotation_class?.name || 'Unknown',
                                color: ann.annotation_class?.color || '#000',
                                label: ann.classification_label,
                                confidence: ann.classification_confidence,
                                area_m2: ann.area_m2,
                            },
                            geometry: ann.polygon_coordinates || null
                        };
                    })
                };
                const blob = new Blob([JSON.stringify(geojson, null, 2)], {type: 'application/json'});
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url; a.download = 'annotations.geojson'; a.click();
            });
        });

        $('#analyzeHealth').click(function() {
            $.ajax({
                url: '{{ route("projects.analyze-health", $project) }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    image_upload_id: {{ $imageUpload->id }},
                },
                success: function(response) {
                    if (response.error) {
                        alert('فشل التحليل: ' + response.error);
                        return;
                    }
                    const statusClass = response.overall_status === 'Good' ? 'text-emerald-600' :
                        response.overall_status === 'Moderate' ? 'text-yellow-600' : 'text-red-600';
                    $('#annotationInfo').html(`
                        <div class="text-sm">
                            <div class="font-bold text-gray-900 mb-2 text-center">🌱 تقرير صحة المحاصيل</div>
                            <div class="border-b border-gray-100 pb-2 flex justify-between">
                                <span class="font-medium ${statusClass}">${response.overall_status}</span>
                                <span class="text-gray-400">الحالة:</span>
                            </div>
                            <div class="border-b border-gray-100 pb-2 flex justify-between">
                                <span class="font-medium text-emerald-600">${response.healthy_percentage}%</span>
                                <span class="text-gray-400">صحي:</span>
                            </div>
                            <div class="border-b border-gray-100 pb-2 flex justify-between">
                                <span class="font-medium text-yellow-600">${response.stressed_percentage}%</span>
                                <span class="text-gray-400">مجهد:</span>
                            </div>
                            <div class="border-b border-gray-100 pb-2 flex justify-between">
                                <span class="font-medium text-red-600">${response.unhealthy_percentage}%</span>
                                <span class="text-gray-400">غير صحي:</span>
                            </div>
                            <div class="pt-2 flex justify-between">
                                <span class="font-medium text-gray-900">${parseFloat(response.total_area_m2).toLocaleString('ar-EG', {maximumFractionDigits: 2})} م²</span>
                                <span class="text-gray-400">المساحة الإجمالية:</span>
                            </div>
                        </div>
                    `);
                }
            });
        });
    });
    </script>
    @endpush

    @push('styles')
    <style>
        canvas { cursor: crosshair; }
        #imageContainer { overflow: hidden; }
    </style>
    @endpush
</x-app-layout>

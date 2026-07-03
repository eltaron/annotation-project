<x-app-layout>
    <x-slot name="header">
        <div class="page-header">
            <div>
                <h1 class="page-title">Annotation Workspace</h1>
                <p class="page-subtitle">{{ $project->name }} — {{ $imageUpload->original_name }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('projects.health-report', [$project, $imageUpload]) }}" class="btn-secondary text-sm">Health Analysis</a>
                <a href="{{ route('projects.show', $project) }}" class="btn-secondary text-sm">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Back
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="alert-success mb-6 animate-fade-in">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert-error mb-6 animate-fade-in">{{ session('error') }}</div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                {{-- Sidebar --}}
                <div class="lg:col-span-1 space-y-6">
                    <div class="card p-5 animate-fade-in">
                        <h4 class="font-bold text-white mb-4 text-sm">Annotation Classes</h4>
                        @if($classes->count())
                            <div class="space-y-2">
                                @foreach($classes as $class)
                                <label class="flex items-center gap-3 p-2.5 rounded-xl border border-white/10 cursor-pointer transition-all hover:bg-white/5 {{ $selectedClassId == $class->id ? 'bg-cyan-500/10 border-cyan-500/30' : '' }}">
                                    <input type="radio" name="class_id" value="{{ $class->id }}" class="w-4 h-4 text-cyan-600 accent-cyan-600"
                                        {{ $selectedClassId == $class->id ? 'checked' : '' }}
                                        onchange="window.location='{{ route('projects.annotate', [$project, $imageUpload, 'class_id' => $class->id]) }}'">
                                    <span class="w-4 h-4 rounded-full border-2 border-white/20 shadow-sm flex-shrink-0" style="background: {{ $class->color }}"></span>
                                    <span class="text-sm font-medium text-slate-200">{{ $class->name }}</span>
                                </label>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-slate-400">No classes yet. Add classes from the project page.</p>
                        @endif
                    </div>

                    <div class="card p-5 animate-fade-in">
                        <h4 class="font-bold text-white mb-3 text-sm">AI Tools</h4>
                        <div class="space-y-2">
                            <button id="classifyBtn" class="btn-secondary w-full text-sm justify-center" onclick="classifyImage()">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                AI Classification
                            </button>
                            <button class="btn-secondary w-full text-sm justify-center" onclick="analyzeHealth()">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                Analyze Health
                            </button>
                        </div>
                    </div>

                    <div class="card p-5 animate-fade-in">
                        <h4 class="font-bold text-white mb-3 text-sm">Export</h4>
                        <div class="space-y-2">
                            <button class="btn-secondary w-full text-sm justify-center" onclick="exportGeoJSON()">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                GeoJSON
                            </button>
                            <button class="btn-secondary w-full text-sm justify-center" onclick="exportCOCO()">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/></svg>
                                COCO Format
                            </button>
                            <button class="btn-secondary w-full text-sm justify-center" onclick="exportCSV()">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                CSV Report
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Canvas --}}
                <div class="lg:col-span-3">
                    <div class="card p-5 animate-fade-in">
                            <div class="relative bg-slate-900 rounded-2xl overflow-hidden" id="canvasContainer" style="min-height: 500px">
                            <div id="toastContainer" style="position: absolute; top: 12px; right: 12px; z-index: 100; max-width: 340px; pointer-events: none;"></div>
                            <canvas id="imageCanvas" class="w-full cursor-crosshair"></canvas>
                            <div id="loadingOverlay" class="absolute inset-0 bg-slate-900/80 backdrop-blur-sm flex items-center justify-center hidden">
                                <div class="text-center">
                                    <div class="w-10 h-10 border-4 border-cyan-400 border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
                                    <p class="text-sm text-slate-300 font-medium">Processing...</p>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center justify-between mt-4">
                            <div class="flex items-center gap-2">
                                <button class="btn-secondary text-xs px-3 py-1.5 flex items-center gap-1" onclick="zoomIn()">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg>
                                </button>
                                <button class="btn-secondary text-xs px-3 py-1.5 flex items-center gap-1" onclick="zoomOut()">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM13 10H7"/></svg>
                                </button>
                                <button class="btn-secondary text-xs px-3 py-1.5 flex items-center gap-1" onclick="undoLast()">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                    Undo
                                </button>
                            </div>
                            <p class="text-xs text-slate-400">Click on the image to start annotating</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const annotations = @json($imageUpload->annotations ?? []);
    const projectId = {{ $project->id }};
    const imageUploadId = {{ $imageUpload->id }};
    const csrfToken = '{{ csrf_token() }}';
    const segmentUrl = '{{ route("projects.segment", $project) }}';
    const classifyUrl = '{{ route("projects.classify", $project) }}';
    const healthUrl = '{{ route("projects.analyze-health", $project) }}';
    const healthReportUrl = '{{ route("projects.health-report", [$project, $imageUpload]) }}';

    const canvas = document.getElementById('imageCanvas');
    const ctx = canvas.getContext('2d');
    const container = document.getElementById('canvasContainer');
    const overlay = document.getElementById('loadingOverlay');

    let img = new Image();
    let scale = 1;
    let offsetX = 0, offsetY = 0;
    let lastClick = null;
    let annotationHistory = [];
    let isDragging = false, dragStartX, dragStartY;

    function resize() {
        canvas.width = container.clientWidth;
        canvas.height = container.clientHeight;
    }

    function drawImage() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ctx.fillStyle = '#0f172a';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        if (!img.width) return;
        const iw = img.width * scale;
        const ih = img.height * scale;
        offsetX = (canvas.width - iw) / 2;
        offsetY = (canvas.height - ih) / 2;
        ctx.drawImage(img, offsetX, offsetY, iw, ih);
    }

    function getRings(poly) {
        if (!poly || !Array.isArray(poly)) return [];
        if (poly.length === 0) return [];
        if (Array.isArray(poly[0]) && Array.isArray(poly[0][0]) && typeof poly[0][0][0] === 'number') {
            return poly;
        }
        if (poly[0] && poly[0].geometry && poly[0].geometry.coordinates) {
            var rings = [];
            poly.forEach(function(f) {
                if (f.geometry && f.geometry.coordinates) {
                    rings = rings.concat(f.geometry.coordinates);
                }
            });
            return rings;
        }
        return poly;
    }

    function drawAnnotations() {
        annotations.forEach(function(a) {
            var rings = getRings(a.polygon_coordinates);
            if (rings.length === 0) return;
            if (!Array.isArray(rings[0]) || !Array.isArray(rings[0][0])) return;
            const color = a.annotation_class?.color || '#10b981';
            ctx.save();
            rings.forEach(function(ring) {
                ctx.beginPath();
                ring.forEach(function(pt, i) {
                    const px = pt[0] * scale + offsetX;
                    const py = pt[1] * scale + offsetY;
                    i === 0 ? ctx.moveTo(px, py) : ctx.lineTo(px, py);
                });
                ctx.closePath();
                ctx.fillStyle = color + '30';
                ctx.fill();
                ctx.strokeStyle = color;
                ctx.lineWidth = 2;
                ctx.stroke();
            });
            ctx.restore();
        });
    }

    function showLoading() { overlay.classList.remove('hidden'); }
    function hideLoading() { overlay.classList.add('hidden'); }

    function showToast(message, type) {
        type = type || 'success';
        var colors = {
            success: 'border-emerald-500/50 bg-slate-800/95 shadow-lg shadow-emerald-500/10',
            error: 'border-red-500/50 bg-slate-800/95 shadow-lg shadow-red-500/10',
            info: 'border-cyan-500/50 bg-slate-800/95 shadow-lg shadow-cyan-500/10'
        };
        var icons = { success: '✓', error: '✕', info: 'ℹ' };
        var el = document.createElement('div');
        el.className = 'flex items-start gap-3 px-4 py-3 rounded-xl border ' + (colors[type] || colors.info) + ' backdrop-blur-xl animate-fade-in';
        el.innerHTML = '<span class="text-sm font-medium leading-snug text-slate-100">' + message + '</span>';
        document.getElementById('toastContainer').appendChild(el);
        setTimeout(function() { if (el.parentNode) el.parentNode.removeChild(el); }, 4000);
    }

    resize();

    const imgUrl = '{{ route("projects.images.preview", [$project, $imageUpload]) }}?t=' + Date.now();
    img.onload = function() {
        drawImage();
        drawAnnotations();
    };
    img.onerror = function() {
        ctx.fillStyle = '#0f172a';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx.fillStyle = '#64748b';
        ctx.font = '16px Cairo, sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText('Image preview not available for .tif files', canvas.width / 2, canvas.height / 2);
    };
    img.src = imgUrl;

    canvas.onmousedown = function(e) {
        const rect = canvas.getBoundingClientRect();
        const x = (e.clientX - rect.left - offsetX) / scale;
        const y = (e.clientY - rect.top - offsetY) / scale;
        if (x < 0 || y < 0 || x > img.width || y > img.height) return;
        lastClick = { x: Math.round(x), y: Math.round(y) };
        canvas.style.cursor = 'crosshair';
    };

    canvas.onclick = function(e) {
        if (!lastClick) return;
        const selectedClass = document.querySelector('input[name="class_id"]:checked');
        if (!selectedClass) { showToast('Please select an annotation class first.', 'info'); return; }
        runSam(lastClick.x, lastClick.y, selectedClass.value);
    };

    canvas.onwheel = function(e) {
        e.preventDefault();
        const delta = e.deltaY > 0 ? 0.9 : 1.1;
        scale *= delta;
        scale = Math.min(Math.max(scale, 0.5), 5);
        drawImage();
        drawAnnotations();
    };

    window.zoomIn = function() {
        scale = Math.min(scale * 1.3, 5);
        drawImage();
        drawAnnotations();
    };

    window.zoomOut = function() {
        scale = Math.max(scale / 1.3, 0.5);
        drawImage();
        drawAnnotations();
    };

    window.undoLast = function() {
        if (annotationHistory.length === 0) { showToast('Nothing to undo.', 'info'); return; }
        const last = annotationHistory.pop();
        const idx = annotations.findIndex(function(a) { return a.id === last.id; });
        if (idx !== -1) annotations.splice(idx, 1);
        drawImage();
        drawAnnotations();
        showToast('Last annotation undone');
    };

    window.runSam = function(clickX, clickY, classId) {
        showLoading();
        fetch(segmentUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify({
                image_upload_id: imageUploadId,
                click_x: clickX,
                click_y: clickY,
                click_type: 1,
                class_id: classId,
            })
        }).then(function(r) {
            if (!r.ok) {
                return r.text().then(function(text) {
                    var msg = 'Server returned ' + r.status;
                    try { var j = JSON.parse(text); msg = j.error || msg; } catch(e) {}
                    throw new Error(msg);
                });
            }
            return r.json();
        }).then(function(data) {
            if (data.error) { showToast('SAM Error: ' + data.error, 'error'); return; }
            annotations.push(data);
            annotationHistory.push(data);
            drawImage();
            drawAnnotations();
            var name = data.annotation_class?.name || 'Unknown';
            showToast('Annotated as "' + name + '" — ' + (data.area_pixels || 0).toLocaleString() + ' px, ' + (data.area_m2 ? data.area_m2.toFixed(1) + ' m²' : 'N/A'));
        }).catch(function(err) {
            showToast('SAM Error: ' + err.message, 'error');
        }).finally(function() {
            hideLoading();
        });
    };

    function handleResponse(r) {
        if (!r.ok) {
            return r.text().then(function(text) {
                var msg = 'Server returned ' + r.status;
                try { var j = JSON.parse(text); msg = j.error || msg; } catch(e) {}
                throw new Error(msg);
            });
        }
        return r.json();
    }

    window.classifyImage = function() {
        if (annotations.length === 0) { showToast('No annotations to classify.', 'info'); return; }
        const lastAnnotation = annotations[annotations.length - 1];
        if (!lastAnnotation.id) { showToast('Annotation has no ID.', 'info'); return; }
        showLoading();
        fetch(classifyUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify({
                image_upload_id: imageUploadId,
                annotation_id: lastAnnotation.id,
            })
        }).then(handleResponse).then(function(data) {
            if (data.error) { showToast('Classification Error: ' + data.error, 'error'); return; }
            const idx = annotations.findIndex(function(a) { return a.id === data.id; });
            if (idx !== -1) annotations[idx] = data;
            var label = data.classification_label || 'N/A';
            var conf = data.classification_confidence ? (data.classification_confidence * 100).toFixed(1) + '%' : 'N/A';
            showToast('Classified as "' + label + '" — confidence: ' + conf);
        }).catch(function(err) {
            showToast('Classification Error: ' + err.message, 'error');
        }).finally(function() {
            hideLoading();
        });
    };

    window.analyzeHealth = function() {
        showLoading();
        fetch(healthUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify({ image_upload_id: imageUploadId })
        }).then(handleResponse).then(function(data) {
            if (data.error) { showToast('Health Analysis Error: ' + data.error, 'error'); return; }
            showToast('Health analysis complete — redirecting to report...');
            setTimeout(function() { window.location.href = healthReportUrl; }, 1000);
        }).catch(function(err) {
            showToast('Health Analysis Error: ' + err.message, 'error');
        }).finally(function() {
            hideLoading();
        });
    };

    function downloadFile(content, filename, mime) {
        var blob = new Blob([content], { type: mime });
        var url = URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;
        a.download = filename;
        a.click();
        URL.revokeObjectURL(url);
    }

    window.exportGeoJSON = function() {
        if (annotations.length === 0) { showToast('No annotations to export.', 'info'); return; }
        var features = annotations.filter(function(a) { return a.polygon_coordinates; }).map(function(a) {
            var rings = getRings(a.polygon_coordinates).map(function(ring) {
                return ring.map(function(pt) { return [pt[0], pt[1]]; });
            });
            return {
                type: 'Feature',
                geometry: { type: 'Polygon', coordinates: rings },
                properties: {
                    class: a.annotation_class?.name || a.annotation_class || 'Unknown',
                    class_id: a.annotation_class_id,
                    area_m2: a.area_m2,
                    area_pixels: a.area_pixels,
                    classification_label: a.classification_label,
                    classification_confidence: a.classification_confidence,
                }
            };
        });
        downloadFile(JSON.stringify({ type: 'FeatureCollection', features: features }, null, 2), 'annotations.geojson', 'application/json');
        showToast('Exported ' + features.length + ' annotations as GeoJSON');
    };

    window.exportCOCO = function() {
        if (annotations.length === 0) { showToast('No annotations to export.', 'info'); return; }
        var cats = {}, catIdx = 1;
        var coco_annotations = [];
        annotations.forEach(function(a, i) {
            var catName = a.annotation_class?.name || a.annotation_class || 'Unknown';
            if (!cats[catName]) { cats[catName] = catIdx++; }
            var rings = a.polygon_coordinates ? getRings(a.polygon_coordinates).map(function(ring) {
                return ring.map(function(pt) { return pt[0]; }).concat(ring.map(function(pt) { return pt[1]; }));
            }) : [];
            var seg = rings.length > 0 ? rings[0] : [];
            var bbox = seg.length > 0 ? [
                Math.min.apply(null, seg.slice(0, seg.length/2)),
                Math.min.apply(null, seg.slice(seg.length/2)),
                Math.max.apply(null, seg.slice(0, seg.length/2)) - Math.min.apply(null, seg.slice(0, seg.length/2)),
                Math.max.apply(null, seg.slice(seg.length/2)) - Math.min.apply(null, seg.slice(seg.length/2))
            ] : [0,0,0,0];
            coco_annotations.push({
                id: a.id || (i + 1),
                image_id: imageUploadId,
                category_id: cats[catName],
                segmentation: [seg],
                area: a.area_pixels || 0,
                bbox: bbox,
                attributes: { classification: a.classification_label, confidence: a.classification_confidence }
            });
        });
        var coco = {
            info: { description: 'Geo Annotate export', date_created: new Date().toISOString() },
            images: [{ id: imageUploadId, file_name: '{{ $imageUpload->original_name }}', width: img.width, height: img.height }],
            categories: Object.keys(cats).map(function(n) { return { id: cats[n], name: n, supercategory: 'none' }; }),
            annotations: coco_annotations
        };
        downloadFile(JSON.stringify(coco, null, 2), 'annotations_coco.json', 'application/json');
        showToast('Exported ' + coco_annotations.length + ' annotations as COCO format');
    };

    window.exportCSV = function() {
        if (annotations.length === 0) { showToast('No annotations to export.', 'info'); return; }
        var rows = [['class_id','class_name','area_pixels','area_m2','classification','confidence','points'].join(',')];
        annotations.forEach(function(a) {
            var pts = a.polygon_coordinates ? getRings(a.polygon_coordinates).map(function(r) {
                return r.map(function(p) { return p.join(' '); }).join(';');
            }).join('|') : '';
            rows.push([
                a.annotation_class_id,
                '"' + (a.annotation_class?.name || a.annotation_class || 'Unknown') + '"',
                a.area_pixels || 0,
                a.area_m2 || 0,
                '"' + (a.classification_label || '') + '"',
                a.classification_confidence || '',
                '"' + pts + '"'
            ].join(','));
        });
        downloadFile(rows.join('\n'), 'annotations_report.csv', 'text/csv');
        showToast('Exported ' + (rows.length - 1) + ' annotations as CSV report');
    };

    window.addEventListener('resize', function() {
        resize();
        drawImage();
        drawAnnotations();
    });
});
</script>

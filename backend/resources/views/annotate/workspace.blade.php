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
                        <h4 class="font-bold text-white mb-3 text-sm">Tools</h4>
                        <div class="space-y-2">
                            <button id="runSamBtn" class="btn-primary w-full text-sm justify-center" onclick="runSam()">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Run SAM
                            </button>
                            <button id="classifyBtn" class="btn-secondary w-full text-sm justify-center" onclick="classifyImage()">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                AI Classification
                            </button>
                            <button class="btn-secondary w-full text-sm justify-center" onclick="exportGeoJSON()">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                Export GeoJSON
                            </button>
                            <button class="btn-secondary w-full text-sm justify-center" onclick="analyzeHealth()">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                Analyze Health
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Canvas --}}
                <div class="lg:col-span-3">
                    <div class="card p-5 animate-fade-in">
                        <div class="relative bg-slate-900 rounded-2xl overflow-hidden" id="canvasContainer">
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

@push('scripts')
<script>
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

function initCanvas() {
    canvas.width = container.clientWidth;
    canvas.height = container.clientHeight;
    const imgUrl = '{{ route("projects.images.preview", [$project, $imageUpload]) }}';
    img.crossOrigin = 'anonymous';
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
        if (!selectedClass) { alert('Please select an annotation class first.'); return; }
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

function drawAnnotations() {
    annotations.forEach(function(a) {
        const poly = a.polygon_coordinates;
        if (!poly || !Array.isArray(poly)) return;
        const color = a.annotation_class?.color || '#10b981';
        ctx.save();
        poly.forEach(function(ring) {
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

function runSam(clickX, clickY, classId) {
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
    }).then(function(r) { return r.json(); }).then(function(data) {
        if (data.error) { alert('SAM Error: ' + data.error); return; }
        data.annotation_class = document.querySelector('input[name="class_id"]:checked')?.nextElementSibling?.textContent?.trim() || 'Unknown';
        annotations.push(data);
        annotationHistory.push(data);
        drawImage();
        drawAnnotations();
    }).catch(function() {
        alert('Connection error during segmentation.');
    }).finally(function() {
        hideLoading();
    });
}

function classifyImage() {
    if (annotations.length === 0) { alert('No annotations to classify.'); return; }
    const lastAnnotation = annotations[annotations.length - 1];
    if (!lastAnnotation.id) { alert('Annotation has no ID.'); return; }
    showLoading();
    fetch(classifyUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify({
            image_upload_id: imageUploadId,
            annotation_id: lastAnnotation.id,
        })
    }).then(function(r) { return r.json(); }).then(function(data) {
        if (data.error) { alert('Classification Error: ' + data.error); return; }
        const idx = annotations.findIndex(function(a) { return a.id === data.id; });
        if (idx !== -1) annotations[idx] = data;
        alert('Classified as: ' + (data.classification_label || 'N/A') + ' (confidence: ' + (data.classification_confidence ? (data.classification_confidence * 100).toFixed(1) + '%' : 'N/A') + ')');
    }).catch(function() {
        alert('Connection error during classification.');
    }).finally(function() {
        hideLoading();
    });
}

function analyzeHealth() {
    showLoading();
    fetch(healthUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify({ image_upload_id: imageUploadId })
    }).then(function(r) { return r.json(); }).then(function(data) {
        if (data.error) { alert('Health Analysis Error: ' + data.error); return; }
        window.location.href = healthReportUrl;
    }).catch(function() {
        alert('Connection error during health analysis.');
    }).finally(function() {
        hideLoading();
    });
}

function exportGeoJSON() {
    if (annotations.length === 0) { alert('No annotations to export.'); return; }
    const features = annotations.filter(function(a) { return a.polygon_coordinates; }).map(function(a) {
        const rings = a.polygon_coordinates.map(function(ring) {
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
    const geojson = { type: 'FeatureCollection', features: features };
    const blob = new Blob([JSON.stringify(geojson, null, 2)], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'annotations.geojson';
    a.click();
    URL.revokeObjectURL(url);
}

function zoomIn() {
    scale = Math.min(scale * 1.3, 5);
    drawImage();
    drawAnnotations();
}

function zoomOut() {
    scale = Math.max(scale / 1.3, 0.5);
    drawImage();
    drawAnnotations();
}

function undoLast() {
    if (annotationHistory.length === 0) { alert('Nothing to undo.'); return; }
    const last = annotationHistory.pop();
    const idx = annotations.findIndex(function(a) { return a.id === last.id; });
    if (idx !== -1) annotations.splice(idx, 1);
    drawImage();
    drawAnnotations();
}

window.addEventListener('resize', function() {
    canvas.width = container.clientWidth;
    canvas.height = container.clientHeight;
    drawImage();
    drawAnnotations();
});

document.addEventListener('DOMContentLoaded', initCanvas);
</script>
@endpush

<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ImageUpload;
use App\Models\Annotation;
use App\Models\CropHealthResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PythonBridgeController extends Controller
{
    private $pythonPath;
    private $projectBasePath;

    public function __construct()
    {
        $this->pythonPath = 'python';
        $this->projectBasePath = str_replace('\\', '/', base_path('..'));
    }

    public function segment(Request $request, Project $project)
    {
        if ($project->user_id !== Auth::id()) abort(403);

        $request->validate([
            'image_upload_id' => 'required|exists:image_uploads,id',
            'click_x' => 'required|numeric',
            'click_y' => 'required|numeric',
            'click_type' => 'required|integer|in:0,1',
            'class_id' => 'required|exists:annotation_classes,id',
            'class_color' => 'nullable|string',
        ]);

        $imageUpload = ImageUpload::findOrFail($request->image_upload_id);
        $imagePath = str_replace('\\', '/', Storage::disk('public')->path($imageUpload->file_path));
        $annotationClass = $project->annotationClasses()->findOrFail($request->class_id);
        $color = $request->class_color ?? $annotationClass->color;

        $result = $this->callPythonSamSegmenter(
            $imagePath, $request->click_x, $request->click_y,
            $request->click_type, $request->class_id, $color
        );

        if (!$result || isset($result['error'])) {
            return response()->json(['error' => $result['error'] ?? 'Segmentation failed'], 500);
        }

        $annotation = Annotation::create([
            'image_upload_id' => $imageUpload->id,
            'annotation_class_id' => $request->class_id,
            'user_id' => Auth::id(),
            'polygon_coordinates' => $result['polygons'] ?? null,
            'area_pixels' => $result['area_pixels'] ?? 0,
            'area_m2' => $result['area_m2'] ?? 0,
            'classification_label' => $result['classification_label'] ?? null,
            'classification_confidence' => $result['classification_confidence'] ?? null,
            'geo_metadata' => $result['geo_metadata'] ?? null,
        ]);

        return response()->json($annotation->load('annotationClass'), 201);
    }

    public function classify(Request $request, Project $project)
    {
        if ($project->user_id !== Auth::id()) abort(403);

        $request->validate([
            'image_upload_id' => 'required|exists:image_uploads,id',
            'annotation_id' => 'required|exists:annotations,id',
        ]);

        $annotation = Annotation::findOrFail($request->annotation_id);
        $imageUpload = ImageUpload::findOrFail($request->image_upload_id);
        $imagePath = str_replace('\\', '/', Storage::disk('public')->path($imageUpload->file_path));

        $result = $this->callPythonClassifier($imagePath);

        if ($result && !isset($result['error'])) {
            $annotation->update([
                'classification_label' => $result['label'],
                'classification_confidence' => $result['confidence'],
            ]);
        }

        return response()->json($annotation->load('annotationClass'));
    }

    public function analyzeHealth(Request $request, Project $project)
    {
        if ($project->user_id !== Auth::id()) abort(403);

        $request->validate([
            'image_upload_id' => 'required|exists:image_uploads,id',
        ]);

        $imageUpload = ImageUpload::findOrFail($request->image_upload_id);
        $imagePath = str_replace('\\', '/', Storage::disk('public')->path($imageUpload->file_path));

        $stats = $this->callPythonGeoProcessor($imagePath);

        if (!$stats || isset($stats['error'])) {
            return response()->json(['error' => 'Health analysis failed'], 500);
        }

        $totalArea = array_sum(array_column($stats, 'area_m2'));
        $healthyArea = ($stats['Excellent']['area_m2'] ?? 0) + ($stats['Good']['area_m2'] ?? 0);
        $stressedArea = $stats['Stressed']['area_m2'] ?? 0;
        $unhealthyArea = $stats['Poor']['area_m2'] ?? 0;

        $healthyPct = $totalArea > 0 ? round(($healthyArea / $totalArea) * 100, 1) : 0;
        $stressedPct = $totalArea > 0 ? round(($stressedArea / $totalArea) * 100, 1) : 0;
        $unhealthyPct = $totalArea > 0 ? round(($unhealthyArea / $totalArea) * 100, 1) : 0;

        $overallStatus = $healthyPct >= 60 ? 'Healthy' : ($healthyPct >= 30 ? 'Moderate' : 'Critical');

        $result = CropHealthResult::create([
            'project_id' => $project->id,
            'image_upload_id' => $imageUpload->id,
            'total_area_m2' => $totalArea,
            'healthy_area_m2' => $healthyArea,
            'stressed_area_m2' => $stressedArea,
            'unhealthy_area_m2' => $unhealthyArea,
            'healthy_percentage' => $healthyPct,
            'stressed_percentage' => $stressedPct,
            'unhealthy_percentage' => $unhealthyPct,
            'overall_status' => $overallStatus,
            'raw_stats' => $stats,
        ]);

        return response()->json($result);
    }

    private function callPythonSamSegmenter($imagePath, $clickX, $clickY, $clickType, $classId, $color)
    {
        $base = $this->projectBasePath;
        $script = <<<PY
import sys, json
sys.path.insert(0, '{$base}')
from sam__predectorr import AdvancedSAMSegmenter

with open(r'{$imagePath}', 'rb') as f:
    data = f.read()

segmenter = AdvancedSAMSegmenter()
segmenter.load_image(data)

result = segmenter.segment_with_click({$clickX}, {$clickY}, {$clickType}, {$classId}, '{$color}')

output = {
    'area_pixels': result['class_statistics']['total_pixels'],
    'area_m2': result['class_statistics']['area_m2'],
    'geo_metadata': result['class_statistics'].get('geospatial_metadata', {}),
}

geojson = segmenter.export_geojson({$classId})
if geojson:
    output['polygons'] = geojson['features']

print(json.dumps(output))
PY;
        return $this->runPython($script);
    }

    private function callPythonClassifier($imagePath)
    {
        $base = $this->projectBasePath;
        $script = <<<PY
import sys, json
sys.path.insert(0, '{$base}')
from classifier import EuroSATResNetClassifier
import numpy as np
from PIL import Image

clf = EuroSATResNetClassifier(weights_path='{$base}/checkpoint/classifier_weights.pth')

img = Image.open(r'{$imagePath}').convert('RGB')
img_array = np.array(img)
label, confidence = clf.predict_patch(img_array)
print(json.dumps({'label': label, 'confidence': confidence}))
PY;
        return $this->runPython($script);
    }

    private function callPythonGeoProcessor($imagePath)
    {
        $base = $this->projectBasePath;
        $script = <<<PY
import sys, json
sys.path.insert(0, '{$base}')
from geo_processor import process_geotiff

with open(r'{$imagePath}', 'rb') as f:
    data = f.read()

rgb, ndvi, stats = process_geotiff(data)
print(json.dumps(stats))
PY;
        return $this->runPython($script);
    }

    private function runPython($script)
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'py_') . '.py';
        file_put_contents($tmpFile, $script);

        $command = sprintf('"%s" %s 2>&1',
            escapeshellcmd($this->pythonPath),
            escapeshellarg($tmpFile)
        );

        try {
            $output = shell_exec($command);
            $result = json_decode($output, true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($result)) {
                \Log::error('Python bridge: invalid JSON output', ['output' => $output]);
                return ['error' => 'Invalid Python output'];
            }
            return $result;
        } catch (\Exception $e) {
            \Log::error('Python bridge error: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        } finally {
            if (file_exists($tmpFile)) {
                unlink($tmpFile);
            }
        }
    }
}

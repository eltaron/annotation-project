<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ImageUpload;
use App\Models\Annotation;
use App\Models\CropHealthResult;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

class PythonBridgeController extends Controller
{
    private $pythonPath;
    private $projectBasePath;

    public function __construct()
    {
        $this->pythonPath = SystemSetting::getValue('python_path', 'python');
        $this->projectBasePath = SystemSetting::getValue('python_base_path', str_replace('\\', '/', base_path('..')));
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

        Log::info('SAM segment starting', [
            'image' => $imagePath,
            'class_id' => $request->class_id,
            'click' => [$request->click_x, $request->click_y],
        ]);

        $result = $this->callPythonSamSegmenter(
            $imagePath, $request->click_x, $request->click_y,
            $request->click_type, $request->class_id, $color
        );

        if (!$result || isset($result['error'])) {
            $msg = $result['error'] ?? 'Segmentation failed';
            Log::error('SAM segment failed', ['error' => $msg]);
            return response()->json(['error' => $msg], 500);
        }

        try {
            $annotation = Annotation::create([
                'image_upload_id' => $imageUpload->id,
                'annotation_class_id' => $request->class_id,
                'user_id' => Auth::id(),
                'polygon_coordinates' => $result['pixel_coords'] ?? null,
                'area_pixels' => $result['area_pixels'] ?? 0,
                'area_m2' => $result['area_m2'] ?? 0,
                'classification_label' => $result['classification_label'] ?? null,
                'classification_confidence' => $result['classification_confidence'] ?? null,
                'geo_metadata' => $result['geo_metadata'] ?? null,
            ]);
        } catch (\Exception $e) {
            Log::error('SAM annotation create failed', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Database error: ' . $e->getMessage()], 500);
        }

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

        // Generate heatmap and save to storage
        $heatmapRelPath = "projects/{$project->id}/heatmaps/{$imageUpload->id}.png";
        $heatmapFullPath = str_replace('\\', '/', Storage::disk('public')->path($heatmapRelPath));
        $heatmapDir = dirname($heatmapFullPath);
        if (!is_dir($heatmapDir)) {
            mkdir($heatmapDir, 0755, true);
        }

        $stats = $this->callPythonGeoProcessor($imagePath, $heatmapFullPath);

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
            'heatmap_path' => $heatmapRelPath,
        ]);

        return response()->json($result);
    }

    public function showHeatmap(Project $project, ImageUpload $imageUpload)
    {
        if ($project->user_id !== Auth::id()) abort(403);

        $result = CropHealthResult::where('project_id', $project->id)
            ->where('image_upload_id', $imageUpload->id)
            ->latest()
            ->first();

        if (!$result || !$result->heatmap_path) {
            abort(404, 'Heatmap not found. Run health analysis first.');
        }

        $path = Storage::disk('public')->path($result->heatmap_path);
        if (!file_exists($path)) {
            abort(404, 'Heatmap file not found.');
        }

        return Response::file($path, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    private function callPythonSamSegmenter($imagePath, $clickX, $clickY, $clickType, $classId, $color)
    {
        $base = $this->projectBasePath;
        $samCheckpoint = SystemSetting::getValue('sam_checkpoint_path', $base . '/checkpoint/sam_vit_b_01ec64.pth');

        // Resolve relative path to absolute (DB may store a relative path)
        if (!preg_match('/^[A-Za-z]:\\\\|^\\//', $samCheckpoint)) {
            $samCheckpoint = $base . '/' . ltrim($samCheckpoint, '/\\');
        }

        Log::debug('SAM checkpoint', ['path' => $samCheckpoint, 'exists' => file_exists($samCheckpoint)]);
        Log::debug('SAM image', ['path' => $imagePath, 'exists' => file_exists($imagePath)]);

        if (!file_exists($samCheckpoint)) {
            $msg = "SAM checkpoint not found at: $samCheckpoint";
            Log::error($msg);
            return ['error' => $msg];
        }
        if (!file_exists($imagePath)) {
            $msg = "Image file not found at: $imagePath";
            Log::error($msg);
            return ['error' => $msg];
        }

        $script = <<<PY
import sys, json, warnings, traceback
sys.path.insert(0, '{$base}')

warnings.filterwarnings('ignore', category=FutureWarning)

try:
    from sam__predectorr import AdvancedSAMSegmenter
    import numpy as np
    from rasterio.features import shapes
    from rasterio import Affine

    with open(r'{$imagePath}', 'rb') as f:
        data = f.read()

    segmenter = AdvancedSAMSegmenter(checkpoint_path=r'{$samCheckpoint}')
    segmenter.load_image(data)

    result = segmenter.segment_with_click({$clickX}, {$clickY}, {$clickType}, {$classId}, '{$color}')

    output = dict(
        area_pixels=result['class_statistics']['total_pixels'],
        area_m2=result['class_statistics']['area_m2'],
        geo_metadata=result['class_statistics'].get('geospatial_metadata', {}),
    )

    # Export pixel coordinates for canvas drawing (in preview image space, max 2048px)
    mask = (segmenter.label_map == {$classId}).astype(np.uint8)
    pixel_rings = []
    for geom, val in shapes(mask, mask=mask, transform=Affine.identity()):
        if val == 1 and geom and 'coordinates' in geom:
            coords_list = geom['coordinates']
            if geom['type'] == 'MultiPolygon':
                for poly_coords in coords_list:
                    for ring in poly_coords:
                        pixel_rings.append([[round(x), round(y)] for x, y in ring])
            else:
                for ring in coords_list:
                    pixel_rings.append([[round(x), round(y)] for x, y in ring])
    output['pixel_coords'] = pixel_rings

    print(json.dumps(output))
except Exception as e:
    error_msg = traceback.format_exc()
    print(json.dumps(dict(error=str(e), trace=error_msg)))
    sys.exit(1)
PY;
        return $this->runPython($script);
    }

    private function callPythonClassifier($imagePath)
    {
        $base = $this->projectBasePath;
        $classifierWeights = SystemSetting::getValue('classifier_weights_path', 'checkpoint/classifier_weights.pth');

        // Resolve relative path to absolute
        if (!preg_match('/^[A-Za-z]:\\\\|^\\//', $classifierWeights)) {
            $classifierWeights = $base . '/' . ltrim($classifierWeights, '/\\');
        }

        $script = <<<PY
import sys, json
sys.path.insert(0, '{$base}')
from classifier import EuroSATResNetClassifier
import numpy as np
from PIL import Image

clf = EuroSATResNetClassifier(weights_path=r'{$classifierWeights}')

img = Image.open(r'{$imagePath}').convert('RGB')
img_array = np.array(img)
label, confidence = clf.predict_patch(img_array)
print(json.dumps({'label': label, 'confidence': confidence}))
PY;
        return $this->runPython($script);
    }

    private function callPythonGeoProcessor($imagePath, $heatmapOutputPath = null)
    {
        $base = $this->projectBasePath;
        $saveHeatmap = $heatmapOutputPath ? "with open(r'{$heatmapOutputPath}', 'wb') as f: f.write(ndvi)" : '';
        $script = <<<PY
import sys, json
sys.path.insert(0, '{$base}')
from geo_processor import process_geotiff

with open(r'{$imagePath}', 'rb') as f:
    data = f.read()

rgb, ndvi, stats = process_geotiff(data)
{$saveHeatmap}
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

        Log::debug('Python command', ['cmd' => $command]);

        try {
            $output = shell_exec($command);

            if ($output === null) {
                $msg = 'shell_exec returned null (command may have failed or timed out)';
                Log::error('Python bridge: ' . $msg, ['command' => $command]);
                return ['error' => $msg];
            }

            $output = trim($output);
            Log::debug('Python raw output', ['output' => substr($output, 0, 1000)]);

            $jsonStart = strpos($output, '{');
            if ($jsonStart === false) {
                Log::error('Python bridge: no JSON found in output', ['output' => $output]);
                return ['error' => substr($output, 0, 500)];
            }

            $jsonPart = substr($output, $jsonStart);
            $jsonEnd = strrpos($jsonPart, '}');
            $jsonPart = $jsonEnd !== false ? substr($jsonPart, 0, $jsonEnd + 1) : $jsonPart;

            $result = json_decode($jsonPart, true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($result)) {
                Log::error('Python bridge: JSON decode failed', [
                    'json_error' => json_last_error_msg(),
                    'json_part' => substr($jsonPart, 0, 500),
                ]);
                return ['error' => 'Python error: ' . substr($output, 0, 500)];
            }

            if (isset($result['error'])) {
                Log::error('Python bridge: script returned error', $result);
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('Python bridge exception: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        } finally {
            if (file_exists($tmpFile)) {
                unlink($tmpFile);
            }
        }
    }
}

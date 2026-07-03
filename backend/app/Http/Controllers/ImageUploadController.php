<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ImageUpload;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImageUploadController extends Controller
{
    public function upload(Request $request, Project $project)
    {
        if ($project->user_id !== Auth::id()) {
            abort(403);
        }

        $file = $request->file('image');
        if (!$file || !$file->isValid()) {
            return back()->with('error', 'No file received or upload failed.');
        }

        $ext = strtolower($file->getClientOriginalExtension());
        if (!in_array($ext, ['tif', 'tiff'])) {
            return back()->with('error', 'Only .tif/.tiff files are allowed.');
        }

        $maxBytes = (int) SystemSetting::getValue('max_upload_size_mb', 500) * 1048576;
        if ($file->getSize() > $maxBytes) {
            return back()->with('error', 'File exceeds the configured size limit.');
        }

        $path = $file->store("projects/{$project->id}/images", 'public');

        $upload = ImageUpload::create([
            'project_id' => $project->id,
            'user_id' => Auth::id(),
            'original_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_size' => $file->getSize(),
        ]);

        return redirect()->route('projects.annotate', [$project, $upload])
            ->with('success', 'Image uploaded successfully!');
    }

    public function annotate(Project $project, ImageUpload $imageUpload, Request $request)
    {
        if ($project->user_id !== Auth::id()) {
            abort(403);
        }

        $imageUpload->load('annotations.annotationClass');
        $classes = $project->annotationClasses;
        $selectedClassId = $request->query('class_id');

        return view('annotate.workspace', compact('project', 'imageUpload', 'classes', 'selectedClassId'));
    }

    private function extractMetadata($path)
    {
        $fullPath = str_replace('\\', '/', Storage::disk('public')->path($path));
        $metadata = ['width' => null, 'height' => null, 'bands' => null, 'crs' => null];

        $script = <<<PY
import sys, json
import rasterio
try:
    with rasterio.open(r'{$fullPath}') as src:
        print(f"{src.width} {src.height} {src.count} {src.crs}")
except Exception as e:
    print(f"0 0 0 None")
PY;

        $tmpFile = tempnam(sys_get_temp_dir(), 'py_') . '.py';
        file_put_contents($tmpFile, $script);

        try {
            $command = sprintf('python %s 2>&1', escapeshellarg($tmpFile));
            $output = shell_exec($command);

            if ($output) {
                $parts = explode(' ', trim($output));
                if (count($parts) >= 4) {
                    $metadata['width'] = (int)$parts[0];
                    $metadata['height'] = (int)$parts[1];
                    $metadata['bands'] = (int)$parts[2];
                    $crs = trim($parts[3]);
                    $metadata['crs'] = ($crs !== 'None') ? $crs : null;
                }
            }
        } catch (\Exception $e) {
            \Log::warning('Metadata extraction failed', ['error' => $e->getMessage()]);
        } finally {
            if (file_exists($tmpFile)) unlink($tmpFile);
        }

        return $metadata;
    }
}

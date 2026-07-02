<?php

namespace App\Http\Controllers;

use App\Models\ImageUpload;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ImagePreviewController extends Controller
{
    public function preview(ImageUpload $imageUpload)
    {
        if ($imageUpload->project->user_id !== Auth::id()) {
            abort(403);
        }

        $previewDir = 'previews';
        $previewFilename = $imageUpload->id . '.png';
        $previewPath = $previewDir . '/' . $previewFilename;

        // Return cached preview if exists
        if (Storage::disk('public')->exists($previewPath)) {
            return response()->file(Storage::disk('public')->path($previewPath), [
                'Content-Type' => 'image/png',
                'Cache-Control' => 'public, max-age=86400',
            ]);
        }

        // Generate preview via Python
        $tifPath = Storage::disk('public')->path($imageUpload->file_path);
        $pngPath = Storage::disk('public')->path($previewPath);

        // Ensure preview directory exists
        Storage::disk('public')->makeDirectory($previewDir);

        $pythonPath = SystemSetting::getValue('python_path', 'python');
        $basePath = SystemSetting::getValue('python_base_path', str_replace('\\', '/', base_path('..')));

        $command = sprintf(
            '"%s" "%s/preview_generator.py" "%s" "%s" 2>&1',
            escapeshellcmd($pythonPath),
            $basePath,
            str_replace('\\', '/', $tifPath),
            str_replace('\\', '/', $pngPath)
        );

        $output = shell_exec($command);

        if (!file_exists($pngPath)) {
            abort(500, 'Failed to generate image preview: ' . ($output ?? 'unknown error'));
        }

        return response()->file($pngPath, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}

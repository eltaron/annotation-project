<?php

namespace App\Http\Controllers;

use App\Models\ImageUpload;
use App\Models\Project;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImagePreviewController extends Controller
{
    public function preview(Project $project, ImageUpload $imageUpload)
    {
        if ($imageUpload->project->user_id !== Auth::id()) {
            abort(403);
        }

        $previewDir = 'previews';
        $previewFilename = $imageUpload->id . '.png';
        $previewPath = $previewDir . '/' . $previewFilename;
        $previewFullPath = Storage::disk('public')->path($previewPath);

        // Return cached preview if exists
        if (Storage::disk('public')->exists($previewPath)) {
            return $this->pngResponse($previewFullPath);
        }

        Storage::disk('public')->makeDirectory($previewDir);

        // Try generating preview via Python
        $tifPath = Storage::disk('public')->path($imageUpload->file_path);
        $pythonPath = SystemSetting::getValue('python_path', 'python');
        $basePath = SystemSetting::getValue('python_base_path', str_replace('\\', '/', base_path('..')));

        $command = sprintf(
            '"%s" "%s/preview_generator.py" "%s" "%s" 2>&1',
            escapeshellcmd($pythonPath),
            $basePath,
            str_replace('\\', '/', $tifPath),
            str_replace('\\', '/', $previewFullPath)
        );

        shell_exec($command);

        if (file_exists($previewFullPath)) {
            return $this->pngResponse($previewFullPath);
        }

        // Fallback: generate a placeholder with the file info using GD
        $img = $this->generatePlaceholder($imageUpload);
        imagepng($img, $previewFullPath);
        imagedestroy($img);

        return $this->pngResponse($previewFullPath);
    }

    private function pngResponse($path)
    {
        return response()->file($path, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    private function generatePlaceholder($imageUpload)
    {
        $w = 800;
        $h = 600;
        $img = imagecreatetruecolor($w, $h);

        $bg = imagecolorallocate($img, 15, 23, 42);
        imagefill($img, 0, 0, $bg);

        $grid = imagecolorallocate($img, 30, 41, 59);
        for ($x = 0; $x < $w; $x += 40) imageline($img, $x, 0, $x, $h, $grid);
        for ($y = 0; $y < $h; $y += 40) imageline($img, 0, $y, $w, $y, $grid);

        $clr = imagecolorallocate($img, 100, 116, 139);
        $cx = $w / 2;
        $cy = $h / 2 - 30;
        imagefilledrectangle($img, $cx - 40, $cy - 15, $cx + 40, $cy + 25, $clr);
        imagefilledrectangle($img, $cx - 25, $cy - 25, $cx + 25, $cy - 10, $clr);
        imagefilledellipse($img, $cx, $cy + 5, 30, 30, $bg);
        imagefilledellipse($img, $cx, $cy + 5, 20, 20, $clr);

        $textClr = imagecolorallocate($img, 148, 163, 184);
        $smallClr = imagecolorallocate($img, 100, 116, 139);
        $name = $imageUpload->original_name;
        $size = number_format($imageUpload->file_size / 1048576, 2) . ' MB';

        // Center text manually
        $textY = $cy + 60;
        foreach ([
            [5, $name, $textClr],
            [3, $size, $smallClr],
            [3, 'Preview not available', $smallClr],
        ] as $i => $line) {
            $fw = imagefontwidth($line[0]) * strlen($line[1]);
            $fh = imagefontheight($line[0]);
            $x = (int)(($w - $fw) / 2);
            $y = (int)($textY + $i * ($fh + 8));
            imagestring($img, $line[0], $x, $y, $line[1], $line[2]);
        }

        return $img;
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ImageUpload;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ChunkedUploadController extends Controller
{
    public function uploadChunk(Request $request, Project $project)
    {
        if ($project->user_id !== Auth::id()) {
            abort(403);
        }

        $ext = strtolower(pathinfo($request->input('original_name', ''), PATHINFO_EXTENSION));
        if (!in_array($ext, ['tif', 'tiff'])) {
            return response()->json(['error' => 'Only .tif/.tiff files are allowed.'], 422);
        }

        $maxBytes = (int) SystemSetting::getValue('max_upload_size_mb', 500) * 1048576;

        $chunk = (int) $request->input('chunk', 0);
        $chunks = (int) $request->input('chunks', 1);
        $uploadId = $request->input('upload_id');
        $originalName = $request->input('original_name');

        if (!$uploadId || !$originalName) {
            return response()->json(['error' => 'Missing upload parameters.'], 422);
        }

        $file = $request->file('file');
        if (!$file || !$file->isValid()) {
            Log::error('CHUNK_UPLOAD: invalid chunk', ['chunk' => $chunk, 'upload_id' => $uploadId]);
            return response()->json(['error' => 'Chunk file invalid.'], 400);
        }

        $chunkDir = storage_path("app/chunks/{$project->id}/{$uploadId}");
        if (!is_dir($chunkDir)) {
            mkdir($chunkDir, 0755, true);
        }

        $file->move($chunkDir, "chunk_{$chunk}");

        $isLast = ($chunk === $chunks - 1);

        if (!$isLast) {
            $progress = (int) round(($chunk + 1) / $chunks * 100);
            return response()->json(['done' => false, 'progress' => $progress]);
        }

        // Last chunk — assemble the final file
        $finalName = uniqid('img_', true) . '.' . $ext;
        $finalPath = "projects/{$project->id}/images/{$finalName}";
        $finalFullPath = storage_path("app/public/{$finalPath}");

        $finalDir = dirname($finalFullPath);
        if (!is_dir($finalDir)) {
            mkdir($finalDir, 0755, true);
        }

        $out = fopen($finalFullPath, 'wb');
        $totalSize = 0;
        for ($i = 0; $i < $chunks; $i++) {
            $chunkFile = "{$chunkDir}/chunk_{$i}";
            if (!file_exists($chunkFile)) {
                Log::error('CHUNK_UPLOAD: missing chunk', ['index' => $i, 'upload_id' => $uploadId]);
                continue;
            }
            $in = fopen($chunkFile, 'rb');
            stream_copy_to_stream($in, $out);
            fclose($in);
            $totalSize += filesize($chunkFile);
            unlink($chunkFile);
        }
        fclose($out);
        rmdir($chunkDir);

        if ($totalSize > $maxBytes) {
            unlink($finalFullPath);
            $maxMb = number_format($maxBytes / 1048576, 0);
            return response()->json(['error' => "File exceeds the {$maxMb}MB limit."], 422);
        }

        $upload = ImageUpload::create([
            'project_id' => $project->id,
            'user_id' => Auth::id(),
            'original_name' => $originalName,
            'file_path' => $finalPath,
            'file_size' => $totalSize,
        ]);

        Log::info('CHUNK_UPLOAD: complete', [
            'chunks' => $chunks,
            'size_mb' => round($totalSize / 1048576, 2),
            'db_id' => $upload->id,
        ]);

        return response()->json([
            'done' => true,
            'redirect' => route('projects.annotate', [$project, $upload]),
        ]);
    }
}

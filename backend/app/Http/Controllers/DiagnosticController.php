<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Http\Request;

class DiagnosticController extends Controller
{
    public function index()
    {
        $pythonPath = SystemSetting::getValue('python_path', 'python');
        $basePath = SystemSetting::getValue('python_base_path', str_replace('\\', '/', base_path('..')));
        $samCheckpoint = SystemSetting::getValue('sam_checkpoint_path', 'checkpoint/sam_vit_b_01ec64.pth');
        $classifierWeights = SystemSetting::getValue('classifier_weights_path', 'checkpoint/classifier_weights.pth');

        $results = [
            'python' => $this->checkPython($pythonPath),
            'imports' => $this->checkImports($pythonPath),
            'sam_checkpoint' => $this->checkFile($basePath, $samCheckpoint),
            'classifier_weights' => $this->checkFile($basePath, $classifierWeights),
            'scripts_dir' => is_dir($basePath),
            'scripts_dir_path' => $basePath,
        ];

        return view('settings.diagnostic', compact('results', 'pythonPath', 'basePath'));
    }

    private function checkPython($pythonPath)
    {
        $version = trim(shell_exec(
            sprintf('"%s" --version 2>&1', escapeshellcmd($pythonPath))
        ) ?? '');
        $available = str_contains($version, 'Python') && !str_contains($version, 'not found');
        return [
            'available' => $available,
            'version' => $version ?: 'Not found',
        ];
    }

    private function checkImports($pythonPath)
    {
        $script = <<<PY
import sys, json
pkgs = {"torch": False, "rasterio": False, "numpy": False, "PIL": False, "segment_anything": False}
for p in pkgs:
    try:
        __import__(p)
        pkgs[p] = True
    except:
        pass
print(json.dumps(pkgs))
PY;

        $tmpFile = tempnam(sys_get_temp_dir(), 'diag_') . '.py';
        file_put_contents($tmpFile, $script);

        $cmd = sprintf(
            '"%s" %s 2>&1',
            escapeshellcmd($pythonPath),
            escapeshellarg($tmpFile)
        );

        $output = trim(shell_exec($cmd) ?? '');
        if (file_exists($tmpFile)) unlink($tmpFile);

        $decoded = json_decode($output, true);

        if (!is_array($decoded)) {
            return array_fill_keys(['torch', 'rasterio', 'numpy', 'PIL', 'segment_anything'], false);
        }

        return $decoded;
    }

    private function checkFile($basePath, $relativePath)
    {
        $full = $basePath . '/' . ltrim($relativePath, '/');
        return [
            'exists' => file_exists($full),
            'path' => $full,
        ];
    }
}

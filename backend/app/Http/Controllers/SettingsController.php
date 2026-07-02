<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = SystemSetting::all()->keyBy('key');
        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $keys = [
            'python_path', 'python_base_path', 'sam_checkpoint_path',
            'classifier_weights_path', 'max_upload_size_mb',
        ];

        foreach ($keys as $key) {
            if ($request->has($key)) {
                SystemSetting::setValue($key, $request->input($key));
            }
        }

        SystemSetting::clearCache();

        return redirect()->route('settings.index')->with('success', 'Settings saved successfully.');
    }
}

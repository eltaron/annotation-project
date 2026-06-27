<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Annotation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnnotationController extends Controller
{
    public function index(Project $project)
    {
        if ($project->user_id !== Auth::id()) {
            abort(403);
        }
        return response()->json($project->annotations()->with('annotationClass')->get());
    }

    public function store(Request $request, Project $project)
    {
        if ($project->user_id !== Auth::id()) {
            abort(403);
        }

        $rules = [
            'image_upload_id' => 'required|exists:image_uploads,id',
            'annotation_class_id' => 'required|exists:annotation_classes,id',
            'area_pixels' => 'nullable|numeric',
            'area_m2' => 'nullable|numeric',
            'classification_label' => 'nullable|string',
            'classification_confidence' => 'nullable|numeric',
        ];

        $validated = $request->validate($rules);
        $validated['user_id'] = Auth::id();

        // Handle JSON fields: accept both JSON strings and arrays
        foreach (['mask_data', 'polygon_coordinates', 'bbox', 'geo_metadata'] as $field) {
            if ($request->has($field)) {
                $value = $request->input($field);
                if (is_string($value)) {
                    $decoded = json_decode($value, true);
                    $validated[$field] = json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
                } else {
                    $validated[$field] = $value;
                }
            }
        }

        $annotation = Annotation::create($validated);

        if ($request->wantsJson()) {
            return response()->json($annotation->load('annotationClass'), 201);
        }

        return back()->with('success', 'Annotation saved!');
    }
}

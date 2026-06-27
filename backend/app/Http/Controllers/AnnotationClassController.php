<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\AnnotationClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnnotationClassController extends Controller
{
    public function store(Request $request, Project $project)
    {
        if ($project->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|max:9',
        ]);

        $exists = $project->annotationClasses()
            ->where('name', $validated['name'])
            ->exists();

        if ($exists) {
            return back()->with('error', 'Class already exists!');
        }

        $project->annotationClasses()->create($validated);

        return back()->with('success', "Class '{$validated['name']}' added!");
    }

    public function destroy(Project $project, AnnotationClass $annotationClass)
    {
        if ($project->user_id !== Auth::id()) {
            abort(403);
        }

        $annotationClass->delete();

        return back()->with('success', 'Class deleted.');
    }
}

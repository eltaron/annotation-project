<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\AnnotationClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Auth::user()->projects()->withCount('imageUploads', 'annotationClasses')->get();
        return view('projects.index', compact('projects'));
    }

    public function create()
    {
        return view('projects.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $project = Auth::user()->projects()->create($validated);

        $defaultClasses = [
            ['name' => 'Annual Crop',           'color' => '#e6ab02'],
            ['name' => 'Forest',                'color' => '#1b9e77'],
            ['name' => 'Herbaceous Vegetation', 'color' => '#66a61e'],
            ['name' => 'Highway',               'color' => '#d95f02'],
            ['name' => 'Industrial',            'color' => '#7570b3'],
            ['name' => 'Pasture',               'color' => '#a6761d'],
            ['name' => 'Permanent Crop',        'color' => '#e7298a'],
            ['name' => 'Residential',           'color' => '#1f78b4'],
            ['name' => 'River',                 'color' => '#a6cee3'],
            ['name' => 'Sea Lake',              'color' => '#b2df8a'],
        ];

        foreach ($defaultClasses as $class) {
            AnnotationClass::create([
                'project_id' => $project->id,
                'name'       => $class['name'],
                'color'      => $class['color'],
            ]);
        }

        return redirect()->route('projects.show', $project)
            ->with('success', 'Project created successfully!');
    }

    public function show(Project $project)
    {
        $this->authorizeAccess($project);
        $project->load('imageUploads', 'annotationClasses');
        return view('projects.show', compact('project'));
    }

    public function edit(Project $project)
    {
        $this->authorizeAccess($project);
        return view('projects.edit', compact('project'));
    }

    public function update(Request $request, Project $project)
    {
        $this->authorizeAccess($project);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $project->update($validated);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Project updated!');
    }

    public function destroy(Project $project)
    {
        $this->authorizeAccess($project);
        $project->delete();

        return redirect()->route('projects.index')
            ->with('success', 'Project deleted.');
    }

    private function authorizeAccess(Project $project)
    {
        if ($project->user_id !== Auth::id()) {
            abort(403);
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Project;
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

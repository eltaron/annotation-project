<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\CropHealthResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $projectsCount = $user->projects()->count();
        $recentProjects = $user->projects()->withCount('imageUploads', 'annotations')
            ->latest()->take(5)->get();

        $totalAnnotations = $user->projects()->withCount('annotations')
            ->get()->sum('annotations_count');

        $latestHealthResults = CropHealthResult::whereIn('project_id', $user->projects()->pluck('id'))
            ->latest()->take(3)->get();

        return view('dashboard', compact(
            'projectsCount',
            'recentProjects',
            'totalAnnotations',
            'latestHealthResults'
        ));
    }

    public function healthReport(Project $project)
    {
        if ($project->user_id !== Auth::id()) {
            abort(403);
        }

        $healthResult = $project->cropHealthResults()->latest()->first();

        return view('projects.health-report', compact('project', 'healthResult'));
    }
}

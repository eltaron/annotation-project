<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Annotation;
use App\Models\ImageUpload;
use App\Models\CropHealthResult;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AssistantController extends Controller
{
    public function index()
    {
        return view('assistant.index');
    }

    public function ask(Request $request)
    {
        $request->validate(['message' => 'required|string|max:500']);

        $question = trim($request->message);
        $user = Auth::user();

        try {
            $answer = $this->answer($question, $user);
            return response()->json(['success' => true, 'answer' => $answer]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'answer' => 'عذراً، حدث خطأ أثناء معالجة السؤال.'], 500);
        }
    }

    private function answer($q, $user)
    {
        $q = mb_strtolower($q, 'UTF-8');

        // ——— Count queries ———
        if (preg_match('/(count|how many|number of|projects?)/i', $q)) {
            $count = $user->projects()->count();
            return "You have **{$count}** project" . ($count != 1 ? 's' : '') . ".";
        }

        if (preg_match('/(count|how many|number of)\s*(images?|files?)/i', $q)) {
            $count = ImageUpload::whereIn('project_id', $user->projects()->pluck('id'))->count();
            return "Total uploaded images: **{$count}**.";
        }

        if (preg_match('/(count|how many|number of)\s*(annotations?|segments?)/i', $q)) {
            $count = Annotation::whereIn('image_upload_id', function ($q) use ($user) {
                $q->select('id')->from('image_uploads')->whereIn('project_id', $user->projects()->pluck('id'));
            })->count();
            return "Total annotations (segments): **{$count}**.";
        }

        if (preg_match('/(count|how many|number of)\s*(users?)/i', $q)) {
            $count = User::count();
            return "Registered users: **{$count}**.";
        }

        if (preg_match('/(count|how many|number of)\s*(classes?|labels?)/i', $q)) {
            $count = \App\Models\AnnotationClass::whereIn('project_id', $user->projects()->pluck('id'))->count();
            return "Total annotation classes: **{$count}**.";
        }

        // ——— Area queries ———
        if (preg_match('/(total|sum|all)\s*(area)/i', $q)) {
            $total = Annotation::whereIn('image_upload_id', function ($q) use ($user) {
                $q->select('id')->from('image_uploads')->whereIn('project_id', $user->projects()->pluck('id'));
            })->sum('area_m2');
            $totalFormatted = number_format($total, 2);
            return "Total classified area: **{$totalFormatted}** m².";
        }

        // ——— Health queries ———
        if (preg_match('/(health|crop|vegetation|plant)/i', $q)) {
            $result = CropHealthResult::whereIn('project_id', $user->projects()->pluck('id'))
                ->latest()->first();
            if ($result) {
                return "Latest crop health report:\n"
                    . "- 🟢 Healthy: **{$result->healthy_percentage}%**\n"
                    . "- 🟡 Stressed: **{$result->stressed_percentage}%**\n"
                    . "- 🔴 Unhealthy: **{$result->unhealthy_percentage}%**\n"
                    . "- Overall status: **{$result->overall_status}**";
            }
            return "No health reports yet. Analyze an image first.";
        }

        // ——— Largest / biggest project ———
        if (preg_match('/(biggest|largest|most)\s*(project)/i', $q)) {
            $project = $user->projects()->withCount('imageUploads')->orderByDesc('image_uploads_count')->first();
            if ($project) {
                return "Your biggest project: **{$project->name}** — with **{$project->image_uploads_count}** image" . ($project->image_uploads_count != 1 ? 's' : '') . ".";
            }
            return 'No projects yet.';
        }

        // ——— Latest project ———
        if (preg_match('/(latest|recent|newest)\s*(project)/i', $q)) {
            $project = $user->projects()->latest()->first();
            if ($project) {
                $date = $project->created_at->format('Y-m-d');
                return "Latest project: **{$project->name}** — created on **{$date}**.";
            }
            return 'No projects yet.';
        }

        // ——— Summary / statistics ———
        if (preg_match('/(summary|statistics|stats|report|full)/i', $q)) {
            $projectsCount = $user->projects()->count();
            $imagesCount = ImageUpload::whereIn('project_id', $user->projects()->pluck('id'))->count();
            $annotationsCount = Annotation::whereIn('image_upload_id', function ($q) use ($user) {
                $q->select('id')->from('image_uploads')->whereIn('project_id', $user->projects()->pluck('id'));
            })->count();
            $healthCount = CropHealthResult::whereIn('project_id', $user->projects()->pluck('id'))->count();
            $totalArea = Annotation::whereIn('image_upload_id', function ($q) use ($user) {
                $q->select('id')->from('image_uploads')->whereIn('project_id', $user->projects()->pluck('id'));
            })->sum('area_m2');

            return "**Full Statistics:**\n"
                . "📁 Projects: **{$projectsCount}**\n"
                . "🖼️ Images: **{$imagesCount}**\n"
                . "🏷️ Annotations: **{$annotationsCount}**\n"
                . "📐 Classified area: **" . number_format($totalArea, 2) . "** m²\n"
                . "🌱 Health reports: **{$healthCount}**";
        }

        // ——— Specific project stats ———
        if (preg_match('/(project)\s*["\']?([^"\']+)["\']?/i', $q, $m)) {
            $name = trim($m[2]);
            $project = $user->projects()->where('name', 'LIKE', "%{$name}%")->first();
            if ($project) {
                $images = $project->imageUploads()->count();
                $annotations = Annotation::whereIn('image_upload_id', $project->imageUploads()->pluck('id'))->count();
                $area = Annotation::whereIn('image_upload_id', $project->imageUploads()->pluck('id'))->sum('area_m2');
                return "Project **{$project->name}**:\n"
                    . "- Images: **{$images}**\n"
                    . "- Annotations: **{$annotations}**\n"
                    . "- Area: **" . number_format($area, 2) . "** m²\n"
                    . "- Description: {$project->description}";
            }
        }

        // ——— Fallback ———
        $examples = "Examples:\n"
            . "- How many projects do I have?\n"
            . "- How many images?\n"
            . "- Count my annotations\n"
            . "- Total area\n"
            . "- Full statistics\n"
            . "- Health reports\n"
            . "- Biggest project";

        return "Sorry, I didn't understand the question.\nTry asking:\n{$examples}";
    }
}

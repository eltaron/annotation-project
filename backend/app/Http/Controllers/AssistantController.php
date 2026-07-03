<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Annotation;
use App\Models\ImageUpload;
use App\Models\AnnotationClass;
use App\Models\CropHealthResult;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
        $start = microtime(true);

        try {
            $answer = $this->answer($question, $user);
            Log::info('Assistant answered', ['question' => $question, 'time_ms' => round((microtime(true)-$start)*1000)]);
            return response()->json(['success' => true, 'answer' => $answer]);
        } catch (\Exception $e) {
            Log::error('Assistant error', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'answer' => 'Sorry, an error occurred while processing your question.'], 500);
        }
    }

    private function answer($q, $user)
    {
        $q = mb_strtolower(trim($q), 'UTF-8');

        // ===== HELP =====
        if (preg_match('/^(help|what can you do|commands|hint|مساعدة|ايش)|^(hi|hello|hey|مرحبا|السلام)/i', $q)) {
            return "I can answer questions about your Geo Annotate data.\n\n"
                . "**Try asking:**\n"
                . "- How many projects do I have?\n"
                . "- Full statistics\n"
                . "- Show my health reports\n"
                . "- What is my latest project?\n"
                . "- Show project \"test\"\n"
                . "- Total classified area\n"
                . "- List all my projects\n"
                . "- How many annotations do I have?";
        }

        // ===== LIST PROJECTS =====
        if (preg_match('/(list|show|all|view)\s*(projects?|المشاريع)/i', $q)
            || preg_match('/(المشاريع|عرض|كل)/i', $q)
            || $q === 'projects') {
            $projects = $user->projects()->latest()->get();
            if ($projects->isEmpty()) return 'You have **no projects** yet. Create one from the dashboard.';
            $lines = $projects->map(fn($p) => "- **{$p->name}** ({$p->imageUploads()->count()} images, created {$p->created_at->format('Y-m-d')})");
            return "**Your Projects ({$projects->count()}):**\n" . $lines->implode("\n");
        }

        // ===== PROJECT COUNT =====
        if (preg_match('/(count|how many|number of|عدد|كم)\s*(projects?|مشروع)/i', $q)
            || preg_match('/(projects?|مشاريع)\s*(count|number|عدد)/i', $q)) {
            $count = $user->projects()->count();
            return "You have **{$count}** project" . ($count != 1 ? 's' : '') . ".";
        }

        // ===== IMAGE COUNT =====
        if (preg_match('/(count|how many|number of|عدد|كم)\s*(images?|files?|صور|ملفات)/i', $q)
            || preg_match('/(images?|صور)\s*(count|number|عدد)/i', $q)) {
            $count = ImageUpload::whereIn('project_id', $user->projects()->pluck('id'))->count();
            return "Total uploaded images: **{$count}**.";
        }

        // ===== ANNOTATION COUNT =====
        if (preg_match('/(count|how many|number of|عدد|كم)\s*(annotations?|segments?|تعليقات|تصنيفات)/i', $q)
            || preg_match('/(annotations?|تصنيفات)\s*(count|number|عدد)/i', $q)) {
            $ids = ImageUpload::whereIn('project_id', $user->projects()->pluck('id'))->pluck('id');
            $count = Annotation::whereIn('image_upload_id', $ids)->count();
            return "Total annotations: **{$count}**.";
        }

        // ===== USER COUNT =====
        if (preg_match('/(count|how many|number of|عدد|كم)\s*(users?|members?|مستخدمين|اعضاء)/i', $q)) {
            $count = User::count();
            return "Registered users: **{$count}**.";
        }

        // ===== CLASS COUNT =====
        if (preg_match('/(count|how many|number of|عدد|كم)\s*(classes?|labels?|categories?|تصنيفات|فئات)/i', $q)) {
            $count = AnnotationClass::whereIn('project_id', $user->projects()->pluck('id'))->count();
            return "Total annotation classes: **{$count}**.";
        }

        // ===== TOTAL AREA =====
        if (preg_match('/(total|sum|all|كل|مجموع)\s*(area|مساحة)/i', $q)
            || preg_match('/(area|مساحة)\s*(total|sum|all|كل|مجموع)/i', $q)) {
            $ids = ImageUpload::whereIn('project_id', $user->projects()->pluck('id'))->pluck('id');
            $total = Annotation::whereIn('image_upload_id', $ids)->sum('area_m2');
            return "Total classified area: **" . number_format($total, 2) . "** m² (" . number_format($total / 10000, 2) . " hectares).";
        }

        // ===== HEALTH REPORTS =====
        if (preg_match('/(health|health report|crop|vegetation|plant|صحة|نبات|محصول)/i', $q)) {
            $result = CropHealthResult::whereIn('project_id', $user->projects()->pluck('id'))
                ->latest()->first();
            if ($result) {
                $icon = $result->overall_status === 'Healthy' ? '🟢' : ($result->overall_status === 'Moderate' ? '🟡' : '🔴');
                return "**Latest Crop Health Report:**\n"
                    . "- 🟢 Healthy: **{$result->healthy_percentage}%**\n"
                    . "- 🟡 Stressed: **{$result->stressed_percentage}%**\n"
                    . "- 🔴 Unhealthy: **{$result->unhealthy_percentage}%**\n"
                    . "- {$icon} Overall: **{$result->overall_status}**\n"
                    . "- Total area: **" . number_format($result->total_area_m2, 2) . "** m²\n"
                    . "- Image: **{$result->imageUpload?->original_name}**";
            }
            return "No health reports yet. Go to an annotation workspace and run **Analyze Health** first.";
        }

        // ===== LATEST PROJECT =====
        if (preg_match('/(latest|recent|newest|last|اخر|جديد)\s*(project|مشروع)/i', $q)) {
            $project = $user->projects()->latest()->first();
            if ($project) {
                $imgs = $project->imageUploads()->count();
                $anns = Annotation::whereIn('image_upload_id', $project->imageUploads()->pluck('id'))->count();
                return "**Latest Project:**\n"
                    . "- Name: **{$project->name}**\n"
                    . "- Created: **{$project->created_at->format('Y-m-d')}**\n"
                    . "- Images: **{$imgs}**\n"
                    . "- Annotations: **{$anns}**\n"
                    . "- Description: {$project->description}";
            }
            return 'No projects yet.';
        }

        // ===== BIGGEST PROJECT =====
        if (preg_match('/(biggest|largest|most|اكبر|كبير)\s*(project|مشروع)/i', $q)) {
            $project = $user->projects()->withCount('imageUploads')->orderByDesc('image_uploads_count')->first();
            if ($project) {
                return "Your biggest project: **{$project->name}** — with **{$project->image_uploads_count}** image" . ($project->image_uploads_count != 1 ? 's' : '') . ".";
            }
            return 'No projects yet.';
        }

        // ===== SPECIFIC PROJECT =====
        if (preg_match('/project\s*["\'"]?([^"\']+)["\']?/i', $q, $m)
            || preg_match('/مشروع\s*["\'"]?([^"\']+)["\']?/i', $q, $m)) {
            $name = trim($m[1]);
            $project = $user->projects()->where('name', 'LIKE', "%{$name}%")->first();
            if ($project) {
                $images = $project->imageUploads()->count();
                $annotations = Annotation::whereIn('image_upload_id', $project->imageUploads()->pluck('id'))->count();
                $area = Annotation::whereIn('image_upload_id', $project->imageUploads()->pluck('id'))->sum('area_m2');
                $health = CropHealthResult::where('project_id', $project->id)->latest()->first();
                $lines = ["**Project: {$project->name}**"];
                $lines[] = "- Images: **{$images}**";
                $lines[] = "- Annotations: **{$annotations}**";
                $lines[] = "- Area: **" . number_format($area, 2) . "** m²";
                if ($health) $lines[] = "- Health: **{$health->overall_status}** ({$health->healthy_percentage}% healthy)";
                if ($project->description) $lines[] = "- Desc: {$project->description}";
                return implode("\n", $lines);
            }
        }

        // ===== ANNOTATIONS FOR A PROJECT =====
        if (preg_match('/(annotations?|classifications?|segments?)\s*(in|for|of)\s*(project)?\s*["\']?([^"\']+)["\']?/i', $q, $m)) {
            $name = trim(end($m));
            $project = $user->projects()->where('name', 'LIKE', "%{$name}%")->first();
            if ($project) {
                $ids = $project->imageUploads()->pluck('id');
                $count = Annotation::whereIn('image_upload_id', $ids)->count();
                $classes = Annotation::whereIn('image_upload_id', $ids)
                    ->join('annotation_classes', 'annotations.annotation_class_id', '=', 'annotation_classes.id')
                    ->selectRaw('annotation_classes.name, count(*) as cnt')
                    ->groupBy('annotation_classes.name')
                    ->orderByDesc('cnt')
                    ->get();
                $lines = ["**Annotations in {$project->name}:** **{$count}** total"];
                foreach ($classes as $c) {
                    $lines[] = "- {$c->name}: **{$c->cnt}**";
                }
                return implode("\n", $lines);
            }
        }

        // ===== FULL STATISTICS =====
        if (preg_match('/(summary|statistics|stats|report|full|احصائيات|تقرير|كامل)/i', $q)) {
            $projectIds = $user->projects()->pluck('id');
            $imageIds = ImageUpload::whereIn('project_id', $projectIds)->pluck('id');
            $projectsCount = $user->projects()->count();
            $imagesCount = $imageIds->count();
            $annotationsCount = Annotation::whereIn('image_upload_id', $imageIds)->count();
            $healthCount = CropHealthResult::whereIn('project_id', $projectIds)->count();
            $totalArea = Annotation::whereIn('image_upload_id', $imageIds)->sum('area_m2');
            $classCount = AnnotationClass::whereIn('project_id', $projectIds)->count();
            $topClass = Annotation::whereIn('image_upload_id', $imageIds)
                ->join('annotation_classes', 'annotations.annotation_class_id', '=', 'annotation_classes.id')
                ->selectRaw('annotation_classes.name, count(*) as cnt')
                ->groupBy('annotation_classes.name')
                ->orderByDesc('cnt')
                ->first();

            return "**Full Statistics:**\n"
                . "📁 Projects: **{$projectsCount}**\n"
                . "🖼️ Images: **{$imagesCount}**\n"
                . "🏷️ Annotations: **{$annotationsCount}**\n"
                . "🏷️ Classes: **{$classCount}**\n"
                . "📐 Area: **" . number_format($totalArea, 2) . "** m²\n"
                . "🌱 Health reports: **{$healthCount}**\n"
                . ($topClass ? "🔥 Most used class: **{$topClass->name}** ({$topClass->cnt})" : "");
        }

        // ===== RECENT ANNOTATIONS =====
        if (preg_match('/(recent|latest|last|اخر|جديد)\s*(annotations?|classifications?|تعليقات)/i', $q)) {
            $ids = ImageUpload::whereIn('project_id', $user->projects()->pluck('id'))->pluck('id');
            $anns = Annotation::whereIn('image_upload_id', $ids)->with('annotationClass', 'imageUpload')
                ->latest()->take(5)->get();
            if ($anns->isEmpty()) return 'No annotations yet.';
            $lines = $anns->map(fn($a) => "- {$a->annotationClass?->name} on {$a->imageUpload?->original_name} ({$a->area_m2} m²)");
            return "**Recent Annotations (last 5):**\n" . $lines->implode("\n");
        }

        // ===== LIST CLASSES =====
        if (preg_match('/(list|show|all|view|what|عرض|ما)\s*(classes?|labels?|categories?|التصنيفات|الفئات)/i', $q)) {
            $classes = AnnotationClass::whereIn('project_id', $user->projects()->pluck('id'))
                ->with('project')->get()->groupBy('project.name');
            if ($classes->isEmpty()) return 'No classes defined yet.';
            $lines = [];
            foreach ($classes as $projName => $cls) {
                $lines[] = "**{$projName}:**";
                foreach ($cls as $c) {
                    $lines[] = "- {$c->name}";
                }
            }
            return "**Annotation Classes:**\n" . implode("\n", $lines);
        }

        // ===== FALLBACK =====
        return "I couldn't understand your question. Try rephrasing:\n\n"
            . "• How many projects do I have?\n"
            . "• Full statistics\n"
            . "• Show health reports\n"
            . "• Total area\n"
            . "• Latest project\n"
            . "• Annotations in project \"test\"\n"
            . "• List classes\n"
            . "• Help";
    }
}

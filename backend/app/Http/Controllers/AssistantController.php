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
        $request->validate(['question' => 'required|string|max:500']);

        $question = trim($request->question);
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
        if (preg_match('/(عدد|كم|count|how many)\s*(المشاريع|project|projects|الخدمات|service|services)/i', $q)) {
            $count = $user->projects()->count();
            return "عدد المشاريع الخاصة بك: **{$count}** مشروع" . ($count != 1 ? '' : '');
        }

        if (preg_match('/(عدد|كم|count|how many)\s*(الصور|images|image|files|files)/i', $q)) {
            $count = ImageUpload::whereIn('project_id', $user->projects()->pluck('id'))->count();
            return "عدد الصور المرفوعة: **{$count}** صورة" . ($count != 1 ? '' : '');
        }

        if (preg_match('/(عدد|كم|count|how many)\s*(التصنيفات|annotations|annotation|التقسيمات)/i', $q)) {
            $count = Annotation::whereIn('image_upload_id', function ($q) use ($user) {
                $q->select('id')->from('image_uploads')->whereIn('project_id', $user->projects()->pluck('id'));
            })->count();
            return "عدد التصنيفات (التقسيمات): **{$count}** تصنيف";
        }

        if (preg_match('/(عدد|كم|count|how many)\s*(المستخدمين|users|user|المسخدمين)/i', $q)) {
            $count = User::count();
            return "عدد المستخدمين المسجلين: **{$count}** مستخدم";
        }

        if (preg_match('/(عدد|كم|count|how many)\s*(الكلاسات|classes|class|labels)/i', $q)) {
            $count = \App\Models\AnnotationClass::whereIn('project_id', $user->projects()->pluck('id'))->count();
            return "عدد كلاسات التصنيف: **{$count}** كلاس";
        }

        // ——— Area queries ———
        if (preg_match('/(اجمالي|total|sum|مجموع|all)\s*(المساحة|area|مساحة)/i', $q)) {
            $total = Annotation::whereIn('image_upload_id', function ($q) use ($user) {
                $q->select('id')->from('image_uploads')->whereIn('project_id', $user->projects()->pluck('id'));
            })->sum('area_m2');
            $totalFormatted = number_format($total, 2);
            return "إجمالي المساحة المُصنَّفة: **{$totalFormatted}** م²";
        }

        // ——— Health queries ———
        if (preg_match('/(صحة|health|المحاصيل|crop|نبات|زرع)/i', $q)) {
            $result = CropHealthResult::whereIn('project_id', $user->projects()->pluck('id'))
                ->latest()->first();
            if ($result) {
                return "آخر تقرير لصحة المحاصيل:\n"
                    . "- 🟢 صحي: **{$result->healthy_percentage}%**\n"
                    . "- 🟡 مجهد: **{$result->stressed_percentage}%**\n"
                    . "- 🔴 غير صحي: **{$result->unhealthy_percentage}%**\n"
                    . "- الحالة العامة: **{$result->overall_status}**";
            }
            return "لا توجد تقارير صحية بعد. قم بتحليل صورة أولاً.";
        }

        // ——— Largest / biggest project ———
        if (preg_match('/(اكبر|أكبر|biggest|largest|most|أكثر)/i', $q) && preg_match('/(مشروع|project)/i', $q)) {
            $project = $user->projects()->withCount('imageUploads')->orderByDesc('image_uploads_count')->first();
            if ($project) {
                return "أكبر مشروع لديك: **{$project->name}** — بعدد **{$project->image_uploads_count}** صورة.";
            }
            return 'لا توجد مشاريع بعد.';
        }

        // ——— Latest project ———
        if (preg_match('/(اخر|آخر|latest|recent|جديد)/i', $q) && preg_match('/(مشروع|project)/i', $q)) {
            $project = $user->projects()->latest()->first();
            if ($project) {
                $date = $project->created_at->format('Y-m-d');
                return "آخر مشروع: **{$project->name}** — تم إنشاؤه في **{$date}**.";
            }
            return 'لا توجد مشاريع بعد.';
        }

        // ——— Summary / statistics ———
        if (preg_match('/(summary|statistics|احصائيات|إحصائيات|تقرير|report|full)/i', $q)) {
            $projectsCount = $user->projects()->count();
            $imagesCount = ImageUpload::whereIn('project_id', $user->projects()->pluck('id'))->count();
            $annotationsCount = Annotation::whereIn('image_upload_id', function ($q) use ($user) {
                $q->select('id')->from('image_uploads')->whereIn('project_id', $user->projects()->pluck('id'));
            })->count();
            $healthCount = CropHealthResult::whereIn('project_id', $user->projects()->pluck('id'))->count();
            $totalArea = Annotation::whereIn('image_upload_id', function ($q) use ($user) {
                $q->select('id')->from('image_uploads')->whereIn('project_id', $user->projects()->pluck('id'));
            })->sum('area_m2');

            return "**إحصائيات كاملة:**\n"
                . "📁 المشاريع: **{$projectsCount}**\n"
                . "🖼️ الصور: **{$imagesCount}**\n"
                . "🏷️ التصنيفات: **{$annotationsCount}**\n"
                . "📐 المساحة المُصنَّفة: **" . number_format($totalArea, 2) . "** م²\n"
                . "🌱 التقارير الصحية: **{$healthCount}**";
        }

        // ——— Specific project stats ———
        if (preg_match('/(مشروع|project)\s*["\']?([^"\']+)["\']?/i', $q, $m)) {
            $name = trim($m[2]);
            $project = $user->projects()->where('name', 'LIKE', "%{$name}%")->first();
            if ($project) {
                $images = $project->imageUploads()->count();
                $annotations = Annotation::whereIn('image_upload_id', $project->imageUploads()->pluck('id'))->count();
                $area = Annotation::whereIn('image_upload_id', $project->imageUploads()->pluck('id'))->sum('area_m2');
                return "مشروع **{$project->name}**:\n"
                    . "- الصور: **{$images}**\n"
                    . "- التصنيفات: **{$annotations}**\n"
                    . "- المساحة: **" . number_format($area, 2) . "** م²\n"
                    . "- الوصف: {$project->description}";
            }
        }

        // ——— Fallback ———
        $examples = "مثال:\n"
            . "- «عدد المشاريع كام»\n"
            . "- «عدد الصور»\n"
            . "- «عدد التصنيفات»\n"
            . "- «إجمالي المساحة»\n"
            . "- «إحصائيات كاملة»\n"
            . "- «تقرير صحة المحاصيل»\n"
            . "- «أكبر مشروع»";

        return "عذراً، لم أفهم السؤال. 😅\nالأسئلة المتاحة:\n{$examples}";
    }
}

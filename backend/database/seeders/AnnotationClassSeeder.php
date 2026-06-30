<?php

namespace Database\Seeders;

use App\Models\AnnotationClass;
use App\Models\Project;
use Illuminate\Database\Seeder;

class AnnotationClassSeeder extends Seeder
{
    public function run(): void
    {
        $projects = Project::all();

        $classesByProject = [
            'المنطقة الزراعية — وادي النطرون' => [
                ['name' => 'محصول صحي', 'color' => '#22c55e'],
                ['name' => 'محصول مجهد', 'color' => '#eab308'],
                ['name' => 'تربة مكشوفة', 'color' => '#d97706'],
                ['name' => 'مباني زراعية', 'color' => '#ef4444'],
                ['name' => 'مصادر مياه', 'color' => '#3b82f6'],
            ],
            'التوسع العمراني — القاهرة الجديدة' => [
                ['name' => 'مباني سكنية', 'color' => '#ef4444'],
                ['name' => 'طرق', 'color' => '#6b7280'],
                ['name' => 'مساحات خضراء', 'color' => '#22c55e'],
                ['name' => 'أراضٍ فارغة', 'color' => '#d97706'],
                ['name' => 'مرافق عامة', 'color' => '#a855f7'],
            ],
            'المسطحات المائية — نهر النيل' => [
                ['name' => 'مياه نظيفة', 'color' => '#3b82f6'],
                ['name' => 'مياه ملوثة', 'color' => '#dc2626'],
                ['name' => 'نباتات مائية', 'color' => '#16a34a'],
                ['name' => 'شواطئ', 'color' => '#f59e0b'],
                ['name' => 'جزر نيلية', 'color' => '#84cc16'],
            ],
        ];

        foreach ($projects as $project) {
            $classes = $classesByProject[$project->name] ?? [];
            foreach ($classes as $cls) {
                AnnotationClass::create([
                    'project_id' => $project->id,
                    'name' => $cls['name'],
                    'color' => $cls['color'],
                ]);
            }
        }

        $this->command->info('✅ Annotation Classes seeded: 15 classes across 3 projects');
    }
}

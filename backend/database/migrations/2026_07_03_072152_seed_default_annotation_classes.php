<?php

use App\Models\Project;
use App\Models\AnnotationClass;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    protected $classes = [
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

    public function up(): void
    {
        $projects = Project::all();
        foreach ($projects as $project) {
            $existing = $project->annotationClasses()->pluck('name')->map(fn($n) => trim(strtolower($n)))->toArray();
            foreach ($this->classes as $class) {
                if (!in_array(strtolower($class['name']), $existing)) {
                    AnnotationClass::create([
                        'project_id' => $project->id,
                        'name'       => $class['name'],
                        'color'      => $class['color'],
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        $names = array_map(fn($c) => $c['name'], $this->classes);
        AnnotationClass::whereIn('name', $names)->delete();
    }
};

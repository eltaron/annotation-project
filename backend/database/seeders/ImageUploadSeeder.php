<?php

namespace Database\Seeders;

use App\Models\ImageUpload;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;

class ImageUploadSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@geolens.com')->first();
        $analyst = User::where('email', 'analyst@geolens.com')->first();
        $projects = Project::all();

        $images = [
            [
                'project_name' => 'المنطقة الزراعية — وادي النطرون',
                'user' => $admin,
                'files' => [
                    ['name' => 'wadi_natrun_2024_04_15.tif', 'size' => 128450560, 'w' => 10980, 'h' => 10980, 'bands' => 4, 'crs' => 'EPSG:32636'],
                    ['name' => 'wadi_natrun_2024_08_20.tif', 'size' => 128450560, 'w' => 10980, 'h' => 10980, 'bands' => 4, 'crs' => 'EPSG:32636'],
                    ['name' => 'wadi_natrun_ndvi_2024.tif', 'size' => 32000000, 'w' => 5490, 'h' => 5490, 'bands' => 1, 'crs' => 'EPSG:32636'],
                ],
            ],
            [
                'project_name' => 'التوسع العمراني — القاهرة الجديدة',
                'user' => $admin,
                'files' => [
                    ['name' => 'new_cairo_2023_01_10.tif', 'size' => 256901120, 'w' => 21960, 'h' => 21960, 'bands' => 4, 'crs' => 'EPSG:32636'],
                    ['name' => 'new_cairo_2024_06_15.tif', 'size' => 256901120, 'w' => 21960, 'h' => 21960, 'bands' => 4, 'crs' => 'EPSG:32636'],
                ],
            ],
            [
                'project_name' => 'المسطحات المائية — نهر النيل',
                'user' => $analyst,
                'files' => [
                    ['name' => 'nile_river_2024_03_01.tif', 'size' => 64225280, 'w' => 5490, 'h' => 5490, 'bands' => 8, 'crs' => 'EPSG:32636'],
                    ['name' => 'nile_river_2024_09_01.tif', 'size' => 64225280, 'w' => 5490, 'h' => 5490, 'bands' => 8, 'crs' => 'EPSG:32636'],
                ],
            ],
        ];

        foreach ($images as $item) {
            $project = $projects->where('name', $item['project_name'])->first();
            if (!$project) continue;

            foreach ($item['files'] as $f) {
                ImageUpload::create([
                    'project_id' => $project->id,
                    'user_id' => $item['user']->id,
                    'original_name' => $f['name'],
                    'file_path' => "projects/{$project->id}/images/{$f['name']}",
                    'file_size' => $f['size'],
                    'width' => $f['w'],
                    'height' => $f['h'],
                    'bands' => $f['bands'],
                    'crs' => $f['crs'],
                ]);
            }
        }

        $this->command->info('✅ Images seeded: 7 images across 3 projects');
    }
}

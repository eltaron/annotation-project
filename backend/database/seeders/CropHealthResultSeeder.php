<?php

namespace Database\Seeders;

use App\Models\CropHealthResult;
use App\Models\ImageUpload;
use App\Models\Project;
use Illuminate\Database\Seeder;

class CropHealthResultSeeder extends Seeder
{
    public function run(): void
    {
        $project1 = Project::where('name', 'المنطقة الزراعية — وادي النطرون')->first();
        $project2 = Project::where('name', 'التوسع العمراني — القاهرة الجديدة')->first();

        if ($project1) {
            $images1 = ImageUpload::where('project_id', $project1->id)->get();

            CropHealthResult::create([
                'project_id' => $project1->id,
                'image_upload_id' => $images1[0]->id ?? null,
                'total_area_m2' => 1250000.00,
                'healthy_area_m2' => 725000.00,
                'stressed_area_m2' => 375000.00,
                'unhealthy_area_m2' => 150000.00,
                'healthy_percentage' => 58.0,
                'stressed_percentage' => 30.0,
                'unhealthy_percentage' => 12.0,
                'overall_status' => 'Good',
                'raw_stats' => json_encode([
                    'ndvi_mean' => 0.52,
                    'ndvi_std' => 0.18,
                    'ndvi_min' => -0.12,
                    'ndvi_max' => 0.91,
                    'red_mean' => 85.3,
                    'nir_mean' => 178.6,
                    'total_pixels_analyzed' => 3840000,
                ]),
            ]);

            CropHealthResult::create([
                'project_id' => $project1->id,
                'image_upload_id' => $images1[1]->id ?? null,
                'total_area_m2' => 1250000.00,
                'healthy_area_m2' => 650000.00,
                'stressed_area_m2' => 425000.00,
                'unhealthy_area_m2' => 175000.00,
                'healthy_percentage' => 52.0,
                'stressed_percentage' => 34.0,
                'unhealthy_percentage' => 14.0,
                'overall_status' => 'Moderate',
                'raw_stats' => json_encode([
                    'ndvi_mean' => 0.45,
                    'ndvi_std' => 0.21,
                    'ndvi_min' => -0.08,
                    'ndvi_max' => 0.87,
                    'red_mean' => 95.7,
                    'nir_mean' => 155.2,
                    'total_pixels_analyzed' => 3840000,
                ]),
            ]);
        }

        if ($project2) {
            $images2 = ImageUpload::where('project_id', $project2->id)->get();

            CropHealthResult::create([
                'project_id' => $project2->id,
                'image_upload_id' => $images2[0]->id ?? null,
                'total_area_m2' => 2500000.00,
                'healthy_area_m2' => 450000.00,
                'stressed_area_m2' => 250000.00,
                'unhealthy_area_m2' => 1800000.00,
                'healthy_percentage' => 18.0,
                'stressed_percentage' => 10.0,
                'unhealthy_percentage' => 72.0,
                'overall_status' => 'Critical',
                'raw_stats' => json_encode([
                    'ndvi_mean' => 0.12,
                    'ndvi_std' => 0.25,
                    'ndvi_min' => -0.35,
                    'ndvi_max' => 0.78,
                    'red_mean' => 156.2,
                    'nir_mean' => 98.4,
                    'built_up_percentage' => 72,
                    'green_percentage' => 18,
                    'bare_soil_percentage' => 10,
                ]),
            ]);
        }

        $this->command->info('✅ Crop Health Results seeded: ' . CropHealthResult::count() . ' reports');
    }
}

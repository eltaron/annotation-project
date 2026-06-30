<?php

namespace Database\Seeders;

use App\Models\Annotation;
use App\Models\AnnotationClass;
use App\Models\ImageUpload;
use App\Models\User;
use Illuminate\Database\Seeder;

class AnnotationSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@geolens.com')->first();
        $analyst = User::where('email', 'analyst@geolens.com')->first();

        // Project 1 annotations
        $img1 = ImageUpload::where('original_name', 'wadi_natrun_2024_04_15.tif')->first();
        if ($img1) {
            $classes = AnnotationClass::where('project_id', $img1->project_id)->get()->keyBy('name');

            Annotation::create([
                'image_upload_id' => $img1->id,
                'annotation_class_id' => $classes['محصول صحي']->id,
                'user_id' => $admin->id,
                'polygon_coordinates' => json_encode([[30.5, 30.5, 30.6, 30.5, 30.6, 30.4, 30.5, 30.4]]),
                'bbox' => json_encode([30.4, 30.4, 30.6, 30.6]),
                'area_pixels' => 45200,
                'area_m2' => 45200.00,
                'classification_label' => 'Healthy Crop',
                'classification_confidence' => 0.964,
                'geo_metadata' => json_encode(['ndvi_mean' => 0.72, 'ndvi_max' => 0.89]),
            ]);

            Annotation::create([
                'image_upload_id' => $img1->id,
                'annotation_class_id' => $classes['محصول مجهد']->id,
                'user_id' => $admin->id,
                'polygon_coordinates' => json_encode([[30.55, 30.45, 30.65, 30.45, 30.65, 30.35, 30.55, 30.35]]),
                'bbox' => json_encode([30.35, 30.35, 30.65, 30.55]),
                'area_pixels' => 28100,
                'area_m2' => 28100.00,
                'classification_label' => 'Stressed Crop',
                'classification_confidence' => 0.882,
                'geo_metadata' => json_encode(['ndvi_mean' => 0.34, 'ndvi_max' => 0.45]),
            ]);

            Annotation::create([
                'image_upload_id' => $img1->id,
                'annotation_class_id' => $classes['تربة مكشوفة']->id,
                'user_id' => $admin->id,
                'polygon_coordinates' => json_encode([[30.45, 30.55, 30.55, 30.55, 30.55, 30.45, 30.45, 30.45]]),
                'bbox' => json_encode([30.45, 30.45, 30.55, 30.55]),
                'area_pixels' => 18300,
                'area_m2' => 18300.00,
                'classification_label' => 'Bare Soil',
                'classification_confidence' => 0.991,
                'geo_metadata' => json_encode(['ndvi_mean' => 0.08, 'ndvi_max' => 0.12]),
            ]);

            Annotation::create([
                'image_upload_id' => $img1->id,
                'annotation_class_id' => $classes['مصادر مياه']->id,
                'user_id' => $admin->id,
                'polygon_coordinates' => json_encode([[30.48, 30.52, 30.58, 30.52, 30.58, 30.42, 30.48, 30.42]]),
                'bbox' => json_encode([30.42, 30.42, 30.58, 30.52]),
                'area_pixels' => 12500,
                'area_m2' => 12500.00,
                'classification_label' => 'Water Body',
                'classification_confidence' => 0.998,
                'geo_metadata' => json_encode(['ndvi_mean' => -0.15, 'ndvi_max' => -0.08]),
            ]);
        }

        // Project 2 annotations
        $img2 = ImageUpload::where('original_name', 'new_cairo_2024_06_15.tif')->first();
        if ($img2) {
            $classes2 = AnnotationClass::where('project_id', $img2->project_id)->get()->keyBy('name');

            Annotation::create([
                'image_upload_id' => $img2->id,
                'annotation_class_id' => $classes2['مباني سكنية']->id,
                'user_id' => $admin->id,
                'polygon_coordinates' => json_encode([[31.5, 30.1, 31.6, 30.1, 31.6, 30.0, 31.5, 30.0]]),
                'bbox' => json_encode([31.0, 30.0, 31.6, 30.1]),
                'area_pixels' => 89000,
                'area_m2' => 89000.00,
                'classification_label' => 'Residential Building',
                'classification_confidence' => 0.945,
                'geo_metadata' => json_encode(['building_count' => 12, 'avg_height_m' => 15]),
            ]);

            Annotation::create([
                'image_upload_id' => $img2->id,
                'annotation_class_id' => $classes2['طرق']->id,
                'user_id' => $admin->id,
                'polygon_coordinates' => json_encode([[31.3, 30.15, 31.7, 30.15, 31.7, 30.12, 31.3, 30.12]]),
                'bbox' => json_encode([31.3, 30.12, 31.7, 30.15]),
                'area_pixels' => 34000,
                'area_m2' => 34000.00,
                'classification_label' => 'Road',
                'classification_confidence' => 0.978,
                'geo_metadata' => json_encode(['road_type' => 'highway', 'length_m' => 4500]),
            ]);

            Annotation::create([
                'image_upload_id' => $img2->id,
                'annotation_class_id' => $classes2['مساحات خضراء']->id,
                'user_id' => $admin->id,
                'polygon_coordinates' => json_encode([[31.55, 30.08, 31.65, 30.08, 31.65, 30.0, 31.55, 30.0]]),
                'bbox' => json_encode([31.55, 30.0, 31.65, 30.08]),
                'area_pixels' => 22000,
                'area_m2' => 22000.00,
                'classification_label' => 'Green Area',
                'classification_confidence' => 0.912,
                'geo_metadata' => json_encode(['ndvi_mean' => 0.65, 'tree_count' => 85]),
            ]);
        }

        // Project 3 annotations
        $img3 = ImageUpload::where('original_name', 'nile_river_2024_09_01.tif')->first();
        if ($img3) {
            $classes3 = AnnotationClass::where('project_id', $img3->project_id)->get()->keyBy('name');

            Annotation::create([
                'image_upload_id' => $img3->id,
                'annotation_class_id' => $classes3['مياه نظيفة']->id,
                'user_id' => $analyst->id,
                'polygon_coordinates' => json_encode([[31.2, 29.95, 31.3, 29.95, 31.3, 29.85, 31.2, 29.85]]),
                'bbox' => json_encode([31.2, 29.85, 31.3, 29.95]),
                'area_pixels' => 56000,
                'area_m2' => 56000.00,
                'classification_label' => 'Clean Water',
                'classification_confidence' => 0.956,
                'geo_metadata' => json_encode(['turbidity_ntu' => 2.3, 'chlorophyll_ugl' => 1.8]),
            ]);

            Annotation::create([
                'image_upload_id' => $img3->id,
                'annotation_class_id' => $classes3['نباتات مائية']->id,
                'user_id' => $analyst->id,
                'polygon_coordinates' => json_encode([[31.25, 29.92, 31.35, 29.92, 31.35, 29.85, 31.25, 29.85]]),
                'bbox' => json_encode([31.25, 29.85, 31.35, 29.92]),
                'area_pixels' => 18500,
                'area_m2' => 18500.00,
                'classification_label' => 'Aquatic Plants',
                'classification_confidence' => 0.889,
                'geo_metadata' => json_encode(['ndvi_mean' => 0.58, 'species' => ' Eichhornia crassipes']),
            ]);
        }

        $this->command->info('✅ Annotations seeded: ' . Annotation::count() . ' annotations');
    }
}

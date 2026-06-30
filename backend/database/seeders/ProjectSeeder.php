<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@geolens.com')->first();
        $analyst = User::where('email', 'analyst@geolens.com')->first();

        Project::create([
            'user_id' => $admin->id,
            'name' => 'المنطقة الزراعية — وادي النطرون',
            'description' => 'تحليل صحة المحاصيل الزراعية في منطقة وادي النطرون باستخدام صور Sentinel-2 متعددة الباندات. يشمل قياس NDVI وتصنيف الغطاء النباتي.',
        ]);

        Project::create([
            'user_id' => $admin->id,
            'name' => 'التوسع العمراني — القاهرة الجديدة',
            'description' => 'رصد التوسع العمراني والتغير في استخدام الأراضي في منطقة القاهرة الجديدة على مدى 3 سنوات.',
        ]);

        Project::create([
            'user_id' => $analyst->id,
            'name' => 'المسطحات المائية — نهر النيل',
            'description' => 'مراقبة جودة المياه وتغير منسوب نهر النيل في موسمي الفيضان والجفاف.',
        ]);

        $this->command->info('✅ Projects seeded: 3 projects');
    }
}

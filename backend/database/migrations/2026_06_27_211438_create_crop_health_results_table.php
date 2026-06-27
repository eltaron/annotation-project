<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crop_health_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('image_upload_id')->nullable()->constrained()->onDelete('set null');
            $table->float('total_area_m2')->default(0);
            $table->float('healthy_area_m2')->default(0);
            $table->float('stressed_area_m2')->default(0);
            $table->float('unhealthy_area_m2')->default(0);
            $table->float('healthy_percentage')->default(0);
            $table->float('stressed_percentage')->default(0);
            $table->float('unhealthy_percentage')->default(0);
            $table->string('overall_status')->nullable();
            $table->json('raw_stats')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crop_health_results');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('annotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('image_upload_id')->constrained()->onDelete('cascade');
            $table->foreignId('annotation_class_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->json('mask_data')->nullable();
            $table->json('polygon_coordinates')->nullable();
            $table->json('bbox')->nullable();
            $table->float('area_pixels')->nullable();
            $table->float('area_m2')->nullable();
            $table->string('classification_label')->nullable();
            $table->float('classification_confidence')->nullable();
            $table->json('geo_metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('annotations');
    }
};

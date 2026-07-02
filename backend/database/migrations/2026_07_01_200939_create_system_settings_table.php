<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        $defaults = [
            'python_path'           => 'python',
            'python_base_path'      => str_replace('\\', '/', base_path('..')),
            'sam_checkpoint_path'   => 'checkpoint/sam_vit_b_01ec64.pth',
            'classifier_weights_path' => 'checkpoint/classifier_weights.pth',
            'max_upload_size_mb'    => '500',
        ];

        foreach ($defaults as $key => $value) {
            DB::table('system_settings')->insert([
                'key' => $key,
                'value' => $value,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};

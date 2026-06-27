<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CropHealthResult extends Model
{
    protected $fillable = [
        'project_id',
        'image_upload_id',
        'total_area_m2',
        'healthy_area_m2',
        'stressed_area_m2',
        'unhealthy_area_m2',
        'healthy_percentage',
        'stressed_percentage',
        'unhealthy_percentage',
        'overall_status',
        'raw_stats',
    ];

    protected $casts = [
        'raw_stats' => 'array',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function imageUpload()
    {
        return $this->belongsTo(ImageUpload::class);
    }
}

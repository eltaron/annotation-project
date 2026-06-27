<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Annotation extends Model
{
    protected $fillable = [
        'image_upload_id',
        'annotation_class_id',
        'user_id',
        'mask_data',
        'polygon_coordinates',
        'bbox',
        'area_pixels',
        'area_m2',
        'classification_label',
        'classification_confidence',
        'geo_metadata',
    ];

    protected $casts = [
        'mask_data' => 'array',
        'polygon_coordinates' => 'array',
        'bbox' => 'array',
        'geo_metadata' => 'array',
    ];

    public function imageUpload()
    {
        return $this->belongsTo(ImageUpload::class);
    }

    public function annotationClass()
    {
        return $this->belongsTo(AnnotationClass::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

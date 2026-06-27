<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'description',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function imageUploads()
    {
        return $this->hasMany(ImageUpload::class);
    }

    public function annotationClasses()
    {
        return $this->hasMany(AnnotationClass::class);
    }

    public function cropHealthResults()
    {
        return $this->hasMany(CropHealthResult::class);
    }

    public function annotations()
    {
        return $this->hasManyThrough(Annotation::class, ImageUpload::class);
    }
}

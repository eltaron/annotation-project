<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImageUpload extends Model
{
    protected $fillable = [
        'project_id',
        'user_id',
        'original_name',
        'file_path',
        'file_size',
        'width',
        'height',
        'bands',
        'crs',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function annotations()
    {
        return $this->hasMany(Annotation::class);
    }
}

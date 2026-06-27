<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnnotationClass extends Model
{
    protected $fillable = [
        'project_id',
        'name',
        'color',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function annotations()
    {
        return $this->hasMany(Annotation::class);
    }
}

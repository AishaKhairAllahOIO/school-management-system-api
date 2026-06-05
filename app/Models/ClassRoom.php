<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassRoom extends Model
{
    protected $guarded = [];

    public function gradeLevel()
    {
        return $this->belongsTo(GradeLevel::class);
    }
}

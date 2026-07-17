<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssessmentComponent extends Model
{
    protected $guarded = [];

    protected $casts = [
        'max_mark' => 'float',
        'weight_percentage' => 'float',
    ];

    public function gradeSubject()
    {
        return $this->belongsTo(GradeSubject::class);
    }
}

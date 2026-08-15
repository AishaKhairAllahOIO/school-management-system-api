<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentComponent extends Model
{
    protected $guarded = [];

    protected $casts = [
        'max_mark' => 'float',
        'weight_percentage' => 'float',
    ];

  public function gradeSubject(): BelongsTo
    {
        return $this->belongsTo(GradeSubject::class, 'grade_subject_id');
    }
    public function studentMarks()
{
    return $this->hasMany(StudentMark::class, 'assessment_component_id');
}
}

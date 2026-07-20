<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GradeSubject extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_failing_subject' => 'boolean',
        'avoid_first_period' => 'boolean',
        'avoid_last_period'  => 'boolean',
        'preferred_period_indexes' => 'array',
        'max_mark' => 'float',
        'passing_mark' => 'float',
        'weight_in_total' => 'float',
    ];

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }
    public function gradeLevel()
    {
        return $this->belongsTo(GradeLevel::class);
    }
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}

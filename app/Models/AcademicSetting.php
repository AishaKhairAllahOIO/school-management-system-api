<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicSetting extends Model
{
    protected $guarded = [];
    protected $casts = [
    'auto_promote_students' => 'boolean',
    'allow_student_repeating' => 'boolean',
    'calculate_gpa' => 'boolean',
    'rank_students' => 'boolean',
    'use_attendance_in_promotion' => 'boolean',
    'maximum_grade' => 'integer',
    'minimum_attendance_percentage' => 'integer',
    'promotion_threshold' => 'integer',
];
    public function currentAcademicYear() {
    return $this->belongsTo(AcademicYear::class, 'current_academic_year_id');
}

    public function gradeScales() {
        return $this->hasMany(GradeScale::class, 'setting_id');
    }
    public function school() {
        return $this->belongsTo(School::class, 'school_id');
    }
}

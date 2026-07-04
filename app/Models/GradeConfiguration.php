<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GradeConfiguration extends Model
{
    protected $guarded = [];
    protected $casts = [
        'planned_classrooms_count' => 'integer',
        'planned_students_capacity' => 'integer',
    ];
    protected $appends = ['actual_classrooms_count', 'actual_students_count'];

    // ... العلاقات (grade, academicYear, supervisor) ...
    public function gradeLevel()
    {
        return $this->belongsTo(GradeLevel::class);
    }
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }
public function getActualClassroomsCountAttribute()
    {
        return Classroom::where('grade_level_id', $this->grade_level_id) // تعديل هنا
                        ->where('academic_year_id', $this->academic_year_id)
                        ->count();
    }

    public function getActualStudentsCountAttribute()
    {
        return Enrollment::where('grade_level_id', $this->grade_level_id) // تعديل هنا
                         ->where('academic_year_id', $this->academic_year_id)
                         ->where('enrollment_status', 'enrolled')
                         ->count();
    }
}

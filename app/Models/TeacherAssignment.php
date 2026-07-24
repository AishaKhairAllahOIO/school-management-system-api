<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherAssignment extends Model
{
    protected $guarded = [];


    public function staff()
    {
        return $this->belongsTo(Staff::class,'teacher_id');
    }

    public function alerts()
    {
        return $this->hasMany(Alert::class);
    }

public function gradeSubject()
{
    return $this->belongsTo(GradeSubject::class, 'grade_subject_id');
}

public function classRoom()
{
    return $this->belongsTo(ClassRoom::class, 'class_room_id');
}

public function academicYear()
{
    return $this->belongsTo(AcademicYear::class, 'academic_year_id');
}
    public function semester(){
        return $this->belongsTo(Semester::class);
    }
}

<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;


class ScheduleEntry extends Model
{


    protected $fillable = [

        'schedule_id',

        'teacher_assignment_id',

        'teacher_id',

        'class_room_id',

        'grade_subject_id',

        'day',

        'period_index',

        'is_locked',

        'source'

    ];

    public function teacher()
{
    return $this->belongsTo(Staff::class,'teacher_id');
}


public function classRoom()
{
    return $this->belongsTo(ClassRoom::class);
}


public function gradeSubject()
{
    return $this->belongsTo(GradeSubject::class);
}


}

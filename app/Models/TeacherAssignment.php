<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherAssignment extends Model
{
    protected $guarded = [];


    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    public function alerts()
    {
        return $this->hasMany(Alert::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function classRoom()
    {
        return $this->belongsTo(ClassRoom::class);
    }
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function semester(){
        return $this->belongsTo(Semester::class);
    }
}

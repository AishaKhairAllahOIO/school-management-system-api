<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherWorkload extends Model
{
    protected $guarded = [];


    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }
}

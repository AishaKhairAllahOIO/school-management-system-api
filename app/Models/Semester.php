<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{

    const FIRST_TERM = 'First_Term';
    const SECOND_TERM = 'Second_Term';
    protected $guarded = [];

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function extraServices()
    {
        return $this->hasMany(ExtraService::class);
    }

    public function scheduleTimeSlots()
    {
        return $this->hasMany(ScheduleTimeSlot::class);
    }


    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{

const FIRST_TERM ='First_Term';
const SECOUND_TERM = 'Secound_Term';
    protected $guarded = [];

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function extraServices()
    {
        return $this->hasMany(ExtraService::class);
    }

    public function scheduleTimeSlot()
    {
        return $this->hasOne(ScheduleTimeSlot::class);
    }


}

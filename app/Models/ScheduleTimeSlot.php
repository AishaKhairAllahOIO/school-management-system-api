<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleTimeSlot extends Model
{
    protected $guarded = [];

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }

    public function day()
    {
        return $this->belongsTo(Day::class);
    }
    
}

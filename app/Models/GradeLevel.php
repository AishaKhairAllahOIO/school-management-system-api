<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GradeLevel extends Model
{
    protected $guarded = [];



    public function classRooms()
    {
        return $this->hasMany(ClassRoom::class);
    }

    public function enrollments()
    {
        return $this->hasManyThrough(Enrollment::class, ClassRoom::class);
    }

    public function activities()
    {
        return $this->hasMany(Activity::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{

    protected $guarded = [];



    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function extraServices()
    {
        return $this->hasMany(ExtraService::class);
    }

    public function semesters()
    {
        return $this->hasMany(Semester::class);
    }


}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicStage extends Model
{
    protected $guarded = [];


    public function gradeLevels()
    {
        return $this->hasMany(GradeLevel::class);
    }

}

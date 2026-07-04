<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\AcademicStageType;

class AcademicStage extends Model
{
    protected $guarded = [];

    protected $casts = [
        'type' => AcademicStageType::class,
    ];
    public function gradeLevels()
    {
        return $this->hasMany(GradeLevel::class);
    }

}

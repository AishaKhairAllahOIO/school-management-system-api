<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeePlan extends Model
{
      use SoftDeletes;
    protected $guarded = [];

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function gradeLevel()
    {
        return $this->belongsTo(GradeLevel::class);
    }

    public function installmentPolicy()
    {
        return $this->belongsTo(InstallmentPolicy::class);
    }

    public function extraServices()
    {
        return $this->hasMany(FeePlanExtraService::class);
    }
}

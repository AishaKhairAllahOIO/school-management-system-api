<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicSetting extends Model
{
    protected $guarded = [];
    protected $casts = [
        'schedule_settings' => 'array', 
    ];
    public function currentAcademicYear() {
    return $this->belongsTo(AcademicYear::class, 'current_academic_year_id');
}
    public function currentSemester()
    {
        return $this->belongsTo(Semester::class, 'current_semester_id');
    }

    public function gradeScales() {
        return $this->hasMany(GradeScale::class, 'setting_id');
    }

}

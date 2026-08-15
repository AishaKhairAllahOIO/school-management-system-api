<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{

    protected $guarded = [];

    protected $casts = [
        'is_current' => 'boolean',
        'start_date' => 'date:Y-m-d',
        'end_date'   => 'date:Y-m-d',
    ];

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
    public function academicSetting() {
    return $this->hasOne(AcademicSetting::class, 'current_academic_year_id');
}
    public function teacherWorkloads()
    {
        return $this->hasMany(TeacherWorkload::class);
    }

    public function teacherAssignments()
    {
        return $this->hasMany(TeacherAssignment::class);
    }
    public function staffFinancialContracts()
    {
        return $this->hasMany(StaffFinancialContract::class);
    }
    public function reportCards()
    {
        return $this->hasMany(ReportCard::class);
    }


}

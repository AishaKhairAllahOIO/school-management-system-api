<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffLeave extends Model
{
    protected $guarded = [];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'days_count' => 'integer',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    public function leaveType()
    {
        return $this->belongsTo(StaffLeaveType::class, 'leave_type_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    // علاقة عكسية لمعرفة الأيام التي تم تسجيل غياب الموظف فيها بسبب هذه الإجازة
    public function attendances()
    {
        return $this->hasMany(StaffAttendance::class, 'staff_leave_id');
    }
}

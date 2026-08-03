<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffAttendance extends Model
{
    protected $guarded = [];

    protected $casts = [
        'attendance_date' => 'date',
    ];

    public function staff() 
    {
        return $this->belongsTo(Staff::class);
    }

    public function leave() 
    {
        return $this->belongsTo(StaffLeave::class, 'staff_leave_id');
    }

    // علاقة لجلب تفاصيل حصص المعلم (إذا كان الموظف معلماً) في هذا اليوم
    public function periodAttendances()
    {
        return $this->hasMany(TeacherPeriodAttendance::class, 'staff_attendance_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherPeriodAttendance extends Model
{
    protected $guarded = [];

    public function dailyAttendance()
    {
        return $this->belongsTo(StaffAttendance::class, 'staff_attendance_id');
    }

    // تأكدي من وجود موديل ScheduleTimeSlot مسبقاً لديكِ في النظام
    public function scheduleEntry()
    {
        return $this->belongsTo(ScheduleEntry::class, 'schedule_entry_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{

    const FIRST_TERM = 'First_Term';
    const SECOND_TERM = 'Second_Term';
    protected $guarded = [];
    protected $casts = [
        'is_current'    => 'boolean',
        'is_final_term' => 'boolean',
        'order'         => 'integer',
        'start_date'    => 'date:Y-m-d',
        'end_date'      => 'date:Y-m-d',
    ];
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
    public function academicSetting() {
        return $this->hasOne(AcademicSetting::class, 'current_semester_id');
    }

    public function scheduleTimeSlots()
    {
        return $this->hasMany(ScheduleTimeSlot::class);
    }
        public function teacherAssignments()
    {
        return $this->hasMany(TeacherAssignment::class, 'academic_term_id'); // تأكدي من اسم الفورين كي لديكم
    }
    public function reportCards()
    {
        return $this->hasMany(ReportCard::class);
    }


}

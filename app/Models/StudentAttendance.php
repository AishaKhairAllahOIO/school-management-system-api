<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentAttendance extends Model
{
    protected $guarded = [];

    protected $casts = [
    'attendance_date' => 'date:Y-m-d',
];
    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function classRoom()
    {
        return $this->belongsTo(ClassRoom::class);
    }
}

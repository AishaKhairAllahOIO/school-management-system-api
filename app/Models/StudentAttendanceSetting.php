<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute; // أضيفي هذا الـ namespace
class StudentAttendanceSetting extends Model
{
    protected $guarded = [];

    protected $casts = [
        'working_days' => 'integer',
        'required_attendance_percentage' => 'decimal:2',
    ];

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }
    protected function allowedAbsenceDays(): Attribute
    {
        return Attribute::make(
            get: fn () => (int) floor(
                $this->working_days * (1 - ($this->required_attendance_percentage / 100))
            )
        );
    }
}

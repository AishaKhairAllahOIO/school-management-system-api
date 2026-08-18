<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CounselorAppointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'counselor_id',
        'student_id',
        'appointment_date',
        'start_time',
        'end_time',
        'booking_status',
        'slot_status',

    ];

    protected $casts = [
        'appointment_date' => 'date',
    ];

    public function counselor()
    {
        return $this->belongsTo(Staff::class, 'counselor_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function session()
    {
        return $this->hasOne(CounselingSession::class, 'appointment_id');
    }



    public function counselingSession()
    {
        return $this->hasOne(CounselingSession::class, 'appointment_id');
    }
}

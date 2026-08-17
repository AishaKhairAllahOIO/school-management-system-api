<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CounselingSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_id',
        'attendance_status',
        'assessment',
        'notes',
    ];

    public function appointment()
    {
        return $this->belongsTo(
            CounselorAppointment::class,
            'appointment_id'
        );
    }
}
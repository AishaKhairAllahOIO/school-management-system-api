<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class CounselorAvailability extends Model
{
    use HasFactory;


    protected $fillable = [

        'counselor_id',

        'day',

        'start_time',

        'end_time',

        'session_duration',

        'daily_sessions_limit',

        'is_active',

    ];


    public function counselor()
    {
        return $this->belongsTo(Staff::class,'counselor_id');
    }


}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimeSlot extends Model
{
    const SLOT_1 = 'First Slot';
    const SLOT_2 = 'Second Slot';
    const SLOT_3 = 'Third Slot';
    const SLOT_4 = 'Fourth Slot';
    const SLOT_5 = 'Fifth Slot';
    const SLOT_6 = 'Sixth Slot';
    const SLOT_7 = 'Seventh Slot';

    const START_TIME_1 = '08:00';
    const END_TIME_1 = '08:45';
    const START_TIME_2 = '08:45';
    const END_TIME_2 = '09:30';
    const START_TIME_3 = '9:45';
    const END_TIME_3 = '10:30';
    const START_TIME_4 = '10:30';
    const END_TIME_4 = '11:15';
    const START_TIME_5 = '11:30';
    const END_TIME_5 = '12:15';
    const START_TIME_6 = '12:15';
    const END_TIME_6 = '13:00';
    const START_TIME_7 = '13:15';
    const END_TIME_7 = '14:00';
    protected $guarded = [];
}

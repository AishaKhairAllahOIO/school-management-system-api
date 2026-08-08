<?php

namespace App\Http\Controllers\Scheduling;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Http\Resources\ScheduleResource;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function show($id)
    {
        // استخدام Eager Loading مهم جداً لتجنب N+1 Problem
        $schedule = Schedule::with([
            'entries.teacher',
            'entries.classRoom',
            'entries.gradeSubject.subject'
        ])->findOrFail($id);

        return new ScheduleResource($schedule);
    }
}

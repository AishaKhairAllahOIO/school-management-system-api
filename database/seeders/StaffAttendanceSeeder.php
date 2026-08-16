<?php

namespace Database\Seeders;

use App\Models\Staff;
use App\Models\StaffAttendance;
use App\Models\StaffLeave;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class StaffAttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $staffList = Staff::take(2)->get();
        if ($staffList->isEmpty()) return;

        $today = Carbon::now()->toDateString();
        
        StaffAttendance::updateOrCreate(
            ['staff_id' => $staffList[1]->id, 'attendance_date' => $today],
            [
                'status' => 'absent',
                'absence_type' => 'unexcused',
            ]
        );
          

        $leave = StaffLeave::where('staff_id', $staffList[0]->id)->first();
        if ($leave) {
            $currentDate = Carbon::parse($leave->start_date);
            $endDate = Carbon::parse($leave->end_date);

            while ($currentDate->lte($endDate)) {
                StaffAttendance::updateOrCreate(
                    ['staff_id' => $leave->staff_id, 'attendance_date' => $currentDate->toDateString()],
                    [
                        'status' => 'on_leave',
                        'absence_type' => null,
                        'staff_leave_id' => $leave->id,
                    ]
                );
                $currentDate->addDay();
            }
        }
    }
}
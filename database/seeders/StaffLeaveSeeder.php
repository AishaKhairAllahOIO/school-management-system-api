<?php

namespace Database\Seeders;

use App\Models\Staff;
use App\Models\StaffLeave;
use App\Models\StaffLeaveType;
use App\Models\AcademicYear;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class StaffLeaveSeeder extends Seeder
{
    public function run(): void
    {
        $staff = Staff::first();
        $unpaidLeaveType = StaffLeaveType::where('payment_type', 'unpaid')->first();
        $currentYear = AcademicYear::where('is_current', true)->first();

        if (!$staff || !$unpaidLeaveType || !$currentYear) return;

        $startDate = Carbon::now()->startOfMonth()->addDays(5);
        $endDate = $startDate->copy()->addDay(); 

        StaffLeave::updateOrCreate(
            [
                'staff_id' => $staff->id,
                'academic_year_id' => $currentYear->id,
                'start_date' => $startDate->toDateString(),
            ],
            [
                'leave_type_id' => $unpaidLeaveType->id,
                'end_date' => $endDate->toDateString(),
                'days_count' => 2,
            ]
        );
    }
}
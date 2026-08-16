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


        $startDate1 = Carbon::now()->startOfMonth()->addDays(5);
        $startDate2 = Carbon::now()->startOfMonth()->addDays(10);
        $startDate3 = Carbon::now()->startOfMonth()->addDays(20);
        $startDate4 = Carbon::now()->startOfMonth()->addDays(13);
        $endDate1 = $startDate1->copy()->addDay();
        $endDate2 = $startDate2->copy()->addDay();
        $endDate3 = $startDate3->copy()->addDay();
        $endDate4 = $startDate4->copy()->addDay();

        StaffLeave::updateOrCreate(
            [
                'staff_id' => 1,
                'academic_year_id' => 1,
                'start_date' => $startDate1->toDateString(),
            ],
            [
                'leave_type_id' => 2 ,
                'end_date' => $endDate1->toDateString(),
                'days_count' => 2,
            ]
        );
        StaffLeave::updateOrCreate(
            [
                'staff_id' => 1,
                'academic_year_id' => 1,
                'start_date' => $startDate2->toDateString(),
            ],
            [
                'leave_type_id' => 1,
                'end_date' => $endDate2->toDateString(),
                'days_count' => 1,
            ]
        );
        StaffLeave::updateOrCreate(
            [
                'staff_id' => 1,
                'academic_year_id' => 1,
                'start_date' => $startDate3->toDateString(),
            ],
            [
                'leave_type_id' => 3,
                'end_date' => $endDate3->toDateString(),
                'days_count' => 5,
            ]
        );
        StaffLeave::updateOrCreate(
            [
                'staff_id' => 1,
                'academic_year_id' => 1,
                'start_date' => $startDate4->toDateString(),
            ],
            [
                'leave_type_id' => 4,
                'end_date' => $endDate4->toDateString(),
                'days_count' => 15,
            ]
        );
    }
}

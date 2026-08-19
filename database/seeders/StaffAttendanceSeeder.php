<?php

namespace Database\Seeders;

use App\Models\Staff;
use App\Models\StaffAttendance;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class StaffAttendanceSeeder extends Seeder
{
    public function run(): void
    {
        // جلب موظف مختلف (مثلاً الموظف الثاني) لكي لا نتدخل في أيام إجازات الموظف الأول
        $staff = Staff::skip(1)->first();
        if (!$staff) return;

        // يوم للغياب الكلي (بدون عذر)
        $absentDate = Carbon::now()->startOfMonth()->addDays(7)->toDateString();
        
        // يوم للغياب الجزئي (بعذر)
        $partialDate = Carbon::now()->startOfMonth()->addDays(8)->toDateString();

        // 1. تسجيل غياب كلي
        StaffAttendance::updateOrCreate(
            [
                'staff_id'        => $staff->id,
                'attendance_date' => $absentDate,
            ],
            [
                'status'         => 'absent',
                'absence_type'   => 'unexcused',
                'staff_leave_id' => null,
            ]
        );

        // 2. تسجيل غياب جزئي
   
        // ملاحظة: لم نقم بكتابة "حاضر" (present) لأن عدم وجود السجل يعني أن الموظف حاضر!
    }
}
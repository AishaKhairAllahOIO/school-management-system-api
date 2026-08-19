<?php

namespace Database\Seeders;

use App\Models\Staff;
use App\Models\StaffLeave;
use App\Models\StaffAttendance;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class StaffLeaveSeeder extends Seeder
{
    public function run(): void
    {
        $staff = Staff::first();
        if (!$staff) return;

        $academicYearId = 1; // افترضنا أن 1 هو العام الحالي

        // تحديد فترات لا تتداخل أبداً (تصحيح التواريخ)
        $leaves = [
            [
                'type'  => 2,
                'start' => Carbon::now()->startOfMonth()->addDays(1), // يوم 2
                'end'   => Carbon::now()->startOfMonth()->addDays(2), // يوم 3 (المدة 2 أيام)
            ],
            [
                'type'  => 1,
                'start' => Carbon::now()->startOfMonth()->addDays(5), // يوم 6
                'end'   => Carbon::now()->startOfMonth()->addDays(5), // يوم 6 (المدة 1 يوم)
            ],
            [
                'type'  => 3,
                'start' => Carbon::now()->startOfMonth()->addDays(10), // يوم 11
                'end'   => Carbon::now()->startOfMonth()->addDays(14), // يوم 15 (المدة 5 أيام)
            ],
            [
                'type'  => 4,
                'start' => Carbon::now()->startOfMonth()->addDays(20), // يوم 21
                'end'   => Carbon::now()->startOfMonth()->addDays(25), // يوم 26 (المدة 6 أيام لتجنب التداخل)
            ],
        ];

        foreach ($leaves as $leaveData) {
            $startDate = $leaveData['start'];
            $endDate   = $leaveData['end'];
            $daysCount = $startDate->diffInDays($endDate) + 1; // حساب دقيق برمجياً

            // 1. زراعة الإجازة
            $leave = StaffLeave::updateOrCreate(
                [
                    'staff_id'   => $staff->id,
                    'start_date' => $startDate->toDateString(),
                ],
                [
                    'academic_year_id' => $academicYearId,
                    'leave_type_id'    => $leaveData['type'],
                    'end_date'         => $endDate->toDateString(),
                    'days_count'       => $daysCount,
                ]
            );

            // 2. محاكاة السيرفيس: زراعة أيام الإجازة في جدول الحضور فوراً
            $currentDate = $startDate->copy();
            while ($currentDate->lte($endDate)) {
                StaffAttendance::updateOrCreate(
                    [
                        'staff_id'        => $staff->id,
                        'attendance_date' => $currentDate->toDateString(),
                    ],
                    [
                        'status'         => 'on_leave',
                        'absence_type'   => null,
                        'staff_leave_id' => $leave->id,
                    ]
                );
                $currentDate->addDay();
            }
        }
    }
}
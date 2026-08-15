<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\StudentAttendance;

class StudentAttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $date1 = '2026-08-01';
        $date2 = '2026-08-02';
        $date3 = '2026-08-03';
        
        $attendances = [
            ['enrollment_id' => 1, 'semester_id' => 1, 'class_room_id' => 1, 'attendance_date' => $date1, 'status' => 'present', 'absence_type' => null],
            ['enrollment_id' => 1, 'semester_id' => 1, 'class_room_id' => 1, 'attendance_date' => $date2, 'status' => 'present', 'absence_type' => null],


            ['enrollment_id' => 2, 'semester_id' => 1, 'class_room_id' => 2, 'attendance_date' => $date1, 'status' => 'absent', 'absence_type' => 'excused'],
            ['enrollment_id' => 2, 'semester_id' => 1, 'class_room_id' => 2, 'attendance_date' => $date2, 'status' => 'present', 'absence_type' => null],
            ['enrollment_id' => 2, 'semester_id' => 1, 'class_room_id' => 2, 'attendance_date' => $date3, 'status' => 'present', 'absence_type' => null, ],


            ['enrollment_id' => 3, 'semester_id' => 1, 'class_room_id' => 1, 'attendance_date' => $date1, 'status' => 'absent', 'absence_type' => 'unexcused'],
            ['enrollment_id' => 3, 'semester_id' => 1, 'class_room_id' => 1, 'attendance_date' => $date2, 'status' => 'absent', 'absence_type' => 'unexcused'],
            ['enrollment_id' => 3, 'semester_id' => 1, 'class_room_id' => 1, 'attendance_date' => $date3, 'status' => 'present', 'absence_type' => null],

       ];

        foreach ($attendances as $record) {
            StudentAttendance::updateOrCreate(
                [
                    'enrollment_id' => $record['enrollment_id'],
                    'attendance_date' => $record['attendance_date']
                ],
                $record
            );
        }
    }
}

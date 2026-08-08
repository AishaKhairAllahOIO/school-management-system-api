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
        $date4 = '2026-08-04';
        
        $attendances = [
            // 🎓 Student 1 (Enrollment 1): The Perfect Student (All Present)
            ['enrollment_id' => 1, 'semester_id' => 1, 'class_room_id' => 1, 'attendance_date' => $date1, 'status' => 'present', 'absence_type' => null],
            ['enrollment_id' => 1, 'semester_id' => 1, 'class_room_id' => 1, 'attendance_date' => $date2, 'status' => 'present', 'absence_type' => null],

            // 🎓 Student 2 (Enrollment 2): Normal Student (Excused Absences - Sick)
            // هذا الطالب لن ينقص رصيده المسموح لأن غيابه مبرر (excused)
            ['enrollment_id' => 2, 'semester_id' => 1, 'class_room_id' => 1, 'attendance_date' => $date1, 'status' => 'absent', 'absence_type' => 'excused'],
            ['enrollment_id' => 2, 'semester_id' => 1, 'class_room_id' => 1, 'attendance_date' => $date2, 'status' => 'present', 'absence_type' => null],
            ['enrollment_id' => 2, 'semester_id' => 1, 'class_room_id' => 1, 'attendance_date' => $date3, 'status' => 'present', 'absence_type' => null, ],

            // 🎓 Student 3 (Enrollment 3): Careless Student (Unexcused Absences)
            // هذا الطالب سينقص رصيده بمقدار 2
            ['enrollment_id' => 3, 'semester_id' => 1, 'class_room_id' => 2, 'attendance_date' => $date1, 'status' => 'absent', 'absence_type' => 'unexcused'],
            ['enrollment_id' => 3, 'semester_id' => 1, 'class_room_id' => 2, 'attendance_date' => $date2, 'status' => 'absent', 'absence_type' => 'unexcused'],
            ['enrollment_id' => 3, 'semester_id' => 1, 'class_room_id' => 2, 'attendance_date' => $date3, 'status' => 'present', 'absence_type' => null],

            // 🎓 Student 4 (Enrollment 4): Student in Danger (Warning Level)
            // هذا الطالب سيتم خصم أيام كثيرة من رصيده ليظهر في الفرونت إند كحالة إنذار!
            ['enrollment_id' => 4, 'semester_id' => 1, 'class_room_id' => 3, 'attendance_date' => $date1, 'status' => 'absent', 'absence_type' => 'unexcused'],
            ['enrollment_id' => 4, 'semester_id' => 1, 'class_room_id' => 3, 'attendance_date' => $date2, 'status' => 'absent', 'absence_type' => 'unexcused'],
            ['enrollment_id' => 4, 'semester_id' => 1, 'class_room_id' => 3, 'attendance_date' => $date3, 'status' => 'absent', 'absence_type' => 'unexcused'],
            ['enrollment_id' => 4, 'semester_id' => 1, 'class_room_id' => 3, 'attendance_date' => $date4, 'status' => 'absent', 'absence_type' => 'unexcused'],
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

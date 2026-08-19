<?php

namespace Database\Seeders;

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
        
        // 💡 تذكر: بناءً على معمارية "الحضور بالاستثناء"، نحن نخزن فقط سجلات الغياب.
        // أي طالب لا يمتلك سجلاً في يوم معين، سيقوم السيرفيس بافتراض أنه "حاضر" (present).
        
        $attendances = [
            // ==========================================
            // الطالب الأول (enrollment_id: 1)
            // ==========================================
            // لن نقوم بإنشاء أي سجل له! لأنه حاضر في الأيام الثلاثة.
            // غياب السجل في الداتابيز يعني أنه (حاضر).


            // ==========================================
            // الطالب الثاني (enrollment_id: 2)
            // ==========================================
            // غاب في اليوم الأول (بعذر)، وحاضر في باقي الأيام (لذلك لن نكتب اليومين الآخرين).
            [
                'enrollment_id'   => 2, 
                'semester_id'     => 1, 
                'class_room_id'   => 2, 
                'attendance_date' => $date1, 
                'status'          => 'absent', 
                'absence_type'    => 'excused'
            ],


            // ==========================================
            // الطالب الثالث (enrollment_id: 3)
            // ==========================================
            // غاب في اليوم الأول والثاني (بدون عذر)، وحاضر في اليوم الثالث (فلن نكتب اليوم الثالث).
            [
                'enrollment_id'   => 3, 
                'semester_id'     => 1, 
                'class_room_id'   => 1, 
                'attendance_date' => $date1, 
                'status'          => 'absent', 
                'absence_type'    => 'unexcused'
            ],
            [
                'enrollment_id'   => 3, 
                'semester_id'     => 1, 
                'class_room_id'   => 1, 
                'attendance_date' => $date2, 
                'status'          => 'absent', 
                'absence_type'    => 'unexcused'
            ],
        ];

        foreach ($attendances as $record) {
            StudentAttendance::updateOrCreate(
                [
                    'enrollment_id'   => $record['enrollment_id'],
                    'attendance_date' => $record['attendance_date']
                ],
                $record
            );
        }
    }
}
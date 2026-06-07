<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ScheduleTimeSlot;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\GradeLevel;
use App\Models\ClassRoom;
use App\Models\Day;
use App\Models\TimeSlot;
use App\Models\Subject;
use App\Models\Staff;
use Carbon\Carbon;

class ScheduleTimeSlotSeeder extends Seeder
{
    public function run(): void
    {
        // 1. جلب الكيانات الأساسية التي تم إنشاؤها في الـ Seeders السابقة
        $year = AcademicYear::first();
        $semester = Semester::first();
        $grade = GradeLevel::first();      // الصف السابع
        $classRoom = ClassRoom::first();   // الشعبة الأولى
        $staff = Staff::first();           // جلب أول أستاذ (تأكدي من وجود بيانات في StaffSeeder)

        if (!$year || !$semester || !$grade || !$classRoom || !$staff) {
            $this->command->warn('يرجى التأكد من تشغيل Seeders (السنة، الفصل، الصف، الشعبة، والموظفين) قبل جدول الحصص.');
            return;
        }

        // 2. ضمان وجود المواد الدراسية (Subjects)
        $math = Subject::firstOrCreate(['subject_name' => 'رياضيات']);
        $science = Subject::firstOrCreate(['subject_name' => 'فيزياء']);

        // 3. ضمان وجود الأوقات الدراسية (Time Slots)
        $slot1 = TimeSlot::firstOrCreate([
            'slot_name' => TimeSlot::SLOT_1 ?? 'First Slot',
            'start_time' => '08:00',
            'end_time' => '08:45'
        ]);

        $slot2 = TimeSlot::firstOrCreate([
            'slot_name' => TimeSlot::SLOT_2 ?? 'Second Slot',
            'start_time' => '08:45',
            'end_time' => '09:30'
        ]);

        // 4. إنشاء يوم "الغد" بشكل ديناميكي لضمان نجاح الـ API دائماً عند الاختبار
        $tomorrowName = Carbon::tomorrow()->format('l');
        $tomorrow = Day::firstOrCreate(['day_name' => $tomorrowName]);

        // 5. ربط الحصة الأولى ليوم غد بنفس السنة والفصل والصف والشعبة
        ScheduleTimeSlot::updateOrCreate([
            'day_id' => $tomorrow->id,
            'time_slot_id' => $slot1->id,
            'class_room_id' => 1,
            'semester_id' => 2,
        ], [
            'subject_id' => $math->id,
            'grade_level_id' => 1,
            'academic_year_id' => 1,
            'staff_id' => $staff->id,
        ]);

        // 6. ربط الحصة الثانية ليوم غد
        ScheduleTimeSlot::updateOrCreate([
            'day_id' => $tomorrow->id,
            'time_slot_id' => $slot2->id,
            'class_room_id' => $classRoom->id,
            'semester_id' => $semester->id,
        ], [
            'subject_id' => $science->id,
            'grade_level_id' => $grade->id,
            'academic_year_id' => $year->id,
            'staff_id' => $staff->id,
        ]);
    }
}

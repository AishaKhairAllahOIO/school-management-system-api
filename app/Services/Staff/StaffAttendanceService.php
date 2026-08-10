<?php
namespace App\Services\Staff;
use App\Models\StaffAttendance;
use App\Models\TeacherPeriodAttendance;
use App\Models\ScheduleEntry;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StaffAttendanceService
{
    /**
     * 1. إنشاء سجل حضور/غياب جديد
     */
    public function storeAttendance(array $data): StaffAttendance
    {
        return DB::transaction(function () use ($data) {
            // 1. إنشاء سجل الأب (الدوام اليومي)
            $attendance = StaffAttendance::create([
                'staff_id'        => $data['staff_id'],
                'attendance_date' => $data['attendance_date'],
                'status'          => $data['status'],
                'absence_type'    => $data['absence_type'] ?? null,
                'staff_leave_id'  => $data['staff_leave_id'] ?? null,
            ]);

            // 2. بناء الأبناء (الحصص الفائتة) بناءً على الحالة
            $this->buildMissedPeriods($attendance, $data);

            return $attendance->load('periodAttendances');
        });
    }

    /**
     * 2. تعديل سجل غياب موجود مسبقاً (Wipe and Replace)
     */
    public function updateAttendance(int $attendanceId, array $data): StaffAttendance
    {
        return DB::transaction(function () use ($attendanceId, $data) {
            $attendance = StaffAttendance::findOrFail($attendanceId);

            // 1. تعديل الأب
            $attendance->update([
                'status'          => $data['status'],
                'absence_type'    => $data['absence_type'] ?? null,
                'staff_leave_id'  => $data['staff_leave_id'] ?? null,
            ]);

            // 2. 🧹 التصفية (Wipe): مسح كل الحصص المرتبطة بهذا اليوم لتجنب التكرار
            TeacherPeriodAttendance::where('staff_attendance_id', $attendance->id)->delete();

            // 3. 🏗️ إعادة البناء (Replace): بناء الحصص الجديدة بناءً على الحالة المعدلة
            $this->buildMissedPeriods($attendance, $data);

            return $attendance->load('periodAttendances');
        });
    }

    /**
     * 3. حذف سجل الغياب
     */
    public function deleteAttendance(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $attendance = StaffAttendance::findOrFail($id);
            $attendance->delete(); // الأبناء سيحذفون تلقائياً بسبب Cascade
            return true;
        });
    }
    public function getAttendanceById(int $id): StaffAttendance
    {
       return StaffAttendance::with([
            'periodAttendances.scheduleEntry.classRoom',
            'periodAttendances.scheduleEntry.gradeSubject'
        ])->findOrFail($id)->refresh(); // إعادة تحميل البيانات بعد التحديث
    }

    /**
     * 🛠️ دالة مساعدة مركزية: تبني الحصص الفائتة بناءً على حالة المعلم
     */
    private function buildMissedPeriods(StaffAttendance $attendance, array $data): void
    {
        $status = $data['status'];

        if (in_array($status, ['absent', 'on_leave'])) {
            $dayName = strtolower(Carbon::parse($attendance->attendance_date)->englishDayOfWeek);
            
           $missedPeriods = ScheduleEntry::whereHas('schedule', function($q) {
                    $q->where('status', 'active'); // أو published حسب ما سميتيه عندك
                })
                ->where('teacher_id', $attendance->staff_id)
                ->where('day', $dayName)
                ->get();

            foreach ($missedPeriods as $period) {
                TeacherPeriodAttendance::create([
                    'staff_attendance_id' => $attendance->id,
                    'schedule_entry_id'   => $period->id,
                ]);
            }
        }
        elseif ($status === 'partial_absence' && !empty($data['missing_periods'])) {
            foreach ($data['missing_periods'] as $periodId) {
                TeacherPeriodAttendance::create([
                    'staff_attendance_id' => $attendance->id,
                    'schedule_entry_id'   => $periodId,
                ]);
            }
        }
       
    }
}
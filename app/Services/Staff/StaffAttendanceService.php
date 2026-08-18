<?php

namespace App\Services\Staff;

use App\Models\StaffAttendance;
use App\Models\TeacherPeriodAttendance;
use App\Models\ScheduleEntry;
use App\Models\Staff;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Services\User\AlertService;

class StaffAttendanceService
{
    protected AlertService $alertService;

    // 💡 4. حقن التبعية داخل المُشيّد (Constructor)
    public function __construct(AlertService $alertService)
    {
        $this->alertService = $alertService;
    }
    public function storeAttendance(array $data): StaffAttendance
    {
        return DB::transaction(function () use ($data) {
            $status = $data['status'];
            $staffId = $data['staff_id'];
            $attendanceDate = $data['attendance_date'];

            // 💡 إذا كان الموظف حاضراً، نمسح أي غياب مسجل له مسبقاً
            if ($status === 'present') {
                StaffAttendance::where('staff_id', $staffId)
                    ->where('attendance_date', $attendanceDate)
                    ->delete(); // الحصص الفائتة (الأبناء) تُحذف تلقائياً بسبب الـ Cascade

                // نُرجع كائن (Object) وهمي يحمل الحالة 'present' لكي لا يظهر خطأ في الكنترولر
                $dummyAttendance = new StaffAttendance($data);
                $dummyAttendance->id = null;
                return $dummyAttendance;
            }

            // --- 💡 التعديل هنا: تنظيف البيانات (Data Sanitization) ---
            // إذا كانت الحالة "إجازة"، نجبر نوع الغياب أن يكون null
            $absenceType = ($status === 'on_leave') ? null : ($data['absence_type'] ?? null);
            $staffLeaveId = ($status === 'on_leave') ? ($data['staff_leave_id'] ?? null) : null;
            // -----------------------------------------------------------

            // 1. إنشاء أو تحديث سجل الأب (الدوام اليومي) لمنع التكرار
            $attendance = StaffAttendance::updateOrCreate(
                [
                    'staff_id'        => $staffId,
                    'attendance_date' => $attendanceDate,
                ],
                [
                    'status'          => $status,
                    'absence_type'    => $absenceType, // نمرر المتغير المنظف
                    'staff_leave_id'  => $staffLeaveId, // نمرر المتغير المنظف
                ]
            );

            // 2. 🧹 التصفية: مسح كل الحصص المرتبطة بهذا اليوم لتجنب تكرارها عند التحديث
            TeacherPeriodAttendance::where('staff_attendance_id', $attendance->id)->delete();

            // 3. بناء الأبناء (الحصص الفائتة) بناءً على الحالة
            $this->buildMissedPeriods($attendance, $data);
            if (in_array($status, ['absent', 'partial_absence'])) {
                $staff = Staff::find($staffId);
                
                if ($staff) {
                    // إرسال الإشعار وتمرير نوع الغياب ضمن الـ Meta لتوضيحه في الإشعار
                    $this->alertService->createStaffAbsence($staff, [
                        'absence_type' => $absenceType
                    ]);
                }
            }

            return $attendance->load('periodAttendances.scheduleEntry.classRoom', 'periodAttendances.scheduleEntry.gradeSubject');
        });
    }

    /**
     * 2. تعديل سجل غياب موجود مسبقاً (Wipe and Replace)
     */
    public function updateAttendance(int $attendanceId, array $data): StaffAttendance
    {
        return DB::transaction(function () use ($attendanceId, $data) {
            $attendance = StaffAttendance::findOrFail($attendanceId);

            // 💡 إذا تم تعديل الغياب إلى "حاضر"
            if (isset($data['status']) && $data['status'] === 'present') {
                $attendance->delete();
                $attendance->status = 'present';
                $attendance->absence_type = null;
                $attendance->staff_leave_id = null;
                
                return $attendance; // نرجعه ككائن للفرونت إند
            }

            $newStatus = $data['status'] ?? $attendance->status;

            // --- 💡 التعديل هنا: تنظيف البيانات عند التعديل ---
            $absenceType = ($newStatus === 'on_leave') ? null : ($data['absence_type'] ?? $attendance->absence_type);
            $staffLeaveId = ($newStatus === 'on_leave') ? ($data['staff_leave_id'] ?? $attendance->staff_leave_id) : null;
            // -----------------------------------------------------------

            // 1. تعديل الأب للغياب أو الإجازة
            $attendance->update([
                'status'          => $newStatus,
                'absence_type'    => $absenceType,
                'staff_leave_id'  => $staffLeaveId,
                'attendance_date' => $data['attendance_date'] ?? $attendance->attendance_date,
            ]);

            // 2. 🧹 التصفية (Wipe): مسح الحصص الفائتة القديمة
            TeacherPeriodAttendance::where('staff_attendance_id', $attendance->id)->delete();

            // 3. 🏗️ إعادة البناء (Replace): بناء الحصص الجديدة
            $this->buildMissedPeriods($attendance, $data);

            return $attendance->load('periodAttendances.scheduleEntry.classRoom', 'periodAttendances.scheduleEntry.gradeSubject');
        });
    }

    /**
     * 3. حذف سجل الغياب (التحويل إلى حاضر)
     */
    public function deleteAttendance(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $attendance = StaffAttendance::findOrFail($id);
            $attendance->delete(); // الأبناء سيحذفون تلقائياً بفضل Cascade
            return true;
        });
    }

    /**
     * 4. جلب تفاصيل غياب موظف
     */
    public function getAttendanceById(int $id)
    {
        $attenReco=StaffAttendance::where('id', $id)->first();
        $allabsent=StaffAttendance::where('staff_id', $attenReco->staff_id)->count();
        $unexcusedAbsent=StaffAttendance::where('id', $id)->where('absence_type','unexcused')->count();

        $records= StaffAttendance::with([
            'periodAttendances.scheduleEntry.classRoom',
            'periodAttendances.scheduleEntry.gradeSubject'
        ])->findOrFail($id); 
        return [
            'AllAbsent'=>$allabsent,
            'unexcutedAbsent'=>$unexcusedAbsent,
            'record'=>$records,
        ];
    }

    /**
     * 🛠️ دالة مساعدة: تبني الحصص الفائتة بناءً على حالة المعلم
     */
    private function buildMissedPeriods(StaffAttendance $attendance, array $data): void
    {
        $status = $data['status'] ?? $attendance->status;
        $insertData = []; 

        if (in_array($status, ['absent', 'on_leave'])) {
            $dayName = strtolower(Carbon::parse($attendance->attendance_date)->englishDayOfWeek);
            
            // جلب الحصص المخصصة للمعلم في هذا اليوم من الجدول النشط
            $missedPeriods = ScheduleEntry::whereHas('schedule', function($q) {
                    $q->where('status', 'active');
                })
                ->where('teacher_id', $attendance->staff_id)
                ->where('day', $dayName)
                ->get();

            foreach ($missedPeriods as $period) {
                $insertData[] = [
                    'staff_attendance_id' => $attendance->id,
                    'schedule_entry_id'   => $period->id,
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ];
            }
        } elseif ($status === 'partial_absence' && !empty($data['missing_periods'])) {
            foreach ($data['missing_periods'] as $periodId) {
                $insertData[] = [
                    'staff_attendance_id' => $attendance->id,
                    'schedule_entry_id'   => $periodId,
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ];
            }
        }

        if (!empty($insertData)) {
            TeacherPeriodAttendance::insert($insertData);
        }
    }
    public function getStaffAttendance(
    int $staffId,
    ?string $fromDate = null,
    ?string $toDate = null
) {
    $query = StaffAttendance::with([
        'periodAttendances.scheduleEntry.classRoom',
        'periodAttendances.scheduleEntry.gradeSubject',
        'leave'
    ])
    ->where('staff_id', $staffId);

    if ($fromDate) {
        $query->whereDate('attendance_date', '>=', $fromDate);
    }

    if ($toDate) {
        $query->whereDate('attendance_date', '<=', $toDate);
    }

    return $query
        ->orderByDesc('attendance_date')
        ->get();
}
}
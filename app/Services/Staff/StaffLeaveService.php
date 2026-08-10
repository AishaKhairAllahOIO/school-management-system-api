<?php
namespace App\Services\Staff;

use App\Models\StaffLeave;
use App\Models\StaffLeaveType;
use App\Models\StaffAttendance;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class StaffLeaveService
{
    /**
     * 1. إنشاء طلب إجازة مع كافة التحققات البرمجية والمالية
     */
    public function createLeave(array $data): StaffLeave
    {
        return DB::transaction(function () use ($data) {
            $staffId       = $data['staff_id'];
            $leaveTypeId   = $data['leave_type_id'];
            $academicYearId = $data['academic_year_id'];
            $startDate     = Carbon::parse($data['start_date']);
            $endDate       = Carbon::parse($data['end_date']);

            // حساب عدد الأيام الكلية للإجازة
            $daysCount = $startDate->diffInDays($endDate) + 1;

            // 🛡️ التحقق 1: عدم تداخل التواريخ مع إجازة أخرى لنفس الموظف
            $isOverlapping = StaffLeave::where('staff_id', $staffId)
                ->where(function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('start_date', [$startDate, $endDate])
                          ->orWhereBetween('end_date', [$startDate, $endDate])
                          ->orWhere(function ($q) use ($startDate, $endDate) {
                              $q->where('start_date', '<=', $startDate)
                                ->where('end_date', '>=', $endDate);
                          });
                })->exists();

            if ($isOverlapping) {
                throw new Exception('تاريخ الإجازة المطلوب يتداخل مع فترة إجازة أخرى مسجلة مسبقاً لهذا الموظف.');
            }

            // 🛡️ التحقق 2: فحص رصيد الإجازات المتبقي للموظف في السنة الأكاديمية
            $leaveType = StaffLeaveType::findOrFail($leaveTypeId);
            $maxAllowedDays = $leaveType->max_days_per_academic_year;

            $usedDays = StaffLeave::where('staff_id', $staffId)
                ->where('leave_type_id', $leaveTypeId)
                ->where('academic_year_id', $academicYearId)
                ->sum('days_count');

            if (($usedDays + $daysCount) > $maxAllowedDays) {
                throw new Exception("رصيد الإجازات لا يكفي. الحد الأقصى المسموح لنوع هذه الإجازة هو {$maxAllowedDays} يوماً، وقد استنفذ الموظف {$usedDays} يوماً.");
            }

            // حفظ طلب الإجازة
            $leave = StaffLeave::create([
                'staff_id'         => $staffId,
                'leave_type_id'    => $leaveTypeId,
                'academic_year_id' => $academicYearId,
                'start_date'       => $startDate->toDateString(),
                'end_date'         => $endDate->toDateString(),
                'days_count'       => $daysCount,
            ]);

            // 💡 ربط الإجازة تلقائياً بجدول حضور الموظف (StaffAttendance) للأيام الواقعة في الفترة
            $currentDate = $startDate->copy();
            while ($currentDate->lte($endDate)) {
                StaffAttendance::updateOrCreate(
                    [
                        'staff_id'        => $staffId,
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

            return $leave;
        });
    }

    /**
     * 2. تعديل إجازة
     */
    public function updateLeave(int $id, array $data): StaffLeave
    {
        return DB::transaction(function () use ($id, $data) {
            $leave = StaffLeave::findOrFail($id);

            // حذف تأثير الإجازة القديمة من جدول الحضور تمهيداً لإعادة جدولتها
            StaffAttendance::where('staff_leave_id', $leave->id)->update([
                'status'         => 'present',
                'staff_leave_id' => null,
            ]);

            $startDate = Carbon::parse($data['start_date'] ?? $leave->start_date);
            $endDate   = Carbon::parse($data['end_date'] ?? $leave->end_date);
            $daysCount = $startDate->diffInDays($endDate) + 1;

            $leave->update([
                'start_date' => $startDate->toDateString(),
                'end_date'   => $endDate->toDateString(),
                'days_count' => $daysCount,
            ]);

            // إعادة إسقاط أيام الإجازة المعدلة على جدول الحضور
            $currentDate = $startDate->copy();
            while ($currentDate->lte($endDate)) {
                StaffAttendance::updateOrCreate(
                    [
                        'staff_id'        => $leave->staff_id,
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

            return $leave;
        });
    }
    public function getStaffLeaves(int $staffId)
    {
        return StaffLeave::where('staff_id', $staffId)
            ->with(['leaveType', 'academicYear'])
            ->latest()
            ->get();
    }
    public function getStaffLeaveById(int $id): StaffLeave
    {
        return StaffLeave::with(['leaveType', 'academicYear'])->findOrFail($id);
    }

    /**
     * 3. حذف إجازة
     */
    public function deleteLeave(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $leave = StaffLeave::findOrFail($id);

            // تحرير أيام الحضور المرتبطة بهذه الإجازة
            StaffAttendance::where('staff_leave_id', $leave->id)->update([
                'status'         => 'present',
                'staff_leave_id' => null,
            ]);

            $leave->delete();
            return true;
        });
    }
}
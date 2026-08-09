<?php  
// قم ببناء سيرفيس كامل لتسجيل ركوردات غياب للطلاب مع ارسال اشعار لاهل كل طالب عند الغياب واماكانية تعديل وحذف وعرض سجلات غياب طالب معين وعر     سجل غياب شعبة كاملة بتاريخ معين
namespace App\Services\Student;
use App\Models\StudentAttendance;
use App\Models\StudentAttendanceSetting;
use App\Events\BulkAttendanceSaved;

use Illuminate\Support\Facades\DB;
use Exception;
use App\Models\Enrollment;
use App\Models\AcademicSetting;
class  StudentAttendanceService
{
    private function getAttendanceSummary(int $enrollmentId, int $semesterId): array
    {
        $setting = StudentAttendanceSetting::where('semester_id', $semesterId)->first();
        $allowedAbsenceDays = $setting ? (int) floor($setting->working_days * (1 - $setting->required_attendance_percentage / 100)) : 0;

        $unexcused = StudentAttendance::where('enrollment_id', $enrollmentId)
            ->where('semester_id', $semesterId)
            ->where('status', 'absent')
            ->where('absence_type', 'unexcused')
            ->count();

        $remaining = max(0, $allowedAbsenceDays - $unexcused);

        return [
            'allowed_absence_days'   => $allowedAbsenceDays,
            'total_unexcused_absent' => $unexcused,
            'remaining_absence_days' => $remaining,
        ];
    }

    public function storeBulkAttendance(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $semesterId = $data['semester_id'];
            $classroomId = $data['class_room_id'];
            $attendanceDate = $data['attendance_date'];

            $savedAttendances = [];

            foreach ($data['attendances'] as $studentData) {
                $enrollmentId = $studentData['enrollment_id'];
                $status = $studentData['status'];
                $absenceType = $status === 'present' ? null : ($studentData['absence_type'] ?? 'unexcused');
                $notes = $studentData['notes'] ?? null;

                // حفظ أو تحديث السجل بفضل الـ Unique Constraint في قاعدة البيانات
                $attendance = StudentAttendance::updateOrCreate(
                    [
                        'enrollment_id' => $enrollmentId,
                        'attendance_date' => $attendanceDate,
                    ],
                    [
                        'semester_id' => $semesterId,
                        'class_room_id' => $classroomId, // Snapshot تاريخي لحماية الهيكل
                        'status' => $status,
                        'absence_type' => $absenceType,
                    ]
                );

                $savedAttendances[] = $attendance->toArray();
            }

            event(new BulkAttendanceSaved($savedAttendances, $semesterId));

            $enrollmentIds = collect($savedAttendances)->pluck('enrollment_id')->toArray();

            $setting = StudentAttendanceSetting::where('semester_id', $semesterId)->first();
            $allowedAbsenceDays = $setting ? (int) floor($setting->working_days * (1 - $setting->required_attendance_percentage / 100)) : 0;

            $enrollmentsWithCount = Enrollment::whereIn('id', $enrollmentIds)
                ->withCount(['attendances as unexcused_count' => function ($query) use ($semesterId) {
                    $query->where('semester_id', $semesterId)
                          ->where('status', 'absent')
                          ->where('absence_type', 'unexcused');
                }])
                ->get()
                ->keyBy('id');

            $responseList = [];
            foreach ($savedAttendances as $saved) {
                $enrollmentId = $saved['enrollment_id'];
                $unexcused = $enrollmentsWithCount[$enrollmentId]->unexcused_count ?? 0;
                $remaining = max(0, $allowedAbsenceDays - $unexcused);

                $responseList[] = [
                    'id' => $saved['id'],
                    'enrollment_id' => $enrollmentId,
                    'attendance_date' => $saved['attendance_date'],
                    'status' => $saved['status'],
                    'absence_type' => $saved['absence_type'],
                    'allowed_absence_days' => $allowedAbsenceDays,
                    'total_unexcused_absent' => $unexcused,
                    'remaining_absence_days' => $remaining,
                ];
            }

            return $responseList;
        });
    }

    /**
     * 2. الفلترة المتقدمة البديلة لعرض طلاب شعبة معينة
     * تتيح جلب قائمة الطلاب مفلترة بـ: الشعبة، حالة الحضور، نوع الغياب، والتاريخ، مع حساب الغيابات المتبقية
     */
    public function filterStudentsAttendance(array $filters)
    {
      $classroomId = $filters['class_room_id'];
        $attendanceDate = $filters['attendance_date'] ?? now()->toDateString();
        $semesterId = $filters['semester_id'] ?? null;
        $status = $filters['status'] ?? null;
        $absenceType = $filters['absence_type'] ?? null;

        if (!$semesterId) {
            $semesterId = AcademicSetting::first()?->current_semester_id;
        }

        $setting = StudentAttendanceSetting::where('semester_id', $semesterId)->first();
        $allowedAbsenceDays = $setting ? (int) floor($setting->working_days * (1 - $setting->required_attendance_percentage / 100)) : 0;

        $query = Enrollment::where('class_room_id', $classroomId)
            ->whereIn('enrollment_status', ['enrolled', 'confirmed'])
            ->with([
                'student.user:id,first_name,father_name,last_name',
                // 🛡️ الحل هنا: استخدام whereDate لتجاهل أجزاء الوقت ومقارنة التاريخ الصافي فقط
                'attendances' => function ($q) use ($attendanceDate, $semesterId) {
                    $q->whereDate('attendance_date', $attendanceDate)
                      ->where('semester_id', $semesterId);
                }
            ]);

        // جلب تراكم الغيابات غير المبررة للطلاب لتجنب N+1 Queries
        $query->withCount(['attendances as unexcused_count' => function ($q) use ($semesterId) {
            $q->where('semester_id', $semesterId)
              ->where('status', 'absent')
              ->where('absence_type', 'unexcused');
        }]);

        // تصفية السجلات بناءً على الفلاتر المدخلة
        if ($status || $absenceType) {
            $query->whereHas('attendances', function ($q) use ($attendanceDate, $semesterId, $status, $absenceType) {
                // 🛡️ استخدام whereDate هنا أيضاً
                $q->whereDate('attendance_date', $attendanceDate)
                  ->where('semester_id', $semesterId);
                if ($status) {
                    $q->where('status', $status);
                }
                if ($absenceType) {
                    $q->where('absence_type', $absenceType);
                }
            });
        }
        //dd($query->toSql(), $query->getBindings(), $attendanceDate, $semesterId);

        $enrollments = $query->paginate($filters['per_page'] ?? 15);

        // بناء الـ Response المنسق للفرونت إند
        $enrollments->getCollection()->transform(function ($enrollment) use ($allowedAbsenceDays) {
            $attendance = StudentAttendance::where('enrollment_id', $enrollment->id)->first();
             
            $unexcused = $enrollment->unexcused_count ?? 0;
            $remaining = max(0, $allowedAbsenceDays - $unexcused);
            $user = $enrollment->student?->user;

            return [
                'enrollment_id'          => $enrollment->id,
                'student_id'             => $enrollment->student_id,
                'full_name'              => $user ? trim($user->first_name . ' ' . $user->father_name . ' ' . $user->last_name) : 'غير متوفر',
                'allowed_absence_days'   => $allowedAbsenceDays,
                'total_unexcused_absent' => $unexcused,
                'remaining_absence_days' => $remaining,
                'attendance'             =>  [
                    'id'              => $attendance->id,
                    'status'          => $attendance->status,
                    'absence_type'    => $attendance->absence_type,
                    'attendance_date' => $attendance->attendance_date,
                ],
            ];
        });

        return $enrollments;
    }
    public function getRecord(int $id): array
    {
        $attendance = StudentAttendance::with('enrollment.student.user')->findOrFail($id);
        $summary    = $this->getAttendanceSummary($attendance->enrollment_id, $attendance->semester_id);

        return [
            'record'             => $attendance,
            'attendance_summary' => $summary,
        ];
    }

    /**
     * 3. تعديل سجل حضور/غياب فردي (كل الحقول الممكنة)
     */
    public function updateSingleAttendance(int $id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $attendance = StudentAttendance::findOrFail($id);

            $attendance->update([
                'status' => $data['status'] ?? $attendance->status,
                // إذا تحولت الحالة إلى حاضر، يتم شطب نوع الغياب تلقائياً للحفاظ على سلامة المنطق الأكاديمي
                'absence_type' => (isset($data['status']) && $data['status'] === 'present') ? null : ($data['absence_type'] ?? $attendance->absence_type),
                'attendance_date' => $data['attendance_date'] ?? $attendance->attendance_date,
            ]);

            $attendance->load('enrollment.student.user');

            // 💡 الحسبة السريعة لبيانات الطالب بعد التعديل
            $semesterId = $attendance->semester_id;
            $enrollmentId = $attendance->enrollment_id;

            $setting = StudentAttendanceSetting::where('semester_id', $semesterId)->first();
            $allowedAbsenceDays = $setting ? (int) floor($setting->working_days * (1 - $setting->required_attendance_percentage / 100)) : 0;

            $unexcused = StudentAttendance::where('enrollment_id', $enrollmentId)
                ->where('semester_id', $semesterId)
                ->where('status', 'absent')
                ->where('absence_type', 'unexcused')
                ->count();

            $remaining = max(0, $allowedAbsenceDays - $unexcused);

            return [
                'record' => $attendance,
                'attendance_summary' => [
                    'allowed_absence_days' => $allowedAbsenceDays,
                    'total_unexcused_absent' => $unexcused,
                    'remaining_absence_days' => $remaining,
                ]
            ];
        });
    }

    /**
     * 4. حذف سجل حضور/غياب فردي لطالب محدد
     */
    public function deleteSingleAttendance(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $attendance = StudentAttendance::findOrFail($id);
            $attendance->delete();
            return true;
        });
    }

}
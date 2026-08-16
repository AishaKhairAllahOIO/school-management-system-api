<?php 

namespace App\Services\Student;

use App\Models\StudentAttendance;
use App\Models\StudentAttendanceSetting;
use App\Events\BulkAttendanceSaved;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Models\Enrollment;
use App\Models\AcademicSetting;

class StudentAttendanceService
{
    /**
     * دالة مساعدة لحساب ملخص غياب الطالب.
     * لم نغير اسمها، وهي تقوم بحساب كم يوم غاب الطالب وكم تبقى له.
     */
    private function getAttendanceSummary(int $enrollmentId, int $semesterId): array
    {
        $setting = StudentAttendanceSetting::where('semester_id', $semesterId)->first();
        $allowedAbsenceDays = $setting ? (int) floor($setting->working_days * (1 - $setting->required_attendance_percentage / 100)) : 0;

        // نحسب فقط سجلات الغياب غير المبرر
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

    /**
     * 1. تخزين حضور وغياب مجموعة من الطلاب دفعة واحدة
     * تم التعديل لتطبيق "الحضور بالاستثناء" وتوفير مساحة قاعدة البيانات
     */
    public function storeBulkAttendance(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $semesterId = $data['semester_id'];
            $classroomId = $data['class_room_id'];
            $attendanceDate = $data['attendance_date'];

            $savedAttendances = [];
            $deletedAttendancesEnrollmentIds = []; // تتبع الطلاب الذين تم تحويلهم من غائب إلى حاضر

            foreach ($data['attendances'] as $studentData) {
                $enrollmentId = $studentData['enrollment_id'];
                $status = $studentData['status'];

                if ($status === 'present') {
                    // إذا كان الطالب حاضراً، نحذف أي سجل غياب له في هذا اليوم (Soft Delete)
                    StudentAttendance::where('enrollment_id', $enrollmentId)
                        ->where('attendance_date', $attendanceDate)
                        ->delete(); 
                    
                    $deletedAttendancesEnrollmentIds[] = $enrollmentId;
                } else {
                    // إذا كان غائباً، نقوم بالإنشاء أو التحديث
                    $absenceType = $studentData['absence_type'] ?? 'unexcused';
                    
                    $attendance = StudentAttendance::updateOrCreate(
                        [
                            'enrollment_id' => $enrollmentId,
                            'attendance_date' => $attendanceDate,
                        ],
                        [
                            'semester_id' => $semesterId,
                            'class_room_id' => $classroomId, 
                            'status' => 'absent', // دائماً absent
                            'absence_type' => $absenceType,
                        ]
                    );

                    $savedAttendances[] = $attendance->toArray();
                }
            }

            // إرسال حدث (Event) فقط للطلاب الذين غابوا لتنبيه أولياء الأمور
            if (count($savedAttendances) > 0) {
                event(new BulkAttendanceSaved($savedAttendances, $semesterId));
            }

            // --- بناء استجابة موحدة للواجهة الأمامية (Frontend) ---
            $allAffectedEnrollmentIds = array_merge(
                collect($savedAttendances)->pluck('enrollment_id')->toArray(),
                $deletedAttendancesEnrollmentIds
            );

            $setting = StudentAttendanceSetting::where('semester_id', $semesterId)->first();
            $allowedAbsenceDays = $setting ? $setting->allowed_absence_days : 0;
            // حساب التراكمي لجميع الطلاب المعدلين بخطوة واحدة لتجنب N+1
            $enrollmentsWithCount = Enrollment::whereIn('id', $allAffectedEnrollmentIds)
                ->withCount(['attendances as unexcused_count' => function ($query) use ($semesterId) {
                    $query->where('semester_id', $semesterId)
                          ->where('status', 'absent')
                          ->where('absence_type', 'unexcused');
                }])
                ->get()
                ->keyBy('id');

            $responseList = [];
            foreach ($data['attendances'] as $studentData) {
                $enrollmentId = $studentData['enrollment_id'];
                $unexcused = $enrollmentsWithCount[$enrollmentId]->unexcused_count ?? 0;
                $remaining = max(0, $allowedAbsenceDays - $unexcused);

                $responseList[] = [
                    'enrollment_id' => $enrollmentId,
                    'attendance_date' => $attendanceDate,
                    'status' => $studentData['status'], // نعيد الحالة كما أرسلها الفرونت إند
                    'absence_type' => $studentData['status'] === 'present' ? null : ($studentData['absence_type'] ?? 'unexcused'),
                    'allowed_absence_days' => $allowedAbsenceDays,
                    'total_unexcused_absent' => $unexcused,
                    'remaining_absence_days' => $remaining,
                ];
            }

            return $responseList;
        });
    }

    /**
     * 2. الفلترة المتقدمة لعرض طلاب شعبة معينة مع حالة حضورهم
     * تم التعديل لافتراض أن الطالب "حاضر" إذا لم يمتلك سجل غياب
     */
public function filterStudentsAttendance(array $filters)
    {
        // 1. استخراج الفلاتر الأساسية للحضور
        $attendanceDate = $filters['attendance_date'] ?? now()->toDateString();
        $semesterId = $filters['semester_id'] ?? AcademicSetting::first()?->current_semester_id;
        $statusFilter = $filters['status'] ?? null;
        $absenceTypeFilter = $filters['absence_type'] ?? null;

        // 2. استخراج الفلاتر العامة
        $searchName = $filters['search_name'] ?? null;
        $gradeId = $filters['grade_id'] ?? null;
        $classroomId = $filters['class_room_id'] ?? null;

        $setting = StudentAttendanceSetting::where('semester_id', $semesterId)->first();
        $allowedAbsenceDays = $setting ? $setting->allowed_absence_days : 0;

        // 3. بناء الاستعلام الأساسي
        $query = Enrollment::whereIn('enrollment_status', ['enrolled', 'confirmed']);

        if ($classroomId) {
            $query->where('class_room_id', $classroomId);
        }

        if ($gradeId) {
            $query->whereHas('classRoom', function ($q) use ($gradeId) {
                $q->where('grade_level_id', $gradeId);
            });
        }

        if ($searchName) {
            $query->whereHas('student.user', function ($q) use ($searchName) {
                $q->where('first_name', 'LIKE', "%{$searchName}%")
                  ->orWhere('last_name', 'LIKE', "%{$searchName}%")
                  ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$searchName}%"]);
            });
        }

        // 4. دمج العلاقات
        $query->with([
            'student.user:id,first_name,father_name,last_name',
            'attendances' => function ($q) use ($attendanceDate, $semesterId) {
                $q->whereDate('attendance_date', $attendanceDate)
                  ->where('semester_id', $semesterId);
            }
        ]);

        // 5. حساب الغيابات التراكمية غير المبررة
        $query->withCount(['attendances as unexcused_count' => function ($q) use ($semesterId) {
            $q->where('semester_id', $semesterId)
              ->whereIn('status', ['absent', 'partial_absence']) // 💡 تأكيد أنها غيابات فقط
              ->where('absence_type', 'unexcused');
        }]);

        // 6. تطبيق فلاتر حالة الحضور (تصحيح المنطق)
        if ($statusFilter === 'absent' || $absenceTypeFilter) {
            $query->whereHas('attendances', function ($q) use ($attendanceDate, $semesterId, $statusFilter, $absenceTypeFilter) {
                $q->whereDate('attendance_date', $attendanceDate)
                  ->where('semester_id', $semesterId)
                  ->where('status', 'absent'); // 💡 يجب أن يكون السجل الفعلي غياباً
                
                if ($absenceTypeFilter) {
                    $q->where('absence_type', $absenceTypeFilter);
                }
            });
        } elseif ($statusFilter === 'present') {
            // 💡 التعديل الذكي: الطالب حاضر إذا (لم يمتلك سجل غياب) في هذا اليوم
            // هذا يشمل من ليس له سجل أبداً، ومن له سجل مكتوب فيه present
            $query->whereDoesntHave('attendances', function ($q) use ($attendanceDate, $semesterId) {
                $q->whereDate('attendance_date', $attendanceDate)
                  ->where('semester_id', $semesterId)
                  ->where('status', '!=', 'present'); // نستبعد فقط من لديه سجل غياب فعلي
            });
        }

        // 7. تنفيذ الاستعلام
        $enrollments = $query->paginate($filters['per_page'] ?? 15);

        // 8. تشكيل الاستجابة (التصحيح הגراحي للـ Contradiction)
        $enrollments->getCollection()->transform(function ($enrollment) use ($allowedAbsenceDays, $attendanceDate) {
            $attendanceRecord = $enrollment->attendances->first();
             
            $unexcused = $enrollment->unexcused_count ?? 0;
            $remaining = max(0, $allowedAbsenceDays - $unexcused);
            $user = $enrollment->student?->user;

            // 💡 التعديل الجوهري هنا: نقرأ الحالة من الداتابيز إن وجدت، وإلا نفترضه حاضراً
            $computedStatus = $attendanceRecord ? $attendanceRecord->status : 'present';
            
            // 💡 إذا كان حاضراً، فنوع الغياب حتماً null
            $computedAbsenceType = ($computedStatus === 'present') ? null : ($attendanceRecord ? $attendanceRecord->absence_type : null);
            
            $recordId = $attendanceRecord ? $attendanceRecord->id : null;

            return [
                'enrollment_id'          => $enrollment->id,
                'student_id'             => $enrollment->student_id,
                'full_name'              => $user ? trim($user->first_name . ' ' . $user->father_name . ' ' . $user->last_name) : 'غير متوفر',
                'allowed_absence_days'   => $allowedAbsenceDays,
                'total_unexcused_absent' => $unexcused,
                'remaining_absence_days' => $remaining,
                'attendance'             =>  [
                    'id'              => $recordId,
                    'status'          => $computedStatus,
                    'absence_type'    => $computedAbsenceType,
                    'attendance_date' => $attendanceDate,
                ],
            ];
        });

        return $enrollments;
    }

    /**
     * 3. جلب سجل غياب محدد
     * هذه الدالة تعمل على السجلات الموجودة مسبقاً (والتي هي غيابات بالضرورة)
     */
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
     * 4. تعديل سجل غياب فردي
     * تم التعديل: إذا تم تعديل الحالة إلى "حاضر"، سيتم حذف السجل.
     */
    public function updateSingleAttendance(int $id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $attendance = StudentAttendance::findOrFail($id);
            $semesterId = $attendance->semester_id;
            $enrollmentId = $attendance->enrollment_id;

            // إذا أرسل المستخدم أن الطالب "حاضر"، نقوم بحذف سجل الغياب
            if (isset($data['status']) && $data['status'] === 'present') {
                $attendance->delete();
                $computedStatus = 'present';
                $computedAbsenceType = null;
            } else {
                // تحديث بيانات الغياب
                $attendance->update([
                    'absence_type' => $data['absence_type'] ?? $attendance->absence_type,
                    'attendance_date' => $data['attendance_date'] ?? $attendance->attendance_date,
                ]);
                $computedStatus = 'absent';
                $computedAbsenceType = $attendance->absence_type;
            }

            // الحسبة السريعة لبيانات الطالب بعد التعديل
            $summary = $this->getAttendanceSummary($enrollmentId, $semesterId);

            return [
                'record'             => $attendance,
                'attendance_summary' => $summary
            ];
        });
    }

    /**
     * 5. حذف سجل حضور/غياب فردي
     * حذف الغياب يعني أن الطالب أصبح حاضراً، لا تغيير في المنطق الأساسي هنا.
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
<?php

namespace App\Services\Report;

use App\Models\Enrollment;
use App\Models\ReportCard;
use App\Models\StudentAttendance;
use App\Models\StudentAttendanceSetting;
use App\Models\GradeSubject;
use Illuminate\Support\Facades\DB;

class ReportCardGenerationService
{
    /**
     * توليد الجلاء لطالب واحد (قيد واحد)
     * * @param Enrollment $enrollment
     * @param int $semesterId
     * @param int $maxAllowedNonFailingFailures أقصى عدد مسموح للرسوب في المواد غير المرسبة (افتراضياً 2)
     */
    public function generateForEnrollment(Enrollment $enrollment, $semesterId, int $maxAllowedNonFailingFailures)
    {
        return DB::transaction(function () use ($enrollment, $semesterId, $maxAllowedNonFailingFailures) {
            $failureReasons = [];
            $finalResult = 'passed';
            $attendanceStatus = 'passed';

            // ==========================================
            // 🛑 الفلتر الأول: قانون الغياب للفصل المحدّد
            // ==========================================
            $attendanceSetting = StudentAttendanceSetting::where('semester_id', $semesterId)->first();
            $allowedAbsences = $attendanceSetting ? $attendanceSetting->allowed_absence_days : 0;

            $unexcusedAbsences = StudentAttendance::where('enrollment_id', $enrollment->id)
                ->where('absence_type', 'unexcused')
                ->count();

            if ($unexcusedAbsences > $allowedAbsences) {
                $attendanceStatus = 'failed';
                $finalResult = 'failed';
                $failureReasons[] = "تجاوز حد الغياب المسموح ({$unexcusedAbsences} أيام)";
            }

            // ==========================================
            // 🛑 الفلتر الثاني: العلامات والمواد (المرسبة وغير المرسبة)
            // ==========================================
            // جلب الصف مباشرة من Enrollment لأمان أسرع
            $gradeLevelId = $enrollment->grade_level_id ?? $enrollment->classRoom?->grade_level_id;

            // جلب مواد الفصل الدراسي الحالي حصراً وبدون تكرار المواد
            $gradeSubjects = GradeSubject::with(['subject', 'assessmentComponents.studentMarks' => function ($query) use ($enrollment) {
                $query->where('enrollment_id', $enrollment->id);
            }])
            ->where('grade_level_id', $gradeLevelId)
            ->where('semester_id', $semesterId)
            ->get()
            ->unique('subject_id');

            $totalMarks = 0;
            $maxTotalMarks = 0;
            $sumOfPassingMarks = 0;

            // 💡 عدّاد ومصفوفة للمواد غير المرسبة التي رسب بها الطالب
            $failedNonFailingCount = 0;
            $failedNonFailingNames = [];

            $detailsData = [];

            foreach ($gradeSubjects as $gs) {
                $subjectTotal = 0;
                $evaluationsSummary = [];

                // تجميع علامات الطالب في المادة (شفهي، مذاكرة، نهائي...)
                foreach ($gs->assessmentComponents as $component) {
                    $markRecord = $component->studentMarks->where('enrollment_id', $enrollment->id)->first();
                    $mark = $markRecord ? $markRecord->mark : 0;
                    $maxMark = $component->max_mark;
                    
                    $subjectTotal += $mark;
                    
                    $evaluationsSummary[$component->type] = [
                        'name'     => $component->name,
                        'mark'     => $mark,
                        'max_mark' => $maxMark
                    ];
                }

                $totalMarks += $subjectTotal;
                $maxTotalMarks += $gs->max_mark;
                $sumOfPassingMarks += $gs->passing_mark;

                $subjectStatus = ($subjectTotal >= $gs->passing_mark) ? 'passed' : 'failed';
                $subjectName = $gs->subject->subject_name ?? 'مادة دراسية';

                // 🔴 الحالة أ: الرسوب في مادة مرسبة أساسية (is_failing_subject == true)
                if ($subjectStatus === 'failed' && $gs->is_failing_subject) {
                    $finalResult = 'failed';
                    $failureReasons[] = "الرسوب في مادة مرسبة: {$subjectName}";
                }

                // 🟡 الحالة ب: الرسوب في مادة غير مرسبة (is_failing_subject == false)
                if ($subjectStatus === 'failed' && !$gs->is_failing_subject) {
                    $failedNonFailingCount++;
                    $failedNonFailingNames[] = $subjectName;
                }

                // تجهيز تفاصيل المادة للحفظ (Snapshot)
                $detailsData[] = [
                    'grade_subject_id'    => $gs->id,
                    'evaluations_summary' => json_encode($evaluationsSummary, JSON_UNESCAPED_UNICODE),
                    'subject_total'       => $subjectTotal,
                    'passing_mark'        => $gs->passing_mark,
                    'max_mark'            => $gs->max_mark,
                    'is_failing_subject'  => $gs->is_failing_subject,
                    'status'              => $subjectStatus,
                ];
            }

            // ==========================================
            // 🛑 الفلتر الثالث: فحص عدد المواد غير المرسبة التي رسب بها
            // ==========================================
            if ($failedNonFailingCount > $maxAllowedNonFailingFailures) {
                $finalResult = 'failed';
                $namesStr = implode('، ', $failedNonFailingNames);
                $failureReasons[] = "الرسوب في ({$failedNonFailingCount}) مواد غير مرسبة ({$namesStr})، والحد المسموح به هو ({$maxAllowedNonFailingFailures})";
            }

            // ==========================================
            // 🛑 الفلتر الرابع: المجموع الكلي الأدنى للنجاح
            // ==========================================
            if ($totalMarks < $sumOfPassingMarks) {
                $finalResult = 'failed';
                $failureReasons[] = "المجموع الكلي ({$totalMarks}) أقل من الحد الأدنى للنجاح ({$sumOfPassingMarks})";
            }

            // تنظيف مصفوفة الأسباب من التكرار
            $failureReasons = array_values(array_unique($failureReasons));

            // ==========================================
            // 💾 المرحلة الرابعة: حفظ الجلاء (Snapshot)
            // ==========================================
            $reportCard = ReportCard::updateOrCreate(
                [
                    'enrollment_id' => $enrollment->id,
                    'semester_id'   => $semesterId,
                ],
                [
                    'academic_year_id'  => $enrollment->academic_year_id,
                    'total_marks'       => $totalMarks,
                    'max_total_marks'   => $maxTotalMarks,
                    'attendance_status' => $attendanceStatus,
                    'final_result'      => $finalResult,
                    'failure_reasons'   => count($failureReasons) > 0 ? $failureReasons : null,
                    'is_published'      => false,
                ]
            );

            // مسح التفاصيل القديمة وحفظ الجديدة
            $reportCard->details()->delete();
            $reportCard->details()->createMany($detailsData);

            // ==========================================
            // 🎓 المرحلة الخامسة: تطبيق سياسة الترفيع
            // ==========================================
            $this->applyPromotionLogic($enrollment, $finalResult);

            return $reportCard;
        });
    }

    /**
     * منطق الترفيع وتغيير حالة التسجيل
     */
    protected function applyPromotionLogic(Enrollment $enrollment, string $finalResult)
    {
        if ($finalResult === 'failed') {
            $enrollment->update(['academic_result' => 'failed']);
            return;
        }

        $currentGrade = $enrollment->gradeLevel ?? $enrollment->classRoom?->gradeLevel;

        if ($currentGrade && $currentGrade->is_graduation_grade) {
            $enrollment->update(['academic_result' => 'passed']);
        } else {
            $enrollment->update(['academic_result' => 'passed']);
        }
    }

    /**
     * جلب العشرة الأوائل حسب الصف (Grade Level) أو الشعبة (Class Room)
     */
    public function getTopStudentsByGrade($semesterId, $gradeLevelId = null, int $limit = 10)
    {
        $query = ReportCard::with([
                'enrollment.student.user', 
                'enrollment.gradeLevel', 
                'enrollment.classRoom'
            ])
            ->where('semester_id', $semesterId)
            ->where('final_result', 'passed'); // فقط الناجحون هم من ينافسون على المراكز الأولى

        // إذا أردنا صفاً معيناً (Grade Level)
        if ($gradeLevelId) {
            $query->whereHas('enrollment', function ($q) use ($gradeLevelId) {
                $q->where('grade_level_id', $gradeLevelId);
            });
        }

        // جلب الطلاب مرتبين تنازلياً حسب المجموع الكلي (total_marks)
        return $query->orderByDesc('total_marks')
            ->limit($limit)
            ->get();
    }
}
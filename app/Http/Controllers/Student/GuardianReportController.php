<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ReportCard;
use App\Http\Resources\ReportCardResource;
use App\ApiResource;
use App\Services\Report\ReportCardGenerationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Throwable;
class GuardianReportController extends Controller
{
    use ApiResource;

    /**
     * 👨‍👩‍👧 عرض جلاء أحد أبناء ولي الأمر بناءً على معرف الولد وفصل الدراسه
     */
    public function showStudentReportCard($studentId, $semesterId): JsonResponse
    {
        $user = Auth::user();

        // 🔒 حماية أمنية سيادية: التأكد أن ولي الأمر يمتلك هذا الطالب حقاً
        // (بافتراض أن لديكِ علاقة guardians مرتبطة بالـ student، أو جدول ربط)
        $isHisChild = $user->guardian && $user->guardian->students()->where('students.id', $studentId)->exists();

        // إذا كان النظام يربط جدول الـ students بجدول الـ users مباشرة لولي الأمر، يمكنكِ تعديل الشرط حسب علاقاتكِ، 
        // لكن هذا التحقق يمنع تماماً أي تسريب للبيانات بين العائلات.
        if (!$isHisChild) {
            return $this->errorResponse('غير مسموح لك بالاطلاع على جلاء هذا الطالب.', 403);
        }

        return $this->fetchReportCard($studentId, $semesterId);
    }

    /**
     * 🎓 عرض الطالب لجلاءه الشخصي مباشرة
     */
    public function showMyReportCard($semesterId): JsonResponse
    {
        $user = Auth::user();

        // التأكد أن المستخدم الحالي هو طالب ولديه سجل في جدول students
        if (!$user->student) {
            return $this->errorResponse('حساب المستخدم الحالي ليس طالباً مسجلاً.', 403);
        }

        $studentId = $user->student->id;

        return $this->fetchReportCard($studentId, $semesterId);
    }

    /**
     * ⚙️ ميثود مركزية مشتركة لجلب الجلاء (منع تكرار الكود - DRY Principle)
     */
    private function fetchReportCard($studentId, $semesterId): JsonResponse
    {
        $reportCard = ReportCard::with(['details.gradeSubject.subject', 'enrollment.student.user'])
            ->whereHas('enrollment', function ($q) use ($studentId) {
                $q->where('student_id', $studentId);
            })
            ->where('semester_id', $semesterId)
            ->where('is_published', true) // 🛑 حماية صارمة ألا يرى النتائج قبل نشرها من الإدارة
            ->first();

        if (!$reportCard) {
            return $this->errorResponse('الجلاء غير متوفر أو لم يتم نشره بعد من قبل إدارة المدرسة.', 404);
        }

        return $this->successResponse(
            new ReportCardResource($reportCard),
            'تم جلب الجلاء بنجاح.'
        );
    }
    public function getTopStudentsForMyChild(ReportCardGenerationService $topStudentsService): JsonResponse
    {
        try {
            $user = auth()->user();
            
            // 💡 1. استخراج قيد الطالب الحالي (أو الطالب التابع لولي الأمر)
            // سنفترض أن المستخدم لديه علاقة student أو سنأخذ أول قيد نشط له
            $student = $user->student ?? $user->students()->first();
            
            if (!$student) {
                return $this->errorResponse('حساب الطالب غير مرتبود، لا يمكن تحديد الصف.', 404);
            }

            $activeEnrollment = $student->enrollments()->latest()->first();

            if (!$activeEnrollment) {
                return $this->errorResponse('لا يوجد قيد دراسي فعال لهذا الطالب.', 404);
            }

            $semesterId = request('semester_id');
            if (!$semesterId) {
                return $this->errorResponse('معرف الفصل الدراسي (semester_id) مطلوب.', 422);
            }

            // 💡 2. جلب grade_level_id الخاص بابن المستخدم تلقائياً دون تدخل بشري
            $gradeLevelId = $activeEnrollment->grade_level_id ?? $activeEnrollment->classRoom?->grade_level_id;

            // 💡 3. استدعاء نفس السيرفس لجلب أوائل هذا الصف فقط!
            $topStudents = $topStudentsService->getTopStudentsByGrade($semesterId, $gradeLevelId, 10);

            return $this->successResponse(
                ReportCardResource::collection($topStudents),
                'تم جلب قائمة العشرة الأوائل لصف ابنك بنجاح.'
            );
        } catch (Throwable $e) {
            return $this->errorResponse('حدث خطأ أثناء جلب قائمة الأوائل.', 500, ['error' => $e->getMessage()]);
        }
    }
}
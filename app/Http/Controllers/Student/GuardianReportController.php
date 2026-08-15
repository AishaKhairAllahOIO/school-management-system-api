<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ReportCard;
use App\Http\Resources\ReportCardResource;
use App\ApiResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

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
}
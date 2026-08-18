<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ReportCard;
use App\Http\Resources\ReportCardResource;
use App\ApiResource;
use App\Http\Resources\Student\TopStudentResource as StudentTopStudentResource;
use App\Http\Resources\TopStudentResource;
use App\Services\Report\ReportCardGenerationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Throwable;
class GuardianReportController extends Controller
{
    use ApiResource;


    public function showStudentReportCard($studentId, $semesterId): JsonResponse
    {
        $user = Auth::user();

        $isHisChild = $user->guardian && $user->guardian->students()->where('students.id', $studentId)->exists();

        if (!$isHisChild) {
            return $this->errorResponse('غير مسموح لك بالاطلاع على جلاء هذا الطالب.', 403);
        }

        return $this->fetchReportCard($studentId, $semesterId);
    }

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

    public function getTopStudentsForMyClass(ReportCardGenerationService $service): JsonResponse
    {
        $user = Auth::user();

        if (!$user->student) {
            return $this->errorResponse(
                'الحساب ليس طالباً',
                403
            );
        }


        $semesterId = request('semester_id');


        $topStudents = $service->getTopStudentsByStudent(
            $user->student->id,
            $semesterId
        );


        return $this->successResponse(
           StudentTopStudentResource ::collection($topStudents),
            'تم جلب أوائل الصف'
        );
    }

    public function getTopStudentsForChild(ReportCardGenerationService $service): JsonResponse
    {

        $user = Auth::user();

        $studentId = request('student_id');
        $semesterId = request('semester_id');


        $isChild = $user->guardian
            ->students()
            ->where('students.id', $studentId)
            ->exists();


        if (!$isChild) {
            return $this->errorResponse(
                'لا يمكنك الوصول لهذا الطالب',
                403
            );
        }


        $topStudents = $service->getTopStudentsByStudent(
            $studentId,
            $semesterId
        );


        return $this->successResponse(
            StudentTopStudentResource::collection($topStudents),
            'تم جلب أوائل صف الطالب'
        );
    }

}
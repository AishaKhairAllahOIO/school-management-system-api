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
            return $this->errorResponse("You are not authorized to view this student's report card.", 403);
        }

        return $this->fetchReportCard($studentId, $semesterId);
    }

    public function showMyReportCard($semesterId): JsonResponse
    {
        $user = Auth::user();

        if (!$user->student) {
            return $this->errorResponse('The current user account is not a registered student.', 403);
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
            return $this->errorResponse('Report card is not available or has not been published yet by the school administration.', 404);
        }

        return $this->successResponse(
            new ReportCardResource($reportCard),
            'Report card retrieved successfully.'
        );
    }

    public function getTopStudentsForMyClass(ReportCardGenerationService $service): JsonResponse
    {
        $user = Auth::user();

        if (!$user->student) {
            return $this->errorResponse(
                'The account is not a student.',
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
           'Class top students retrieved successfully.',
           200
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
               'You are not authorized to access this student.',
                403
            );
        }


        $topStudents = $service->getTopStudentsByStudent(
            $studentId,
            $semesterId
        );


        return $this->successResponse(
            StudentTopStudentResource::collection($topStudents),
           'Student class top students retrieved successfully.',
           200
        );
    }

}

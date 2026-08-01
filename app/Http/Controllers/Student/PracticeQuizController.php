<?php

namespace App\Http\Controllers\Student;

use App\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\Student\SubmitQuizAttemptRequest;
use App\Models\Enrollment;
use App\Services\Quiz\PracticeQuizService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class PracticeQuizController extends Controller
{
    use ApiResource;

    public function __construct(protected PracticeQuizService $quizService) {}

    private function getCurrentEnrollment($user)
    {
        if (!$user->student) return null;
        return Enrollment::where('student_id', $user->student->id)
            ->whereHas('academicYear', fn($q) => $q->where('is_current', true))
            ->with('classRoom')
            ->latest()
            ->first();
    }

    public function getSubjects(Request $request)
    {
        try {
            $enrollment = $this->getCurrentEnrollment($request->user());
            if (!$enrollment || !$enrollment->classRoom) return $this->errorResponse('Active enrollment not found.', 404);

            $subjects = $this->quizService->getStudentSubjects($enrollment->classRoom->grade_level_id);
            return $this->successResponse($subjects, 'Subjects retrieved successfully.', 200);
        } catch (Exception $e) {
            Log::error('Student Subjects Fetch Error: ' . $e->getMessage());
            return $this->errorResponse('Failed to retrieve subjects.', 500);
        }
    }

    public function getQuizzesBySubject(Request $request, $gradeSubjectId)
    {
        try {
            $enrollment = $this->getCurrentEnrollment($request->user());
            if (!$enrollment || !$enrollment->classRoom) return $this->errorResponse('Active enrollment not found.', 404);

            $quizzes = $this->quizService->getStudentQuizzes($gradeSubjectId, $enrollment->classRoom->grade_level_id, $enrollment->id);
            return $this->successResponse($quizzes, 'Practice quizzes retrieved successfully.', 200);
        } catch (Exception $e) {
            $code = $e->getCode() === 403 ? 403 : 500;
            return $this->errorResponse($e->getMessage(), $code);
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $enrollment = $this->getCurrentEnrollment($request->user());
            if (!$enrollment || !$enrollment->classRoom) return $this->errorResponse('Active enrollment not found.', 404);

            $quiz = $this->quizService->getStudentQuizForSolving($id, $enrollment->classRoom->grade_level_id);

            return $this->successResponse([
                'quiz' => $quiz // تم الاستغناء عن إرسال enrollment_id للموبايل
            ], 'Quiz ready to start.', 200);

        } catch (Exception $e) {
            return $this->errorResponse('Quiz not found or unauthorized.', 404);
        }
    }

    public function submit(SubmitQuizAttemptRequest $request)
    {
        try {
            $enrollment = $this->getCurrentEnrollment($request->user());
            if (!$enrollment) return $this->errorResponse('Active enrollment not found.', 404);

            $data = $request->validated();
            $data['enrollment_id'] = $enrollment->id;

            $result = $this->quizService->submitAttempt($data);
            return $this->successResponse($result, 'Quiz attempt submitted and graded successfully.', 200);
        } catch (Exception $e) {
            Log::error('Student Submit Quiz Error: ' . $e->getMessage());
            return $this->errorResponse('Failed to submit quiz attempt.', 500);
        }
    }


    public function markAllRead(Request $request)
    {
        $count = $this->quizService->markAllRead($request->user());
        return $this->successResponse(['unread_count' => $count], 'All quizzes marked as read.', 200);
    }
    public function unreadCount(Request $request)
    {
        $count = $this->quizService->unreadCount($request->user());
        return $this->successResponse(['unread_count' => $count], 'Unread quizzes count retrieved.', 200);
    }
}

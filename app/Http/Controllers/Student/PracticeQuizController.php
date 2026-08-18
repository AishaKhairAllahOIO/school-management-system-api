<?php

namespace App\Http\Controllers\Student;

use App\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\Student\SubmitQuizAttemptRequest;
use App\Models\Enrollment;
use App\Services\Quiz\PracticeQuizService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PracticeQuizController extends Controller
{
    use ApiResource;

    public function __construct(
        protected PracticeQuizService $quizService
    ) {
    }

    /**
     * Get student's current active enrollment.
     */
    private function getCurrentEnrollment($user): ?Enrollment
    {
        if (!$user || !$user->student) {
            return null;
        }

        return Enrollment::query()
            ->where(
                'student_id',
                $user->student->id
            )
            ->whereHas('academicYear', function ($query) {
                $query->where(
                    'is_current',
                    true
                );
            })
            ->with([
                'classRoom',
                'student.user',
            ])
            ->latest()
            ->first();
    }

    /**
     * Get subjects for student's grade.
     */
    public function getSubjects(Request $request)
    {
        try {

            $enrollment =
                $this->getCurrentEnrollment(
                    $request->user()
                );

            if (
                !$enrollment ||
                !$enrollment->classRoom
            ) {
                return $this->errorResponse(
                    'Active enrollment not found.',
                    404
                );
            }

            $gradeLevelId =
                $enrollment->classRoom->grade_level_id;

            $subjects =
                $this->quizService
                    ->getStudentSubjects(
                        $gradeLevelId
                    );

            return $this->successResponse(
                $subjects,
                'Subjects retrieved successfully.',
                200
            );

        } catch (Exception $e) {

            Log::error(
                'Student Subjects Fetch Error.',
                [
                    'user_id' =>
                        $request->user()?->id,

                    'error' =>
                        $e->getMessage(),
                ]
            );

            return $this->errorResponse(
                'Error:Server',
                500
            );
        }
    }

    /**
     * Get quizzes by subject.
     */
    public function getQuizzesBySubject(
        Request $request,
        $gradeSubjectId
    ) {
        try {

            $enrollment =
                $this->getCurrentEnrollment(
                    $request->user()
                );

            if (
                !$enrollment ||
                !$enrollment->classRoom
            ) {
                return $this->errorResponse(
                    'Active enrollment not found.',
                    404
                );
            }

            $gradeLevelId =
                $enrollment->classRoom->grade_level_id;

            $quizzes =
                $this->quizService
                    ->getStudentQuizzes(
                        (int) $gradeSubjectId,
                        (int) $gradeLevelId,
                        (int) $enrollment->id
                    );

            return $this->successResponse(
                $quizzes,
                'Practice quizzes retrieved successfully.',
                200
            );

        } catch (Exception $e) {

            $code =
                $e->getCode() === 403
                ? 403
                : 500;

            Log::warning(
                'Student Fetch Quizzes Error.',
                [
                    'user_id' =>
                        $request->user()?->id,

                    'grade_subject_id' =>
                        $gradeSubjectId,

                    'error' =>
                        $e->getMessage(),
                ]
            );

            return $this->errorResponse(
                $e->getMessage(),
                $code
            );
        }
    }

    /**
     * Show quiz for solving.
     */
    public function show(
        Request $request,
        $id
    ) {
        try {

            $enrollment =
                $this->getCurrentEnrollment(
                    $request->user()
                );

            if (
                !$enrollment ||
                !$enrollment->classRoom
            ) {
                return $this->errorResponse(
                    'Active enrollment not found.',
                    404
                );
            }

            $gradeLevelId =
                $enrollment->classRoom->grade_level_id;

            $quiz =
                $this->quizService
                    ->getStudentQuizForSolving(
                        (int) $id,
                        (int) $gradeLevelId
                    );

            return $this->successResponse(
                [
                    'quiz' => $quiz,
                ],
                'Quiz ready to start.',
                200
            );

        } catch (Exception $e) {

            Log::warning(
                'Student Quiz Show Error.',
                [
                    'user_id' =>
                        $request->user()?->id,

                    'quiz_id' => $id,

                    'error' =>
                        $e->getMessage(),
                ]
            );

            return $this->errorResponse(
                'Quiz not found or unauthorized.',
                404
            );
        }
    }

    /**
     * Submit quiz attempt.
     */
    public function submit(
        SubmitQuizAttemptRequest $request
    ) {
        try {

            $enrollment =
                $this->getCurrentEnrollment(
                    $request->user()
                );

            if (!$enrollment) {

                return $this->errorResponse(
                    'Active enrollment not found.',
                    404
                );
            }

            $data =
                $request->validated();

            /*
             * Do NOT accept enrollment_id from Flutter.
             * The backend determines it from the authenticated student.
             */
            $data['enrollment_id'] =
                $enrollment->id;

            $result =
                $this->quizService
                    ->submitAttempt($data);

            return $this->successResponse(
                $result,
                'Quiz attempt submitted and graded successfully.',
                200
            );

        } catch (Exception $e) {

            Log::error(
                'Student Submit Quiz Error.',
                [
                    'user_id' =>
                        $request->user()?->id,

                    'error' =>
                        $e->getMessage(),
                ]
            );

            $code =
                in_array(
                    $e->getCode(),
                    [403, 422],
                    true
                )
                ? $e->getCode()
                : 500;

            return $this->errorResponse(
                $e->getMessage(),
                $code
            );
        }
    }

    /**
     * Mark all quizzes of subject as read.
     */
    public function markAllRead(
        Request $request,
        int $gradeSubjectId
    ) {
        try {

            $result =
                $this->quizService
                    ->markAllRead(
                        $request->user(),
                        $gradeSubjectId
                    );

            return $this->successResponse(
                $result,
                'All quizzes marked as read.',
                200
            );

        } catch (Exception $e) {

            Log::error(
                'Student Mark Quiz Read Error.',
                [
                    'user_id' =>
                        $request->user()?->id,

                    'grade_subject_id' =>
                        $gradeSubjectId,

                    'error' =>
                        $e->getMessage(),
                ]
            );

            return $this->errorResponse(
                'Failed to mark quizzes as read.',
                500
            );
        }
    }

    /**
     * Get unread quizzes count.
     */
    public function unreadCount(Request $request)
    {
        try {

            $count =
                $this->quizService
                    ->unreadCount(
                        $request->user()
                    );

            return $this->successResponse(
                $count,
                'Unread quizzes count retrieved.',
                200
            );

        } catch (Exception $e) {

            Log::error(
                'Student Unread Quiz Count Error.',
                [
                    'user_id' =>
                        $request->user()?->id,

                    'error' =>
                        $e->getMessage(),
                ]
            );

            return $this->errorResponse(
                'Failed to retrieve unread quizzes count.',
                500
            );
        }
    }

    /**
     * Get last attempt details.
     */
    public function getLastAttemptDetails(Request $request, $quizId)
    {
        try {

            $enrollment =
                $this->getCurrentEnrollment(
                    $request->user()
                );

            if (
                !$enrollment ||
                !$enrollment->classRoom
            ) {
                return $this->errorResponse(
                    'Active enrollment not found.',
                    404
                );
            }

            $attemptDetails =
                $this->quizService
                    ->getLastQuizAttemptDetails(
                        (int) $quizId,
                        (int) $enrollment->id
                    );

            if (!$attemptDetails) {

                return $this->successResponse(
                    null,
                    'You have not attempted this quiz yet.',
                    200
                );
            }

            return $this->successResponse(
                $attemptDetails,
                'Last attempt details retrieved successfully.',
                200
            );

        } catch (Exception $e) {

            $code =
                in_array(
                    $e->getCode(),
                    [403, 404],
                    true
                )
                ? $e->getCode()
                : 500;

            Log::error(
                'Student Last Quiz Attempt Error.',
                [
                    'user_id' =>
                        $request->user()?->id,

                    'quiz_id' =>
                        $quizId,

                    'error' =>
                        $e->getMessage(),
                ]
            );

            return $this->errorResponse(
                $e->getMessage(),
                $code
            );
        }
    }
}

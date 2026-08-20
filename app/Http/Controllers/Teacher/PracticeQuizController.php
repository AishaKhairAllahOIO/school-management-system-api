<?php

namespace App\Http\Controllers\Teacher;

use App\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StorePracticeQuizRequest;
use App\Services\Quiz\PracticeQuizService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
     * Create practice quiz.
     */
    public function store(
        StorePracticeQuizRequest $request
    ) {
        try {

            $teacher =
                $request->user()->staff;

            if (!$teacher) {

                return $this->errorResponse(
                    'Teacher profile not found.',
                    403
                );
            }

            $quiz =
                $this->quizService
                    ->createQuiz(
                        $request->validated(),
                        (int) $teacher->id
                    );

            return $this->successResponse(
                [
                    'quiz_id' => $quiz->id,
                ],
                'Practice quiz created successfully.',
                201
            );

        } catch (Exception $e) {

            Log::error(
                'Teacher Quiz Creation Error.',
                [
                    'teacher_id' =>
                        $request->user()?->staff?->id,

                    'error' =>
                        $e->getMessage(),
                ]
            );

            $code =
                $e->getCode() === 403
                    ? 403
                    : 500;

            return $this->errorResponse(
                $code === 403
                    ? $e->getMessage()
                    : 'An error occurred while creating the practice quiz.',
                $code
            );
        }
    }

    /**
     * Get quizzes for a GradeSubject.
     */
    public function getQuizzesByGradeSubject(
        Request $request,
        $gradeSubjectId,
        $gradeLevelId
    ) {
        try {

            $teacher =
                $request->user()->staff;

            if (!$teacher) {

                return $this->errorResponse(
                    'Teacher profile not found.',
                    403
                );
            }

            $quizzes =
                $this->quizService
                    ->getTeacherQuizzes(
                        (int) $gradeLevelId,
                        (int) $gradeSubjectId,
                        (int) $teacher->id
                    );

            return $this->successResponse(
                $quizzes,
                'Quizzes retrieved successfully.',
                200
            );

        } catch (Exception $e) {

            $code =
                $e->getCode() === 403
                    ? 403
                    : 500;

            return $this->errorResponse(
                $e->getMessage(),
                $code
            );
        }
    }

    /**
     * Show quiz details.
     */
    public function show(
        Request $request,
        $quizId
    ) {
        try {

            $teacher =
                $request->user()->staff;

            if (!$teacher) {

                return $this->errorResponse(
                    'Teacher profile not found.',
                    403
                );
            }

            $quiz =
                $this->quizService
                    ->getQuizDetails(
                        (int) $quizId,
                        (int) $teacher->id
                    );

            if (!$quiz) {

                return $this->errorResponse(
                    'Quiz not found.',
                    404
                );
            }

            return $this->successResponse(
                $quiz,
                'Quiz details retrieved successfully.',
                200
            );

        } catch (Exception $e) {

            Log::error(
                'Teacher Show Quiz Error.',
                [
                    'quiz_id' =>
                        $quizId,

                    'teacher_id' =>
                        $request->user()?->staff?->id,

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
     * Activate / hide quiz.
     */
    public function toggleActive(
        Request $request,
        $id
    ) {
        try {

            $teacher =
                $request->user()->staff;

            if (!$teacher) {

                return $this->errorResponse(
                    'Teacher profile not found.',
                    403
                );
            }

            $isActive =
                $this->quizService
                    ->toggleQuizStatus(
                        (int) $id,
                        (int) $teacher->id
                    );

            $status =
                $isActive
                    ? 'activated'
                    : 'hidden';

            return $this->successResponse(
                [
                    'is_active' => $isActive,
                ],
                "Quiz successfully {$status}.",
                200
            );

        } catch (ModelNotFoundException $e) {

            return $this->errorResponse(
                'Quiz not found.',
                404
            );

        } catch (Exception $e) {

            Log::error(
                'Teacher Toggle Quiz Error.',
                [
                    'quiz_id' => $id,

                    'teacher_id' =>
                        $request->user()?->staff?->id,

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
     * Delete quiz.
     */
    public function destroy(
        Request $request,
        $id
    ) {
        try {

            $teacher =
                $request->user()->staff;

            if (!$teacher) {

                return $this->errorResponse(
                    'Teacher profile not found.',
                    403
                );
            }

            $this->quizService
                ->deleteQuiz(
                    (int) $id,
                    (int) $teacher->id
                );

            return $this->successResponse(
                null,
                'Quiz deleted successfully.',
                200
            );

        } catch (ModelNotFoundException $e) {

            return $this->errorResponse(
                'Quiz not found.',
                404
            );

        } catch (Exception $e) {

            $code =
                $e->getCode() === 403
                    ? 403
                    : 500;

            return $this->errorResponse(
               'Error:Server',
                $code
            );
        }
    }
}

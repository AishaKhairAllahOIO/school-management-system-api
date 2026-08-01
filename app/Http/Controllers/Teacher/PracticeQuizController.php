<?php

namespace App\Http\Controllers\Teacher;

use App\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StorePracticeQuizRequest;
use App\Services\Quiz\PracticeQuizService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class PracticeQuizController extends Controller
{
    use ApiResource;

    public function __construct(protected PracticeQuizService $quizService) {}

    public function store(StorePracticeQuizRequest $request)
    {
        try {
            $quiz = $this->quizService->createQuiz($request->validated(), $request->user()->staff->id);
            return $this->successResponse(['quiz_id' => $quiz->id], 'Practice quiz created successfully.', 201);
        } catch (Exception $e) {
            Log::error('Teacher Quiz Creation Error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred while creating the practice quiz.', 500);
        }
    }
    public function getQuizzesByGradeSubject(Request $request, $gradeSubjectId)
    {
        try {
            $quizzes = $this->quizService->getTeacherQuizzes($gradeSubjectId, $request->user()->staff->id);
            return $this->successResponse($quizzes, 'Quizzes retrieved successfully.', 200);
        } catch (Exception $e) {
            $code = $e->getCode() === 403 ? 403 : 500;
            return $this->errorResponse($e->getMessage(), $code);
        }
    }
    public function toggleActive(Request $request, $id)
    {
        try {
            $isActive = $this->quizService->toggleQuizStatus($id, $request->user()->staff->id);
            $status = $isActive ? 'activated' : 'hidden';
            return $this->successResponse(['is_active' => $isActive], "Quiz successfully {$status}.", 200);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Quiz not found.', 404);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to change quiz status.', 500);
        }
    }
    public function destroy(Request $request, $id)
    {
        try {
            $this->quizService->deleteQuiz($id, $request->user()->staff->id);
            return $this->successResponse(null, 'Quiz deleted successfully.', 200);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Quiz not found.', 404);
        } catch (Exception $e) {
            $code = $e->getCode() === 403 ? 403 : 500;
            return $this->errorResponse($e->getMessage(), $code);
        }
    }
    public function show(Request $request, $quizId)
    {
        $quiz = $this->quizService->getQuizDetails($quizId, $request->user()->staff->id);
        if (!$quiz) return $this->errorResponse('Quiz not found.', 404);

        return $this->successResponse($quiz, 'Quiz details retrieved successfully.', 200);
    }
}

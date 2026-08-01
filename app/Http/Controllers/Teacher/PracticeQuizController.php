<?php

namespace App\Http\Controllers\Teacher;

use App\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StorePracticeQuizRequest;
use App\Models\GradeSubject;
use App\Models\PracticeQuiz;
use App\Services\Quiz\PracticeQuizService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PracticeQuizController extends Controller
{
    use ApiResource;

    protected PracticeQuizService $quizService;

    public function __construct(PracticeQuizService $quizService)
    {
        $this->quizService = $quizService;
    }

    public function store(StorePracticeQuizRequest $request)
    {
        try {
            $validatedData = $request->validated();
            $teacherId = $request->user()->staff->id;

            $quiz = $this->quizService->createQuiz($validatedData, $teacherId);

            return $this->successResponse(
                ['quiz_id' => $quiz->id],
                'Practice quiz created successfully and students have been notified.',
                201
            );

        } catch (Exception $e) {
            Log::error('Teacher Quiz Creation Error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred while creating the practice quiz.', 500);
        }
    }
    public function getQuizzesByGradeSubject(Request $request, $gradeSubjectId)
    {
        try {
            $teacherId = $request->user()->staff->id;

            $isValid = GradeSubject::where('id', $gradeSubjectId)
                ->whereHas('teacherAssignments', function ($query) use ($teacherId) {
                    $query->where('teacher_id', $teacherId)
                          ->whereHas('academicYear', fn($q) => $q->where('is_current', true));
                })->exists();

            if (!$isValid) {
                return $this->errorResponse('You are not authorized to view or manage this subject.', 403);
            }

            $quizzes = PracticeQuiz::where('grade_subject_id', $gradeSubjectId)
                ->where('teacher_id', $teacherId)
                ->withCount('attempts')
                ->withSum('questions', 'mark')
                ->latest()
                ->get()
                ->map(function ($quiz) {
                    $isLocked = $quiz->attempts_count > 0;

                    return [
                        'id'             => $quiz->id,
                        'title'          => $quiz->title,
                        'total_mark'     => (float) ($quiz->questions_sum_mark ?? 0),
                        'attempts_count' => $quiz->attempts_count,
                        'is_active'      => $quiz->is_active,
                        'is_locked'      => $isLocked,
                        'created_at'     => $quiz->created_at->format('Y-m-d H:i'),
                    ];
                });

            return $this->successResponse($quizzes, 'Quizzes retrieved successfully.', 200);

        } catch (Exception $e) {
            Log::error('Teacher Fetch Quizzes Error: ' . $e->getMessage());
            return $this->errorResponse('Failed to retrieve quizzes.', 500);
        }
    }
    public function toggleActive(Request $request, $id)
    {
        try {
            $teacherId = $request->user()->staff->id;

            $quiz = PracticeQuiz::where('id', $id)
                ->where('teacher_id', $teacherId)
                ->first();

            if (!$quiz) {
                return $this->errorResponse('Quiz not found.', 404);
            }

            $quiz->update(['is_active' => !$quiz->is_active]);

            $status = $quiz->is_active ? 'activated' : 'hidden';

            return $this->successResponse(
                ['is_active' => $quiz->is_active],
                "Quiz successfully {$status}.",
                200
            );

        } catch (Exception $e) {
            Log::error('Teacher Toggle Quiz Error: ' . $e->getMessage());
            return $this->errorResponse('Failed to change quiz status.', 500);
        }
    }
    public function destroy(Request $request, $id)
    {
        try {
            $teacherId = $request->user()->staff->id;

            $quiz = PracticeQuiz::where('id', $id)
                ->where('teacher_id', $teacherId)
                ->first();

            if (!$quiz) {
                return $this->errorResponse('Quiz not found.', 404);
            }

            if ($quiz->attempts()->exists()) {
                return $this->errorResponse('Cannot delete this quiz because students have already attempted it. You can hide it instead.', 403);
            }

            $quiz->delete();

            return $this->successResponse(null, 'Quiz deleted successfully.', 200);

        } catch (Exception $e) {
            Log::error('Teacher Quiz Delete Error: ' . $e->getMessage());
            return $this->errorResponse('Failed to delete the quiz.', 500);
        }
    }

    public function show(Request $request, $quizId)
    {
        $teacherId = $request->user()->staff->id;

        $quiz = $this->quizService->getQuizDetails($quizId, $teacherId);
        return $this->successResponse($quiz, 'Quiz details retrieved successfully.', 200);
    }

}

<?php

namespace App\Http\Controllers\Student;

use App\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\Student\SubmitQuizAttemptRequest;
use App\Models\Enrollment;
use App\Models\GradeSubject;
use App\Models\PracticeQuiz;
use App\Services\Quiz\PracticeQuizService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class PracticeQuizController extends Controller
{
    use ApiResource;

    protected PracticeQuizService $quizService;

    public function __construct(PracticeQuizService $quizService)
    {
        $this->quizService = $quizService;
    }

    /**
     * Helper Method: Get current enrollment
     */
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

            if (!$enrollment || !$enrollment->classRoom) {
                return $this->errorResponse('Active enrollment not found.', 404);
            }

            $gradeLevelId = $enrollment->classRoom->grade_level_id;

            $subjects = GradeSubject::with('subject:id,subject_name')
                ->where('grade_level_id', $gradeLevelId)
                ->get()
                ->map(function ($gs) {
                    return [
                        'grade_subject_id' => $gs->id,
                        'subject_name'     => $gs->subject->subject_name ?? 'N/A',
                    ];
                });

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

            if (!$enrollment || !$enrollment->classRoom) {
                return $this->errorResponse('Active enrollment not found.', 404);
            }

            $gradeLevelId = $enrollment->classRoom->grade_level_id;
            $isValidSubject = GradeSubject::where('id', $gradeSubjectId)
                ->where('grade_level_id', $gradeLevelId)
                ->exists();

            if (!$isValidSubject) {
                return $this->errorResponse('You are not authorized to view quizzes for this subject.', 403);
            }

            $quizzes = PracticeQuiz::where('grade_subject_id', $gradeSubjectId)
                ->where('is_active', true)
                ->withSum('questions', 'mark')
                ->with(['attempts' => function($query) use ($enrollment) {
                    $query->where('enrollment_id', $enrollment->id);
                }])
                ->latest()
                ->get()
                ->map(function ($quiz) {
                    $attempts = $quiz->attempts;
                    $attemptsCount = $attempts->count();

                    $highScore = $attemptsCount > 0 ? $attempts->max('earned_mark') : 0;

                    $totalMark = $quiz->questions_sum_mark ?? 0;

                    return [
                        'id'             => $quiz->id,
                        'title'          => $quiz->title,
                        'description'    => $quiz->description,
                        'total_mark'     => (float) $totalMark,
                        'attempts_count' => $attemptsCount,
                        'high_score'     => (float) $highScore,
                        'progress_msg'   => $attemptsCount > 0
                                            ? "You have completed this practice {$attemptsCount} time(s)."
                                            : "You haven't attempted this practice yet.",
                        'created_at'     => $quiz->created_at->format('Y-m-d H:i'),
                    ];
                });

            return $this->successResponse($quizzes, 'Practice quizzes retrieved successfully.', 200);

        } catch (Exception $e) {
            Log::error('Student Quizzes By Subject Error: ' . $e->getMessage());
            return $this->errorResponse('Failed to retrieve quizzes.', 500);
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $enrollment = $this->getCurrentEnrollment($request->user());

            if (!$enrollment || !$enrollment->classRoom) {
                return $this->errorResponse('Active enrollment not found.', 404);
            }

            $gradeLevelId = $enrollment->classRoom->grade_level_id;

            $quiz = PracticeQuiz::whereHas('gradeSubject', function ($query) use ($gradeLevelId) {
                    $query->where('grade_level_id', $gradeLevelId);
                })
                ->with(['questions' => function ($q) {
                    $q->select('id', 'practice_quiz_id', 'question_text', 'mark');
                }, 'questions.options' => function ($q) {
                    $q->select('id', 'question_id', 'option_text');
                }])
                ->where('id', $id)
                ->where('is_active', true)
                ->first();

            if (!$quiz) {
                return $this->errorResponse('Quiz not found or unauthorized.', 404);
            }

            return $this->successResponse([
                'enrollment_id' => $enrollment->id,
                'quiz'          => $quiz
            ], 'Quiz ready to start.', 200);

        } catch (Exception $e) {
            Log::error('Student Show Quiz Error: ' . $e->getMessage());
            return $this->errorResponse('Failed to load the quiz.', 500);
        }
    }


    public function submit(SubmitQuizAttemptRequest $request)
    {
        try {
            $result = $this->quizService->submitAttempt($request->validated());

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

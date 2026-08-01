<?php

namespace App\Services\Quiz;

use App\Models\PracticeQuiz;
use App\Models\Question;
use App\Models\Option;
use App\Models\StudentQuizAttempt;
use App\Models\GradeSubject;
use App\Models\Enrollment;
use App\Models\User;
use App\Models\Alert;
use App\Jobs\SendPushNotification;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class PracticeQuizService
{

    public function createQuiz(array $data, int $teacherId)
    {
        $quiz = DB::transaction(function () use ($data, $teacherId) {
            $quiz = PracticeQuiz::create([
                'grade_subject_id' => $data['grade_subject_id'],
                'teacher_id'       => $teacherId,
                'title'            => $data['title'],
                'description'      => $data['description'] ?? null,
                'is_active'        => $data['is_active'] ?? true,
            ]);

            foreach ($data['questions'] as $questionData) {
                $question = $quiz->questions()->create([
                    'question_text' => $questionData['question_text'],
                    'mark'          => $questionData['mark'],
                ]);

                $optionsToInsert = [];
                $now = now();

                foreach ($questionData['options'] as $optionData) {
                    $optionsToInsert[] = [
                        'question_id' => $question->id,
                        'option_text' => $optionData['option_text'],
                        'is_correct'  => $optionData['is_correct'],
                        'created_at'  => $now,
                        'updated_at'  => $now,
                    ];
                }

                Option::insert($optionsToInsert);
            }

            return $quiz;
        });

        if ($quiz->is_active) {
            $this->notifyStudentsAboutNewQuiz($quiz);
        }

        return $quiz;
    }
    private function notifyStudentsAboutNewQuiz(PracticeQuiz $quiz): void
    {
        try {
            $gradeSubject = GradeSubject::with('subject', 'gradeLevel')->find($quiz->grade_subject_id);

            if (!$gradeSubject) {
                return;
            }

            $subjectName = $gradeSubject->subject->subject_name ?? 'Subject';
            $gradeName = $gradeSubject->gradeLevel->name ?? 'Class';

            $title = "New Practice Quiz Available!";
            $body = "Your teacher added a new practice quiz in {$subjectName} for {$gradeName} students. Test your knowledge now!";

            $enrollments = Enrollment::whereHas('classRoom', function ($q) use ($gradeSubject) {
                $q->where('grade_level_id', $gradeSubject->grade_level_id);
            })
                ->whereHas('academicYear', function ($q) {
                    $q->where('is_current', true);
                })
                ->with('student.user')
                ->get();

            $userIdsToPush = [];
            $alertsToInsert = [];
            $now = now();

            foreach ($enrollments as $enrollment) {
                $alertsToInsert[] = [
                    'title'           => $title,
                    'body'            => $body,
                    'type'            => 'practice_quiz',
                    'notifiable_type' => Enrollment::class,
                    'notifiable_id'   => $enrollment->id,
                    'created_at'      => $now,
                    'updated_at'      => $now,
                ];

                // Prepare Push Notification User IDs
                if ($enrollment->student && $enrollment->student->user) {
                    $userIdsToPush[] = $enrollment->student->user->id;
                }
            }

            if (!empty($alertsToInsert)) {
                Alert::insert($alertsToInsert);
            }

            if (!empty($userIdsToPush)) {
                $pushData = [
                    'type'             => 'new_practice_quiz',
                    'quiz_id'          => (string) $quiz->id,
                    'grade_subject_id' => (string) $gradeSubject->id,
                ];

                SendPushNotification::dispatch(
                    array_unique($userIdsToPush),
                    $title,
                    $body,
                    $pushData
                );
            }
        } catch (Exception $e) {
            Log::error('Practice Quiz Notification Error', [
                'quiz_id' => $quiz->id,
                'error'   => $e->getMessage()
            ]);
        }
    }
    public function submitAttempt(array $data)
    {
        return DB::transaction(function () use ($data) {
            $enrollmentId = $data['enrollment_id'];
            $answers = collect($data['answers']);
            $questionIds = $answers->pluck('question_id')->toArray();

            $questions = Question::with([
                'options' => function ($query) {
                    $query->where('is_correct', true);
                }
            ])->whereIn('id', $questionIds)->get()->keyBy('id');

            $totalMark = 0;
            $earnedMark = 0;
            $quizId = null;
            $feedback = [];

            foreach ($answers as $answer) {
                $question = $questions[$answer['question_id']] ?? null;
                if (!$question) continue;

                if ($quizId === null) {
                    $quizId = $question->practice_quiz_id;
                }

                $totalMark += $question->mark;

                $correctOption = $question->options->first();
                $isCorrect = $correctOption && $correctOption->id == $answer['option_id'];

                if ($isCorrect) {
                    $earnedMark += $question->mark;
                }

                $feedback[] = [
                    'question_id'       => $question->id,
                    'is_correct'        => $isCorrect,
                    'correct_option_id' => $correctOption ? $correctOption->id : null,
                ];
            }

            $attempt = StudentQuizAttempt::create([
                'practice_quiz_id' => $quizId,
                'enrollment_id'    => $enrollmentId,
                'total_mark'       => $totalMark,
                'earned_mark'      => $earnedMark,
            ]);

            return [
                'attempt_id'  => $attempt->id,
                'total_mark'  => $totalMark,
                'earned_mark' => $earnedMark,
                'percentage'  => $totalMark > 0 ? round(($earnedMark / $totalMark) * 100, 2) : 0,
                'feedback'    => $feedback,
            ];
        });
    }
private function getBaseQueryForUser(User $user)
    {
        if ($user->hasRole('student') && $user->student) {

            $enrollment = Enrollment::where('student_id', $user->student->id)
                ->whereHas('academicYear', fn($q) => $q->where('is_current', true))
                ->with('classRoom')
                ->latest()
                ->first();

            if (!$enrollment || !$enrollment->classRoom) {
                return PracticeQuiz::query()->where('id', '<', 0);
            }

            $gradeLevelId = $enrollment->classRoom->grade_level_id;

            // جلب الكويزات التي تتبع للمواد الموجودة في صف هذا الطالب
            return PracticeQuiz::whereHas('gradeSubject', function ($q) use ($gradeLevelId) {
                $q->where('grade_level_id', $gradeLevelId);
            });
        }

        if ($user->hasRole('teacher') && $user->staff) {
            return PracticeQuiz::where('teacher_id', $user->staff->id);
        }

        return PracticeQuiz::query();
    }
    public function unreadCount(User $user): int
    {
        return $this->getBaseQueryForUser($user)
            ->whereDoesntHave('readers', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->count();
    }
    public function markAllRead(User $user): int
    {
        $unreadQuizzesIds = $this->getBaseQueryForUser($user)
            ->whereDoesntHave('readers', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->pluck('id');

        if ($unreadQuizzesIds->isNotEmpty()) {
            $syncData = [];
            $now = now();

            foreach ($unreadQuizzesIds as $id) {
                $syncData[$id] = ['read_at' => $now];
            }

            $user->readPracticeQuizzes()->syncWithoutDetaching($syncData);
        }

        return $this->unreadCount($user);
    }

    public function getQuizDetails(int $quizId, int $teacherId)
    {
        try {

            $quiz = PracticeQuiz::where('id', $quizId)
                ->where('teacher_id', $teacherId)
                ->withCount('attempts')
                ->withSum('questions', 'mark')
                ->with(['questions', 'questions.options'])
                ->first();

            if (!$quiz) {
                throw new ModelNotFoundException('Quiz not found or unauthorized.', 404);
            }

            $responseData = [
                'id'             => $quiz->id,
                'title'          => $quiz->title,
                'description'    => $quiz->description,
                'is_active'      => $quiz->is_active,
                'is_locked'      => $quiz->attempts_count > 0,
                'total_mark'     => (float) ($quiz->questions_sum_mark ?? 0),
                'attempts_count' => $quiz->attempts_count,
                'created_at'     => $quiz->created_at->format('Y-m-d H:i'),

                'questions'      => $quiz->questions->map(function ($question) {
                    return [
                        'id'            => $question->id,
                        'question_text' => $question->question_text,
                        'mark'          => (float) $question->mark,
                        'options'       => $question->options->map(function ($option) {
                            return [
                                'id'          => $option->id,
                                'option_text' => $option->option_text,
                                'is_correct'  => $option->is_correct,
                            ];
                        })
                    ];
                })
            ];

            return $responseData;
        } catch (ModelNotFoundException $e) {
            Log::error('Teacher Fetch Quiz Details Error: ' . $e->getMessage());
            return null;
        } catch (Exception $e) {
            Log::error('Teacher Fetch Quiz Details Error: ' . $e->getMessage());
            return null;
        }



    }

    public function getTeacherQuizzes(int $gradeSubjectId, int $teacherId)
    {
        $isValid = GradeSubject::where('id', $gradeSubjectId)
            ->whereHas('teacherAssignments', function ($query) use ($teacherId) {
                $query->where('teacher_id', $teacherId)
                      ->whereHas('academicYear', fn($q) => $q->where('is_current', true));
            })->exists();

        if (!$isValid) {
            throw new Exception('You are not authorized to view or manage this subject.', 403);
        }

        return PracticeQuiz::where('grade_subject_id', $gradeSubjectId)
            ->where('teacher_id', $teacherId)
            ->withCount('attempts')
            ->withSum('questions', 'mark')
            ->latest()
            ->get()
            ->map(function ($quiz) {
                return [
                    'id'             => $quiz->id,
                    'title'          => $quiz->title,
                    'total_mark'     => (float) ($quiz->questions_sum_mark ?? 0),
                    'attempts_count' => $quiz->attempts_count,
                    'is_active'      => $quiz->is_active,
                    'is_locked'      => $quiz->attempts_count > 0,
                    'created_at'     => $quiz->created_at->format('Y-m-d H:i'),
                ];
            });
    }

    public function toggleQuizStatus(int $quizId, int $teacherId): bool
    {
        $quiz = PracticeQuiz::where('id', $quizId)
            ->where('teacher_id', $teacherId)
            ->firstOrFail();

        $quiz->update(['is_active' => !$quiz->is_active]);

        return $quiz->is_active;
    }

    public function deleteQuiz(int $quizId, int $teacherId): void
    {
        $quiz = PracticeQuiz::where('id', $quizId)
            ->where('teacher_id', $teacherId)
            ->firstOrFail();

        if ($quiz->attempts()->exists()) {
            throw new Exception('Cannot delete this quiz because students have already attempted it. You can hide it instead.', 403);
        }

        $quiz->delete();
    }

    public function getStudentSubjects(int $gradeLevelId)
    {
        return GradeSubject::with('subject:id,subject_name')
            ->where('grade_level_id', $gradeLevelId)
            ->get()
            ->map(function ($gs) {
                return [
                    'grade_subject_id' => $gs->id,
                    'subject_name'     => $gs->subject->subject_name ?? 'N/A',
                ];
            });
    }

    public function getStudentQuizzes(int $gradeSubjectId, int $gradeLevelId, int $enrollmentId)
    {
        $isValidSubject = GradeSubject::where('id', $gradeSubjectId)
            ->where('grade_level_id', $gradeLevelId)
            ->exists();

        if (!$isValidSubject) {
            throw new Exception('You are not authorized to view quizzes for this subject.', 403);
        }

        return PracticeQuiz::where('grade_subject_id', $gradeSubjectId)
            ->where('is_active', true)
            ->withSum('questions', 'mark')
            ->with(['attempts' => function($query) use ($enrollmentId) {
                $query->where('enrollment_id', $enrollmentId);
            }])
            ->latest()
            ->get()
            ->map(function ($quiz) {
                $attemptsCount = $quiz->attempts->count();
                $highScore = $attemptsCount > 0 ? $quiz->attempts->max('earned_mark') : 0;

                return [
                    'id'             => $quiz->id,
                    'title'          => $quiz->title,
                    'description'    => $quiz->description,
                    'total_mark'     => (float) ($quiz->questions_sum_mark ?? 0),
                    'attempts_count' => $attemptsCount,
                    'high_score'     => (float) $highScore,
                    'progress_msg'   => $attemptsCount > 0
                                        ? "You have completed this practice {$attemptsCount} time(s)."
                                        : "You haven't attempted this practice yet.",
                    'created_at'     => $quiz->created_at->format('Y-m-d H:i'),
                ];
            });
    }

    public function getStudentQuizForSolving(int $quizId, int $gradeLevelId)
    {
        $quiz = PracticeQuiz::whereHas('gradeSubject', function ($query) use ($gradeLevelId) {
                $query->where('grade_level_id', $gradeLevelId);
            })
            ->with(['questions' => function ($q) {
                $q->select('id', 'practice_quiz_id', 'question_text', 'mark');
            }, 'questions.options' => function ($q) {
                $q->select('id', 'question_id', 'option_text');
            }])
            ->where('id', $quizId)
            ->where('is_active', true)
            ->first();

        if (!$quiz) {
            throw new ModelNotFoundException('Quiz not found or unauthorized.');
        }

        return $quiz;
    }

}

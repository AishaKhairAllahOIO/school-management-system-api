<?php

namespace App\Services\Quiz;

use App\Enums\GradeName;
use App\Jobs\SendPushNotification;
use App\Models\Alert;
use App\Models\Enrollment;
use App\Models\GradeSubject;
use App\Models\Option;
use App\Models\PracticeQuiz;
use App\Models\Question;
use App\Models\StudentQuizAttempt;
use App\Models\StudentQuizAttemptAnswer;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class PracticeQuizService
{

    public function createQuiz(array $data, int $teacherId): PracticeQuiz
    {
        $quiz = DB::transaction(function () use ($data, $teacherId) {

            $gradeSubject = GradeSubject::query()
                ->with('gradeLevel')
                ->where('id', $data['grade_subject_id'])
                ->where('grade_level_id', $data['grade_level_id'])
                ->whereHas('teacherAssignments', function ($query) use ($teacherId) {
                    $query
                        ->where('teacher_id', $teacherId)
                        ->whereHas('academicYear', function ($q) {
                            $q->where('is_current', true);
                        });
                })
                ->first();

            if (!$gradeSubject) {
                throw new Exception(
                    'You are not authorized to create a quiz for this subject.',
                    403
                );
            }

            $quiz = PracticeQuiz::create([
                'grade_subject_id' => $data['grade_subject_id'],
                'grade_level_id' => $data['grade_level_id'],
                'teacher_id' => $teacherId,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);

            foreach ($data['questions'] as $questionData) {

                $question = $quiz->questions()->create([
                    'question_text' => $questionData['question_text'],
                    'mark' => $questionData['mark'],
                ]);

                $now = now();

                $optionsToInsert = [];

                foreach ($questionData['options'] as $optionData) {
                    $optionsToInsert[] = [
                        'question_id' => $question->id,
                        'option_text' => $optionData['option_text'],
                        'is_correct' => $optionData['is_correct'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                Option::insert($optionsToInsert);
            }

            return $quiz;
        });


        if ($quiz->is_active) {
            $this->notifyStudentsAboutNewQuiz($quiz);
        }

        return $quiz->load([
            'gradeLevel',
            'gradeSubject.subject',
        ]);
    }


  public function getTeacherQuizzes(
    int $gradeSubjectId,
    int $gradeLevelId,
    int $teacherId
): Collection {

    $gradeSubjects = GradeSubject::query()
        ->where('id', $gradeSubjectId)
        ->where('grade_level_id', $gradeLevelId)
        ->whereHas('teacherAssignments', function ($query) use ($teacherId) {
            $query->where('teacher_id', $teacherId)
                ->whereHas('academicYear', function ($q) {
                    $q->where('is_current', true);
                });
        })
        ->with('gradeLevel')
        ->get();


    return $gradeSubjects->map(function ($gradeSubject) use ($teacherId) {

        return [
            'grade_subject_id' => $gradeSubject->id,

            'grade_level' => [
                'id' => $gradeSubject->gradeLevel->id,
                'name' => $gradeSubject->gradeLevel->name?->value
                    ?? $gradeSubject->gradeLevel->name,
            ],

            'quizzes' => PracticeQuiz::query()
                ->where('grade_subject_id', $gradeSubject->id)
                ->where('teacher_id', $teacherId)
                ->withCount('attempts')
                ->withSum('questions', 'mark')
                ->latest()
                ->get()
                ->map(function ($quiz) {

                    return [
                        'id' => $quiz->id,
                        'title' => $quiz->title,
                        'total_mark' => (float) $quiz->questions_sum_mark,
                        'attempts_count' => (int) $quiz->attempts_count,
                        'is_active' => (bool) $quiz->is_active,
                    ];
                }),
        ];
    });
}

    public function getQuizDetails(
        int $quizId,
        int $teacherId
    ): ?array {
        try {

            $quiz = PracticeQuiz::query()
                ->where('id', $quizId)
                ->where('teacher_id', $teacherId)
                ->with([
                    'gradeLevel',
                    'gradeSubject.subject',
                    'questions.options',
                ])
                ->withCount('attempts')
                ->withSum('questions', 'mark')
                ->first();

            if (!$quiz) {
                throw new ModelNotFoundException(
                    'Quiz not found or unauthorized.'
                );
            }

            return [
                'id' => $quiz->id,

                'title' => $quiz->title,

                'description' => $quiz->description,

                'is_active' => (bool) $quiz->is_active,

                'is_locked' => $quiz->attempts_count > 0,

                'total_mark' => (float) (
                    $quiz->questions_sum_mark ?? 0
                ),

                'attempts_count' => (int) $quiz->attempts_count,

                'grade_level' => [
                    'id' => $quiz->gradeLevel?->id,
                    'name' => $quiz->gradeLevel?->name?->value
                        ?? $quiz->gradeLevel?->name,
                    'level' => $quiz->gradeLevel?->level,
                ],

                'subject' => [
                    'id' => $quiz->gradeSubject?->subject?->id,
                    'name' => $quiz->gradeSubject?->subject?->subject_name,
                ],

                'created_at' => $quiz->created_at
                    ->format('Y-m-d H:i'),

                'questions' => $quiz->questions
                    ->map(function ($question) {

                        return [
                            'id' => $question->id,

                            'question_text' =>
                                $question->question_text,

                            'mark' => (float) $question->mark,

                            'options' => $question->options
                                ->map(function ($option) {

                                    return [
                                        'id' => $option->id,
                                        'option_text' =>
                                            $option->option_text,
                                        'is_correct' =>
                                            (bool) $option->is_correct,
                                    ];
                                })
                                ->values(),
                        ];
                    })
                    ->values(),
            ];

        } catch (ModelNotFoundException $e) {

            Log::warning(
                'Teacher attempted to access unavailable quiz.',
                [
                    'quiz_id' => $quizId,
                    'teacher_id' => $teacherId,
                ]
            );

            return null;
        } catch (Exception $e) {

            Log::error(
                'Teacher Fetch Quiz Details Error.',
                [
                    'quiz_id' => $quizId,
                    'teacher_id' => $teacherId,
                    'error' => $e->getMessage(),
                ]
            );

            return null;
        }
    }


    public function toggleQuizStatus(
        int $quizId,
        int $teacherId
    ): bool {
        $quiz = PracticeQuiz::query()
            ->where('id', $quizId)
            ->where('teacher_id', $teacherId)
            ->firstOrFail();

        $quiz->update([
            'is_active' => !$quiz->is_active,
        ]);

        return (bool) $quiz->is_active;
    }


    public function deleteQuiz(
        int $quizId,
        int $teacherId
    ): void {
        $quiz = PracticeQuiz::query()
            ->where('id', $quizId)
            ->where('teacher_id', $teacherId)
            ->firstOrFail();

        if ($quiz->attempts()->exists()) {
            throw new Exception(
                'Cannot delete this quiz because students have already attempted it. You can hide it instead.',
                403
            );
        }

        DB::transaction(function () use ($quiz) {

            $quiz->questions()->each(function ($question) {
                $question->options()->delete();
            });

            $quiz->questions()->delete();

            $quiz->readers()->detach();

            $quiz->delete();
        });
    }



    public function getStudentSubjects(
        int $gradeLevelId
    ): Collection {
        return GradeSubject::query()
            ->where('grade_level_id', $gradeLevelId)
            ->with('subject:id,subject_name')
            ->get()
            ->map(function ($gradeSubject) {

                return [
                    'grade_subject_id' => $gradeSubject->id,

                    'subject_id' => $gradeSubject->subject?->id,

                    'subject_name' =>
                        $gradeSubject->subject?->subject_name,
                ];
            })
            ->values();
    }

    public function getStudentQuizzes(
        int $gradeSubjectId,
        int $gradeLevelId,
        int $enrollmentId
    ): Collection {


        $gradeSubject = GradeSubject::query()
            ->where('id', $gradeSubjectId)
            ->where('grade_level_id', $gradeLevelId)
            ->first();

        if (!$gradeSubject) {
            throw new Exception(
                'You are not authorized to view quizzes for this subject.',
                403
            );
        }


        $enrollment = Enrollment::query()
            ->with('classRoom')
            ->where('id', $enrollmentId)
            ->whereHas('classRoom', function ($query) use ($gradeLevelId) {
                $query->where('grade_level_id', $gradeLevelId);
            })
            ->first();

        if (!$enrollment) {
            throw new Exception(
                'Invalid enrollment.',
                403
            );
        }

        $userId = $enrollment->student?->user?->id;

        return PracticeQuiz::query()
            ->where('grade_subject_id', $gradeSubjectId)
            ->where('grade_level_id', $gradeLevelId)
            ->where('is_active', true)

            ->with('gradeLevel')

            ->withSum('questions', 'mark')

            ->with([
                'attempts' => function ($query) use ($enrollmentId) {
                    $query->where(
                        'enrollment_id',
                        $enrollmentId
                    );
                },
            ])

            ->when(
                $userId,
                function ($query) use ($userId) {

                    $query->withExists([
                        'readers as is_read' => function ($query) use ($userId) {
                            $query->where(
                                'user_id',
                                $userId
                            );
                        },
                    ]);
                }
            )

            ->latest()

            ->get()

            ->map(function (PracticeQuiz $quiz) {

                $attemptsCount =
                    $quiz->attempts->count();

                $highScore = $attemptsCount > 0
                    ? $quiz->attempts->max('earned_mark')
                    : 0;

                return [
                    'id' => $quiz->id,

                    'title' => $quiz->title,

                    'description' => $quiz->description,

                    'total_mark' => (float) (
                        $quiz->questions_sum_mark ?? 0
                    ),

                    'attempts_count' => $attemptsCount,

                    'high_score' => (float) $highScore,

                    'progress_msg' => $attemptsCount > 0
                        ? "You have completed this practice {$attemptsCount} time(s)."
                        : "You haven't attempted this practice yet.",

                    'is_read' => (bool) (
                        $quiz->is_read ?? false
                    ),

                    'created_at' => $quiz->created_at
                        ->format('Y-m-d H:i'),
                ];
            })
            ->values();
    }


    public function getStudentQuizForSolving(
        int $quizId,
        int $gradeLevelId
    ): array {

        $quiz = PracticeQuiz::query()
            ->where('id', $quizId)
            ->where('grade_level_id', $gradeLevelId)
            ->where('is_active', true)
            ->with([
                'gradeSubject.subject:id,subject_name',
                'questions:id,practice_quiz_id,question_text,mark',
                'questions.options:id,question_id,option_text',
            ])
            ->first();

        if (!$quiz) {
            throw new ModelNotFoundException('Quiz not found or unauthorized.');
        }

        return [
            'id' => $quiz->id,
            'title' => $quiz->title,
            'description' => $quiz->description,
            'subject_name' => $quiz->gradeSubject?->subject?->subject_name,
            'total_mark' => (float) $quiz->questions->sum('mark'),

            'questions' => $quiz->questions->map(function ($question) {
                return [
                    'id' => $question->id,
                    'question_text' => $question->question_text,
                    'mark' => (float) $question->mark,

                    'options' => $question->options->map(function ($option) {
                        return [
                            'id' => $option->id,
                            'option_text' => $option->option_text,
                        ];
                    })->values(),
                ];
            })->values(),
        ];
    }


    public function submitAttempt(
        array $data
    ): array {

        return DB::transaction(function () use ($data) {

            $enrollmentId = (int) $data['enrollment_id'];

            $answers = collect($data['answers']);

            if ($answers->isEmpty()) {
                throw new Exception(
                    'No answers were submitted.',
                    422
                );
            }

            $questionIds = $answers
                ->pluck('question_id')
                ->map(fn($id) => (int) $id)
                ->unique()
                ->values();

            $questions = Question::query()
                ->with('options')
                ->whereIn('id', $questionIds)
                ->get()
                ->keyBy('id');


            if ($questions->count() !== $questionIds->count()) {
                throw new Exception(
                    'One or more submitted questions are invalid.',
                    422
                );
            }

            $firstQuestion = $questions->first();

            if (!$firstQuestion) {
                throw new Exception(
                    'Invalid quiz questions.',
                    422
                );
            }

            $quizId = $firstQuestion->practice_quiz_id;


            foreach ($questions as $question) {

                if (
                    (int) $question->practice_quiz_id !==
                    (int) $quizId
                ) {
                    throw new Exception(
                        'Questions from different quizzes cannot be submitted together.',
                        422
                    );
                }
            }

            $quiz = PracticeQuiz::query()
                ->where('id', $quizId)
                ->where('is_active', true)
                ->first();

            if (!$quiz) {
                throw new Exception(
                    'Quiz is not available.',
                    403
                );
            }


            $enrollment = Enrollment::query()
                ->where('id', $enrollmentId)
                ->whereHas('classRoom', function ($query) use ($quiz) {
                    $query->where(
                        'grade_level_id',
                        $quiz->grade_level_id
                    );
                })
                ->exists();

            if (!$enrollment) {
                throw new Exception(
                    'You are not authorized to submit this quiz.',
                    403
                );
            }

            $totalMark = 0;

            $earnedMark = 0;

            $detailedAnswers = [];

            foreach ($answers as $answer) {

                $questionId = (int) $answer['question_id'];

                $optionId = (int) $answer['option_id'];

                $question = $questions->get($questionId);

                if (!$question) {
                    continue;
                }

                $selectedOption = $question->options
                    ->firstWhere('id', $optionId);

                if (!$selectedOption) {
                    throw new Exception(
                        "Invalid option for question {$questionId}.",
                        422
                    );
                }

                $totalMark += (float) $question->mark;

                $correctOption = $question->options
                    ->firstWhere('is_correct', true);

                $isCorrect =
                    $correctOption &&
                    (int) $correctOption->id === $optionId;

                if ($isCorrect) {
                    $earnedMark += (float) $question->mark;
                }

                $detailedAnswers[] = [
                    'question_id' => $question->id,

                    'selected_option_id' => $optionId,

                    'is_correct' => (bool) $isCorrect,

                    'correct_option_id' =>
                        $correctOption?->id,
                ];
            }

            $attempt = StudentQuizAttempt::create([
                'practice_quiz_id' => $quizId,

                'enrollment_id' => $enrollmentId,

                'total_mark' => $totalMark,

                'earned_mark' => $earnedMark,
            ]);

            $now = now();

            $answersRecords = [];

            foreach ($detailedAnswers as $detail) {

                $answersRecords[] = [
                    'student_quiz_attempt_id' =>
                        $attempt->id,

                    'question_id' =>
                        $detail['question_id'],

                    'selected_option_id' =>
                        $detail['selected_option_id'],

                    'is_correct' =>
                        $detail['is_correct'],

                    'created_at' => $now,

                    'updated_at' => $now,
                ];
            }

            StudentQuizAttemptAnswer::insert(
                $answersRecords
            );

            return [
                'attempt_id' => $attempt->id,

                'total_mark' => (float) $totalMark,

                'earned_mark' => (float) $earnedMark,

                'percentage' => $totalMark > 0
                    ? round(
                        ($earnedMark / $totalMark) * 100,
                        2
                    )
                    : 0,

                'feedback' => $detailedAnswers,
            ];
        });
    }

    public function getLastQuizAttemptDetails(
        int $quizId,
        int $enrollmentId
    ): ?array {

        $quiz = PracticeQuiz::query()
            ->where('id', $quizId)
            ->first();

        if (!$quiz) {
            throw new ModelNotFoundException(
                'Quiz not found.'
            );
        }

        $validEnrollment = Enrollment::query()
            ->where('id', $enrollmentId)
            ->whereHas('classRoom', function ($query) use ($quiz) {
                $query->where(
                    'grade_level_id',
                    $quiz->grade_level_id
                );
            })
            ->exists();

        if (!$validEnrollment) {
            throw new Exception(
                'You are not authorized to access this quiz attempt.',
                403
            );
        }

        $lastAttempt = StudentQuizAttempt::query()
            ->where('practice_quiz_id', $quizId)
            ->where('enrollment_id', $enrollmentId)
            ->with([
                'attemptAnswers.question.options',
                'attemptAnswers.selectedOption',
            ])
            ->latest()
            ->first();

        if (!$lastAttempt) {
            return null;
        }

        return [
            'attempt_summary' => [

                'attempt_id' => $lastAttempt->id,

                'total_mark' =>
                    (float) $lastAttempt->total_mark,

                'earned_mark' =>
                    (float) $lastAttempt->earned_mark,

                'percentage' =>
                    $lastAttempt->total_mark > 0
                    ? round(
                        (
                            $lastAttempt->earned_mark /
                            $lastAttempt->total_mark
                        ) * 100,
                        2
                    )
                    : 0,

                'solved_at' =>
                    $lastAttempt->created_at
                        ->format('Y-m-d H:i:s'),
            ],

            'questions_details' =>
                $lastAttempt->attemptAnswers
                    ->map(function ($answer) {

                        $question = $answer->question;

                        $correctOption =
                            $question->options
                                ->firstWhere(
                                    'is_correct',
                                    true
                                );

                        return [

                            'question_id' =>
                                $question->id,

                            'question_text' =>
                                $question->question_text,

                            'question_mark' =>
                                (float) $question->mark,

                            'is_correct' =>
                                (bool) $answer->is_correct,

                            'selected_option_id' =>
                                $answer->selected_option_id,

                            'selected_option_text' =>
                                $answer->selectedOption?->option_text,

                            'correct_option_id' =>
                                $correctOption?->id,

                            'correct_option_text' =>
                                $correctOption?->option_text,

                            'all_options' =>
                                $question->options
                                    ->map(function ($option) {

                                        return [
                                            'id' =>
                                                $option->id,

                                            'option_text' =>
                                                $option->option_text,

                                            'is_correct' =>
                                                (bool)
                                                $option->is_correct,
                                        ];
                                    })
                                    ->values(),
                        ];
                    })
                    ->values(),
        ];
    }


    private function getBaseQueryForUser(User $user)
    {
        if (
            $user->hasRole('student') &&
            $user->student
        ) {
            $enrollment = Enrollment::query()
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
                ->with('classRoom')
                ->latest()
                ->first();

            if (
                !$enrollment ||
                !$enrollment->classRoom
            ) {
                return PracticeQuiz::query()
                    ->whereRaw('1 = 0');
            }

            return PracticeQuiz::query()
                // ✅ التعديل هنا: إضافة اسم الجدول قبل اسم العمود
                ->where(
                    'practice_quizzes.grade_level_id',
                    $enrollment->classRoom->grade_level_id
                )
                ->where('practice_quizzes.is_active', true);
        }

        /*
         * Teacher
         */
        if (
            $user->hasRole('teacher') &&
            $user->staff
        ) {
            return PracticeQuiz::query()
                // ✅ التعديل هنا أيضاً للحماية المستقبلية
                ->where(
                    'practice_quizzes.teacher_id',
                    $user->staff->id
                );
        }

        return PracticeQuiz::query()
            ->whereRaw('1 = 0');
    }


    public function unreadCount(User $user): array
    {
        return $this->getBaseQueryForUser($user)

            ->whereDoesntHave(
                'readers',
                function ($query) use ($user) {

                    $query->where(
                        'user_id',
                        $user->id
                    );
                }
            )

            ->join(
                'grade_subjects',
                'grade_subjects.id',
                '=',
                'practice_quizzes.grade_subject_id'
            )

            ->join(
                'subjects',
                'subjects.id',
                '=',
                'grade_subjects.subject_id'
            )

            ->selectRaw(
                'grade_subjects.id as grade_subject_id,
                 subjects.subject_name,
                 count(*) as unread_count'
            )

            ->groupBy(
                'grade_subjects.id',
                'subjects.subject_name'
            )

            ->get()

            ->map(function ($row) {

                return [
                    'grade_subject_id' =>
                        (int) $row->grade_subject_id,

                    'subject_name' =>
                        $row->subject_name,

                    'unread_count' =>
                        (int) $row->unread_count,
                ];
            })

            ->values()

            ->all();
    }

    public function markAllRead(
        User $user,
        int $gradeSubjectId
    ): array {

        $unreadQuizIds = $this->getBaseQueryForUser($user)

            ->where(
                'grade_subject_id',
                $gradeSubjectId
            )

            ->whereDoesntHave(
                'readers',
                function ($query) use ($user) {

                    $query->where(
                        'user_id',
                        $user->id
                    );
                }
            )

            ->pluck('id');

        if ($unreadQuizIds->isNotEmpty()) {

            $syncData = [];

            $now = now();

            foreach ($unreadQuizIds as $quizId) {

                $syncData[$quizId] = [
                    'read_at' => $now,
                ];
            }

            $user
                ->readPracticeQuizzes()
                ->syncWithoutDetaching($syncData);
        }

        return $this->unreadCount($user);
    }

    private function notifyStudentsAboutNewQuiz(
        PracticeQuiz $quiz
    ): void {

        try {

            $quiz->loadMissing([
                'gradeLevel',
                'gradeSubject.subject',
            ]);

            $gradeSubject =
                $quiz->gradeSubject;

            $gradeLevel =
                $quiz->gradeLevel;

            if (!$gradeSubject || !$gradeLevel) {
                return;
            }

            $subjectName =
                $gradeSubject->subject?->subject_name
                ?? 'Subject';

            $gradeName =
                $gradeLevel->name;

            $gradeNameString = $gradeName instanceof GradeName
                ? $gradeName->labelAr()
                : $gradeName;

            $title =
                'New Practice Quiz Available!';

            $body =
                "Your teacher added a new practice quiz in {$subjectName} for {$gradeNameString} students. Test your knowledge now!";

            $enrollments = Enrollment::query()

                ->whereHas('classRoom', function ($query) use ($quiz) {

                    $query->where(
                        'grade_level_id',
                        $quiz->grade_level_id
                    );
                })
                ->whereHas('academicYear', function ($q) {
                    $q->where('is_current', true);
                })

                ->with([
                    'student.user',
                ])

                ->get();

            if ($enrollments->isEmpty()) {
                return;
            }

            $userIdsToPush = [];

            $alertsToInsert = [];

            $now = now();

            foreach ($enrollments as $enrollment) {

                $alertsToInsert[] = [

                    'title' => $title,

                    'description' => $body,

                    'type' => 'practice_quiz',

                    'audience' => 'student',

                    'notifiable_type' =>
                        Enrollment::class,

                    'notifiable_id' =>
                        $enrollment->id,

                    'created_at' => $now,

                    'updated_at' => $now,
                ];

                if (
                    $enrollment->student &&
                    $enrollment->student->user
                ) {

                    $userIdsToPush[] =
                        $enrollment->student->user->id;
                }
            }

            if (!empty($alertsToInsert)) {

                Alert::insert(
                    $alertsToInsert
                );
            }

            if (!empty($userIdsToPush)) {

                $pushData = [

                    'type' =>
                        'new_practice_quiz',

                    'quiz_id' =>
                        (string) $quiz->id,

                    'grade_subject_id' =>
                        (string) $quiz->grade_subject_id,

                    'grade_level_id' =>
                        (string) $quiz->grade_level_id,
                ];

                SendPushNotification::dispatch(

                    array_unique(
                        $userIdsToPush
                    ),

                    $title,

                    $body,

                    $pushData
                );
            }

        } catch (Exception $e) {

            Log::error(
                'Practice Quiz Notification Error.',
                [
                    'quiz_id' => $quiz->id,

                    'error' =>
                        $e->getMessage(),
                ]
            );
        }
    }
}

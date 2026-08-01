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

                $optionsToInsert = [];
                $now = now();

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

        return $quiz;
    }
    private function notifyStudentsAboutNewQuiz(PracticeQuiz $quiz): void
    {
        try {
            $gradeSubject = GradeSubject::with('subject', 'gradeLevel')->find($quiz->grade_subject_id);

            if (!$gradeSubject) {
                return;
            }

            $subjectName = $gradeSubject->subject->subject_name ?? 'المادة';
            $gradeName = $gradeSubject->gradeLevel->name ?? 'الصف';

            $title = "تدريب جديد متاح!";
            $body = "أضاف المعلم كويز تدريبي جديد في مادة {$subjectName} لطلاب {$gradeName}. اختبر معلوماتك الآن!";

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
                    'title' => $title,
                    'body' => $body,
                    'type' => 'practice_quiz',
                    'notifiable_type' => Enrollment::class,
                    'notifiable_id' => $enrollment->id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                if ($enrollment->student && $enrollment->student->user) {
                    $userIdsToPush[] = $enrollment->student->user->id;
                }
            }

            if (!empty($alertsToInsert)) {
                Alert::insert($alertsToInsert);
            }

            if (!empty($userIdsToPush)) {
                $pushData = [
                    'type' => 'new_practice_quiz',
                    'quiz_id' => (string) $quiz->id,
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
            Log::error('فشل إرسال إشعارات الكويز التدريبي', [
                'quiz_id' => $quiz->id,
                'error' => $e->getMessage()
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
                if (!$question)
                    continue;

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
                    'question_id' => $question->id,
                    'is_correct' => $isCorrect,
                    'correct_option_id' => $correctOption ? $correctOption->id : null,
                ];
            }

            $attempt = StudentQuizAttempt::create([
                'practice_quiz_id' => $quizId,
                'enrollment_id' => $enrollmentId,
                'total_mark' => $totalMark,
                'earned_mark' => $earnedMark,
            ]);

            return [
                'attempt_id' => $attempt->id,
                'total_mark' => $totalMark,
                'earned_mark' => $earnedMark,
                'percentage' => $totalMark > 0 ? round(($earnedMark / $totalMark) * 100, 2) : 0,
                'feedback' => $feedback,
            ];
        });
    }

    private function getBaseQueryForUser(User $user)
    {

        if ($user->hasRole('student') && $user->student) {
            $classRoomId = Enrollment::where('student_id', $user->student->id)
                ->whereHas('academicYear', fn($q) => $q->where('is_current', true))
                ->latest()
                ->value('class_room_id');

            if (!$classRoomId) {
                return PracticeQuiz::query()->where('id', '<', 0);
            }

            return PracticeQuiz::whereHas('gradeSubject.classRooms', function ($q) use ($classRoomId) {
                $q->where('class_rooms.id', $classRoomId);
            });
        }

        if ($user->hasRole('teacher') && $user->staff) {
            return PracticeQuiz::whereHas('gradeSubject.teacherAssignments', function ($q) use ($user) {
                $q->where('teacher_id', $user->staff->id)
                    ->whereHas('academicYear', fn($q) => $q->where('is_current', true));
            });
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


}






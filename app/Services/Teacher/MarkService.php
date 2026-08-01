<?php

namespace App\Services\Teacher;

use App\Jobs\SendPushNotification;
use App\Models\AssessmentComponent;
use App\Models\Enrollment;
use App\Models\GradeSubject;
use App\Models\StudentMark;
use Illuminate\Support\Facades\DB;
use Exception;

class MarkService
{
    public function getGradebookMatrix(int $gradeSubjectId, int $classRoomId)
    {
        $gradeSubject = GradeSubject::with('subject', 'gradeLevel', 'assessmentComponents')
            ->findOrFail($gradeSubjectId);

        $components = $gradeSubject->assessmentComponents;
        $componentIds = $components->pluck('id')->toArray();

        $enrollments = Enrollment::where('class_room_id', $classRoomId)
            ->whereHas('academicYear', fn($q) => $q->where('is_current', true))
            ->with([
                'student.user:id,first_name,father_name,last_name,photo_url',
                'studentMarks' => function ($q) use ($componentIds) {
                    $q->whereIn('assessment_component_id', $componentIds);
                }
            ])->get();

        return [
            'subject_info' => [
                'subject_name' => $gradeSubject->subject->subject_name,
                'grade_name' => $gradeSubject->gradeLevel->name,
                'class_room_id' => $classRoomId,
            ],

            'columns' => $components->map(function ($comp) {
                return [
                    'id' => $comp->id,
                    'name' => $comp->name,
                    'type' => $comp->type,
                    'max_mark' => (float) $comp->max_mark,
                ];
            }),

            'students' => $enrollments->map(function ($enrollment) {
                $user = $enrollment->student->user;

                $marksDictionary = [];
                foreach ($enrollment->studentMarks as $mark) {
                    $marksDictionary[$mark->assessment_component_id] = [
                        'mark' => $mark->mark !== null ? (float) $mark->mark : null,
                        'notes' => $mark->notes,
                    ];
                }

                $photoUrl = $user->photo_url
                    ? url('/api/documents/photos/' . ltrim(preg_replace('/^.*?(users\/|defaults\/)/', '$1', $user->photo_url), '/'))
                    : null;

                return [
                    'enrollment_id' => $enrollment->id,
                    'student_name' => trim(preg_replace('/\s+/', ' ', "{$user->first_name} {$user->father_name} {$user->last_name}")),
                    'photo_url' => $photoUrl,
                    'marks' => (object) $marksDictionary,
                ];
            }),
        ];
    }

    public function saveMarksBulk(array $data, int $staffId)
    {
        return DB::transaction(function () use ($data, $staffId) {
            $marksToUpsert = [];
            $componentIds = collect($data['marks'])->pluck('assessment_component_id')->unique()->toArray();
            $enrollmentIds = collect($data['marks'])->pluck('enrollment_id')->unique()->toArray();

            $components = AssessmentComponent::with('gradeSubject.subject')
                ->whereIn('id', $componentIds)
                ->get()
                ->keyBy('id');

            $existingMarks = StudentMark::whereIn('enrollment_id', $enrollmentIds)
                ->whereIn('assessment_component_id', $componentIds)
                ->get()
                ->keyBy(fn($item) => $item->enrollment_id . '_' . $item->assessment_component_id);

            $notificationsToDispatch = [];

            foreach ($data['marks'] as $markData) {
                $compId = $markData['assessment_component_id'];
                $enrollmentId = $markData['enrollment_id'];
                $providedMark = $markData['mark'] ?? null;
                $component = $components->get($compId);

                if ($providedMark !== null && $component) {
                    if ($providedMark > $component->max_mark) {
                        throw new Exception("The entered mark ({$providedMark}) exceeds the maximum limit ({$component->max_mark}).", 422);
                    }

                    if (in_array($component->type, ['quiz1', 'quiz2', 'exam'])) {
                        $markKey = $enrollmentId . '_' . $compId;
                        $isUpdate = $existingMarks->has($markKey);

                        $notificationsToDispatch[] = [
                            'enrollment_id' => $enrollmentId,
                            'component' => $component,
                            'isUpdate' => $isUpdate
                        ];
                    }
                }

                $marksToUpsert[] = [
                    'enrollment_id' => $enrollmentId,
                    'assessment_component_id' => $compId,
                    'teacher_id' => $staffId,
                    'mark' => $providedMark,
                    'notes' => $markData['notes'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            StudentMark::upsert(
                $marksToUpsert,
                ['enrollment_id', 'assessment_component_id'],
                ['mark', 'notes', 'teacher_id', 'updated_at']
            );

            if (!empty($notificationsToDispatch)) {
                $this->dispatchNotifications($notificationsToDispatch);
            }

            return true;
        });
    }

    private function dispatchNotifications(array $notificationsData): void
    {
        $enrollmentIds = collect($notificationsData)->pluck('enrollment_id')->unique()->toArray();

        $enrollments = Enrollment::whereIn('id', $enrollmentIds)
            ->with(['student.user:id', 'student.guardian.user:id'])
            ->get()
            ->keyBy('id');

        foreach ($notificationsData as $data) {
            $enrollment = $enrollments->get($data['enrollment_id']);
            if (!$enrollment || !$enrollment->student)
                continue;

            $userIds = [];
            if ($enrollment->student->user_id) {
                $userIds[] = $enrollment->student->user_id;
            }
            if ($enrollment->student->guardian && $enrollment->student->guardian->user_id) {
                $userIds[] = $enrollment->student->guardian->user_id;
            }

            if (!empty($userIds)) {
                $component = $data['component'];
                $subjectName = $component->gradeSubject?->subject?->subject_name ?? 'Subject';
                $isUpdate = $data['isUpdate'];

                $title = $isUpdate
                    ? "Mark Update: {$subjectName}"
                    : "New Mark: {$subjectName}";

                $componentTypeName = $this->getComponentTypeName($component->type);
                $body = $isUpdate
                    ? "The {$componentTypeName} mark for {$subjectName} has been updated."
                    : "A new {$componentTypeName} mark for {$subjectName} has been recorded.";

                $type = $isUpdate ? 'update_mark' : 'new_mark';

                SendPushNotification::dispatch(
                    array_unique($userIds),
                    $title,
                    $body,
                    [
                        'type' => $type,
                        'enrollment_id' => $enrollment->id,
                        'assessment_component_id' => $component->id,
                    ]
                )->afterCommit();
            }
        }
    }

    private function getComponentTypeName(string $type): string
    {
        return match ($type) {
            'quiz1' => 'First Quiz',
            'quiz2' => 'Second Quiz',
            'exam' => 'Exam',
            default => 'Test'
        };
    }
}

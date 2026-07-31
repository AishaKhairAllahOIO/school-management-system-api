<?php

namespace App\Services\Teacher;

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
                'subject_name'    => $gradeSubject->subject->subject_name,
                'grade_name'      => $gradeSubject->gradeLevel->name,
                'class_room_id'   => $classRoomId,
            ],

            'columns' => $components->map(function ($comp) {
                return [
                    'id'       => $comp->id,
                    'name'     => $comp->name,
                    'type'     => $comp->type,
                    'max_mark' => (float) $comp->max_mark,
                ];
            }),

            'students' => $enrollments->map(function ($enrollment) {
                $user = $enrollment->student->user;

                $marksDictionary = [];
                foreach ($enrollment->studentMarks as $mark) {
                    $marksDictionary[$mark->assessment_component_id] = [
                        'mark'  => $mark->mark !== null ? (float) $mark->mark : null,
                        'notes' => $mark->notes,
                    ];
                }

                $photoUrl = $user->photo_url
                    ? url('/api/documents/photos/' . ltrim(preg_replace('/^.*?(users\/|defaults\/)/', '$1', $user->photo_url), '/'))
                    : null;

                return [
                    'enrollment_id' => $enrollment->id,
                    'student_name'  => trim(preg_replace('/\s+/', ' ', "{$user->first_name} {$user->father_name} {$user->last_name}")),
                    'photo_url'     => $photoUrl,
                    'marks'         => (object) $marksDictionary,
                ];
            }),
        ];
    }

    public function saveMarksBulk(array $data, int $staffId)
    {
        return DB::transaction(function () use ($data, $staffId) {
            $marksToUpsert = [];

            $componentIds = collect($data['marks'])->pluck('assessment_component_id')->unique()->toArray();
            $componentsMaxMarks = AssessmentComponent::whereIn('id', $componentIds)->pluck('max_mark', 'id');

            foreach ($data['marks'] as $markData) {
                $compId = $markData['assessment_component_id'];
                $providedMark = $markData['mark'] ?? null;

                if ($providedMark !== null && isset($componentsMaxMarks[$compId])) {
                    if ($providedMark > $componentsMaxMarks[$compId]) {
                        throw new Exception("العلامة المدخلة ({$providedMark}) تتجاوز النهاية العظمى ({$componentsMaxMarks[$compId]}).", 422);
                    }
                }

                $marksToUpsert[] = [
                    'enrollment_id'           => $markData['enrollment_id'],
                    'assessment_component_id' => $compId,
                    'teacher_id'              => $staffId,
                    'mark'                    => $providedMark,
                    'notes'                   => $markData['notes'] ?? null,
                    'created_at'              => now(),
                    'updated_at'              => now(),
                ];
            }

            StudentMark::upsert(
                $marksToUpsert,
                ['enrollment_id', 'assessment_component_id'],
                ['mark', 'notes', 'teacher_id', 'updated_at']
            );

            return true;
        });
    }
}

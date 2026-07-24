<?php

namespace App\Services\Teacher;

use App\Models\ClassRoom;
use App\Models\GradeLevel;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Collection;
class TeacherDropdownService
{

    public function getSubjectsTree(User $user, ?int $subjectId = null): Collection
    {
        if (!$user->staff) {
            return collect();
        }

        $assignments = $user->staff->teacherAssignments()
            ->whereHas('academicYear', function ($q) {
                $q->where('is_current', true);
            })
            ->when($subjectId, function ($query) use ($subjectId) {
                $query->whereHas('gradeSubject', function ($q) use ($subjectId) {
                    $q->where('subject_id', $subjectId);
                });
            })
            ->with([
                'gradeSubject.subject:id,subject_name',
                'classRoom:id,name,grade_level_id',
                'classRoom.gradeLevel:id,name'
            ])
            ->get();

        return $assignments->groupBy('gradeSubject.subject_id')->map(function ($subjectGroup) {

            $subject = $subjectGroup->first()->gradeSubject->subject;

            return [
                'subject_id' => $subject->id,
                'subject_name' => $subject->subject_name,
                'grades' => $subjectGroup->groupBy('classRoom.grade_level_id')->map(function ($gradeGroup) {

                    $grade = $gradeGroup->first()->classRoom->gradeLevel;

                    return [
                        'grade_level_id' => $grade->id ?? null,
                        'grade_level_name' => $grade->name ?? '',
                        'classrooms' => $gradeGroup->map(function ($assignment) {
                            return [
                                'class_room_id' => $assignment->classRoom->id,
                                'class_room_name' => $assignment->classRoom->name,
                            ];
                        })->unique('class_room_id')->values(),
                    ];
                })->values(),
            ];
        })->values();
    }

}

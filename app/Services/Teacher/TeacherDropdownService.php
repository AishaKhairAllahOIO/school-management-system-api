<?php

namespace App\Services\Teacher;

use App\Models\Enrollment;
use App\Models\User;
use App\Support\FileUrl;
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
                        // 👇 السطر الذهبي الذي أضفناه لربط هذه الشجرة بنظام الكويزات
                        'grade_subject_id' => $gradeGroup->first()->grade_subject_id,

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
    public function getClassroomStudents(int $classRoomId): Collection
    {
        return Enrollment::where('class_room_id', $classRoomId)
            ->whereHas('academicYear', fn($q) => $q->where('is_current', true))
            ->with(['student.user:id,first_name,father_name,last_name,photo_url'])
            ->get()
            ->map(function ($enrollment) {
                return [
                    'enrollment_id' => $enrollment->id,
                    'student_id' => $enrollment->student_id,
                    'full_name' => $enrollment->student?->user
                        ? ($enrollment->student->user->first_name . ' ' . $enrollment->student->user->father_name . ' ' . $enrollment->student->user->last_name)
                        : 'طالب غير معرف',
                    'personal_photo' => FileUrl::make(
                        $enrollment->student?->user->photo_url,
                        config('filesystems.public_disk')
                    ),
                ];
            });
    }
    
}

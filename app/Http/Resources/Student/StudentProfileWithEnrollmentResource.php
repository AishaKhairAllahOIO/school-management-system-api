<?php

namespace App\Http\Resources\Student;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Support\FileUrl;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentProfileWithEnrollmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $enrollment = $this;
        $student = $enrollment->student;
        $studentUser = $student->user;
        $guardian = $student->guardian;
        $guardianUser = $guardian ? $guardian->user : null;

        return [
            'student' => [
                'id' => (string) $student->id,
                'firstName' => $studentUser->first_name,
                'lastName' => $studentUser->last_name,
                'fullName' => trim(preg_replace('/\s+/', ' ', "{$studentUser->first_name} {$studentUser->father_name} {$studentUser->last_name}")),
                'fatherName' => $studentUser->father_name,
                'motherName' => $studentUser->mother_name,
                'birthDate' => $studentUser->birth_date ? Carbon::parse($studentUser->birth_date)->toDateString() : null,
                'birthPlace' => $studentUser->birth_place,
                'gender' => $studentUser->gender,
                'nationality' => $studentUser->nationality,
                'address' => $studentUser->address,
                'phoneNumber' => $studentUser->phone_number,
                'photoUrl' => FileUrl::endpoint(
                    $studentUser->photo_url
                ),
            ],

            'guardian' => $guardianUser ? [
                'id' => (string) $guardian->id,
                'firstName' => $guardianUser->first_name,
                'lastName' => $guardianUser->last_name,
                'fullName' => trim(preg_replace('/\s+/', ' ', "{$guardianUser->first_name} {$guardianUser->father_name} {$guardianUser->last_name}")),
                'fatherName' => $guardianUser->father_name,
                'motherName' => $guardianUser->mother_name,
                'birthDate' => $guardianUser->birth_date ? Carbon::parse($guardianUser->birth_date)->toDateString() : null,
                'birthPlace' => $guardianUser->birth_place,
                'gender' => $guardianUser->gender,
                'nationality' => $guardianUser->nationality,
                'address' => $guardianUser->address,
                'phoneNumber' => $guardianUser->phone_number,
                'photoUrl' => FileUrl::endpoint(
                    $guardianUser->photo_url
                ),
            ] : null,

            'enrollment' => [
                'id' => (string) $enrollment->id,
                'academicYearId' => (string) $enrollment->academic_year_id,
                'gradeId' => (string) $enrollment->grade_level_id,
                'classroomId' => (string) $enrollment->class_room_id,

                'academicYear' => $enrollment->academicYear ? [
                    'id' => (string) $enrollment->academicYear->id,
                    'name' => $enrollment->academicYear->year_name,
                    'startDate' => $enrollment->academicYear->start_date ? Carbon::parse($enrollment->academicYear->start_date)->toDateString() : null,
                    'endDate' => $enrollment->academicYear->end_date ? Carbon::parse($enrollment->academicYear->end_date)->toDateString() : null,
                ] : null,

                'grade' => $enrollment->gradeLevel ? [
                    'id' => (string) $enrollment->gradeLevel->id,
                    'name' => $enrollment->gradeLevel->name,
                    'level' => $enrollment->gradeLevel->level,
                ] : null,

                'classroom' => $enrollment->classRoom ? [
                    'id' => (string) $enrollment->classRoom->id,
                    'name' => $enrollment->classRoom->name,
                ] : null,

                'enrollmentStatus' => $enrollment->enrollment_status,
                'enrollmentDate' => $enrollment->enrollment_date ? Carbon::parse($enrollment->enrollment_date)->toDateString() : null,
            ],
        ];
    }
}

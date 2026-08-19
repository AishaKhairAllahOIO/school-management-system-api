<?php

namespace App\Services\User;


use App\ApiResource;
use App\Models\Semester;
use App\Support\FileUrl;
use Illuminate\Http\Exceptions\HttpResponseException;

class UserService
{

    use ApiResource;

    public function getStudent($user)
    {
        $student = $user->student;

        if (!$student) {
            throw new HttpResponseException($this->errorResponse("Student not found.", 404));
        }

        $currentEnrollment = $student->enrollments()
            ->whereHas('academicYear', function ($q) {
                $q->whereDate('start_date', '<=', now())
                    ->whereDate('end_date', '>=', now());
            })
            ->with(['gradeLevel', 'classRoom'])
            ->first();

        if (!$currentEnrollment) {
            throw new HttpResponseException($this->errorResponse("No active enrollment found for the student in the current academic year.", 404));
        }

        $semester = Semester::where('academic_year_id', $currentEnrollment->academic_year_id)
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->first();

        if (!$semester) {
            $semester = Semester::where('academic_year_id', $currentEnrollment->academic_year_id)->first();
        }

        if (!$semester) {
            throw new HttpResponseException($this->errorResponse("No active semester found for the student in the current academic year.", 404));
        }

        return [
            'academic_info' =>
                [
                    'grade_name' => $currentEnrollment->gradeLevel->name ?? 'غير محدد',
                    'semester_name' => $semester->semester_name ?? 'غير محدد',
                    'name' => $currentEnrollment->classRoom->name ?? 'غير محدد',
                ],
        ];
    }
    public function getGuardian($user)
    {
        $guardian = $user->guardian;

        if (!$guardian) {
            throw new HttpResponseException($this->errorResponse("Guardian not found.", 404));
        }

        $students = $guardian->students()->with([
            'enrollments' => function ($q) {
                $q->whereHas('academicYear', function ($q) {
                    $q->whereDate('start_date', '<=', now())
                        ->whereDate('end_date', '>=', now());
                })->with(['gradeLevel', 'classRoom']);
            }
        ])->get();

        $childrenCards = $students->map(function ($student) {
            $currentEnrollment = $student->enrollments->first();

            $gradeName = 'غير مسجل حالياً';
            $className = 'غير محدد';
            $semesterName = 'غير محدد';

            if ($currentEnrollment) {
                $gradeName = $currentEnrollment->gradeLevel->name ?? 'غير محدد';
                $className = $currentEnrollment->classRoom->name ?? 'غير محدد';

                $semester = Semester::where('academic_year_id', $currentEnrollment->academic_year_id)
                    ->whereDate('start_date', '<=', now())
                    ->whereDate('end_date', '>=', now())
                    ->first();

                $semesterName = $semester->semester_name ?? 'غير محدد';
            }

            'user' . 'student';

            return [
                'id' => $student->id,
                'first_name' => $student->user->first_name,
                'father_name' => $student->user->father_name,
                'last_name' => $student->user->last_name,
                'gender' => $student->user->gender,
                'student_photo_url' => FileUrl::make(
                    $student->user->photo_url,
                    config('filesystems.public_disk')
                ),

                'grade_name' => $gradeName,
                'class_room_name' => $className,
            ];
        })->toArray();

        return [
            'children_cards' => $childrenCards
        ];
    }

}







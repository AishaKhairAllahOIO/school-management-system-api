<?php

namespace App\Services\User;


use App\ApiResource;
use App\Http\Resources\Auth\UserResource;
use App\Models\Semester;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
class UserService
{

    use ApiResource;

    public function getStudent($user)
    {
        $student = $user->student;

        if (!$student) {
            throw new HttpResponseException($this->errorResponse('هذا الحساب غير مسجل كطالب في النظام.', 404));
        }

        $currentEnrollment = $student->enrollments()
            ->whereHas('academicYear', function ($q) {
                $q->whereDate('start_date', '<=', now())
                    ->whereDate('end_date', '>=', now());
            })
            ->with(['gradeLevel', 'classRoom'])
            ->first();

        if (!$currentEnrollment) {
            throw new HttpResponseException($this->errorResponse('لا يوجد تسجيل أكاديمي نشط للطالب في السنة الدراسية الحالية.', 404));
        }

        $semester = Semester::where('academic_year_id', $currentEnrollment->academic_year_id)
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->first();

        if (!$semester) {
            $semester = Semester::where('academic_year_id', $currentEnrollment->academic_year_id)->first();
        }

        if (!$semester) {
            throw new HttpResponseException($this->errorResponse('لا يوجد فصل دراسي معرف لهذه السنة الأكاديمية.', 404));
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
            throw new HttpResponseException($this->errorResponse('هذا الحساب غير مسجل كولي أمر في النظام.', 404));
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
                'student_photo_url' => $student->user->photo_url
                    ? url('/api/documents/photos/' . ltrim(preg_replace('/^.*?(users\/|defaults\/)/', '$1', $student->user->photo_url), '/'))
                    : null,
                'grade_name' => $gradeName,
                'class_room_name' => $className,
            ];
        })->toArray();

        return [
            'children_cards' => $childrenCards
        ];
    }


    
}







<?php

namespace App\Http\Resources\Student;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentEnrollmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // العلاقات محمّلة مسبقاً من الـ Service
        $student      = $this->student; 
        $guardian     = $student->guardian; 
        $guardianUser = $guardian->user; 
        $enrollment   = $student->enrollments->last();

        return [
            'student' => [
                'id'            => $student->id,
                'userId'        => $this->id,
                'full_name'       => $this->first_name . ' ' . $this->last_name,
                'fatherName'    => $this->father_name,
                'motherName'    => $this->mother_name,
                'birthDate'     => $this->birth_date,
                'birthPlace'    => $this->birth_place,
                'address'       => $this->address,
                'gender'        => $this->gender,
                'nationality'   => $this->nationality,
                'phoneNumber'   => $this->phone_number,
                'photoUrl'      => $this->photo_url,
                'accountStatus' => $this->account_status,
            ],
            'guardian' => [
                'id'          => $guardian->id,
                'userId'      => $guardianUser->id,
                'full_name'    => $guardianUser->first_name . ' ' . $guardianUser->last_name,
                'fatherName'  => $guardianUser->father_name,
                'motherName'  => $guardianUser->mother_name,
                'birthDate'   => $guardianUser->birth_date,
                'birthPlace'  => $guardianUser->birth_place,
                'address'     => $guardianUser->address,
                'gender'      => $guardianUser->gender,
                'nationality' => $guardianUser->nationality,
                'phoneNumber' => $guardianUser->phone_number,
            ],
            'enrollment' => $enrollment ? [
                'id'               => $enrollment->id,
                'academicYearId'   => $enrollment->academic_year_id,
                'gradeLevelId'     => $enrollment->grade_level_id,
                'classRoomId'      => $enrollment->class_room_id,
                'enrollmentStatus' => $enrollment->enrollment_status,
                'academicResult'   => $enrollment->academic_result,
            ] : null,
        ];
    }
}
<?php

namespace App\Http\Resources\Student;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentProfileWithEnrollmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $enrollment   = $this; 
        $student      = $this->student;
        $studentUser  = $student->user; // البيانات الشخصية للطالب من جدول users
        $guardian     = $student->guardian;
        $guardianUser = $guardian ? $guardian->user : null; // البيانات الشخصية للولي (إن وجد)

        return [
            'student' => [
                'id'            => $student->id,             // الآي دي الخاص بجدول الطلاب
                'userId'        => $studentUser->id,         // الآي دي الخاص بجدول المستخدمين
                'fullName'      => $studentUser->first_name . ' ' . $studentUser->last_name,
                'fatherName'    => $studentUser->father_name,
                'motherName'    => $studentUser->mother_name,
                'birthDate'     => $studentUser->birth_date,
                'birthPlace'    => $studentUser->birth_place,
                'address'       => $studentUser->address,
                'gender'        => $studentUser->gender,
                'nationality'   => $studentUser->nationality,
                'phoneNumber'   => $studentUser->phone_number,
                'photoUrl'      => $studentUser->photo_url,
                'accountStatus' => $studentUser->account_status,
                'recordStatus'  => $studentUser->record_status,
            ],
            
            // بيانات ولي الأمر الشخصية
            'guardian' => $guardianUser ? [
                'id'            => $guardian->id,
                'userId'        => $guardianUser->id,
                'fullName'      => $guardianUser->first_name . ' ' . $guardianUser->last_name,
                'fatherName'    => $guardianUser->father_name,
                'motherName'    => $guardianUser->mother_name,
                'birthDate'     => $guardianUser->birth_date,
                'birthPlace'    => $guardianUser->birth_place,
                'address'       => $guardianUser->address,
                'gender'        => $guardianUser->gender,
                'nationality'   => $guardianUser->nationality,
                'phoneNumber'   => $guardianUser->phone_number,
                'photoUrl'      => $guardianUser->photo_url,
                'accountStatus' => $guardianUser->account_status,
                'recordStatus'  => $guardianUser->record_status,
            ] : null,
            
            // بيانات القيد الأكاديمي الحالية (Enrollment)
            'enrollment' => [
                'id'               => (string) $enrollment->id,
                'studentId'        => (string) $enrollment->student_id,
                'academicYearId'   => (string) $enrollment->academic_year_id,
                'gradeId'          => (string) $enrollment->grade_level_id, 
                'classroomId'      => (string) $enrollment->class_room_id,  
                'enrollmentStatus' => $enrollment->enrollment_status,       
                'enrollmentDate'   => $enrollment->enrollment_date?->toDateString(),
                'completedAt'      => $enrollment->completed_at?->toIso8601String(),
                'createdAt'        => $enrollment->created_at?->toIso8601String(),
                'updatedAt'        => $enrollment->updated_at?->toIso8601String(),
            ],
        ];
    }
}
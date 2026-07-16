<?php

namespace App\Http\Resources\Student;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentProfileWithEnrollmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
$enrollment   = $this;
        $student      = $enrollment->student;
        $studentUser  = $student->user; 
        $guardian     = $student->guardian;
        $guardianUser = $guardian ? $guardian->user : null; 

        return [
            'student' => [
                'id'            => (string) $student->id,
                'userId'        => (string) $studentUser->id,
                // تنظيف الاسم من أي مسافات زائدة
                'fullName'      => trim(preg_replace('/\s+/', ' ', $studentUser->first_name . ' ' . $studentUser->father_name . ' ' . $studentUser->last_name)),
                'fatherName'    => $studentUser->father_name,
                'motherName'    => $studentUser->mother_name,
                'birthDate'     => $studentUser->birth_date,
                'birthPlace'    => $studentUser->birth_place,
                'address'       => $studentUser->address,
                'gender'        => $studentUser->gender,
                'nationality'   => $studentUser->nationality,
                'phoneNumber'   => $studentUser->phone_number,
                'photoUrl'      => $studentUser->photo_url ? (str_starts_with($studentUser->photo_url, 'http') ? $studentUser->photo_url : asset('storage/' . $studentUser->photo_url)) : null,
                'accountStatus' => $studentUser->account_status,
                'recordStatus'  => $studentUser->record_status,
            ],
            
            'guardian' => $guardianUser ? [
                'id'            => (string) $guardian->id,
                'userId'        => (string) $guardianUser->id,
                'fullName'      => trim(preg_replace('/\s+/', ' ', $guardianUser->first_name . ' ' . $guardianUser->father_name . ' ' . $guardianUser->last_name)),
                'fatherName'    => $guardianUser->father_name,
                'motherName'    => $guardianUser->mother_name,
                'birthDate'     => $guardianUser->birth_date,
                'birthPlace'    => $guardianUser->birth_place,
                'address'       => $guardianUser->address,
                'gender'        => $guardianUser->gender,
                'nationality'   => $guardianUser->nationality,
                'phoneNumber'   => $guardianUser->phone_number,
                'photoUrl'      => $guardianUser->photo_url ? (str_starts_with($guardianUser->photo_url, 'http') ? $guardianUser->photo_url : asset('storage/' . $guardianUser->photo_url)) : null,
                'accountStatus' => $guardianUser->account_status,
                'recordStatus'  => $guardianUser->record_status,
            ] : null,
            
            // 💡 التعديل الجوهري: إضافة أسماء (الصف، الشعبة، العام) بداخل القيد
            'enrollment' => [
                'id'               => (string) $enrollment->id,
                'studentId'        => (string) $enrollment->student_id,
                'academicYearId'   => (string)$enrollment->academic_year_id,
                'gradeId'          =>(string)$enrollment->grade_level_id,
                'classroomId'      =>(string)$enrollment->class_room_id,
            
                'enrollmentStatus' => $enrollment->enrollment_status,       
                'enrollmentDate'   => $enrollment->enrollment_date ? \Carbon\Carbon::parse($enrollment->enrollment_date)->toDateString() : null,
                'completedAt'      => $enrollment->completed_at ? \Carbon\Carbon::parse($enrollment->completed_at)->toIso8601String() : null,
                
                'isDeleted'        => $enrollment->trashed(),
                'deletedAt'        => $enrollment->deleted_at ? $enrollment->deleted_at->toIso8601String() : null,
                
                'createdAt'        => $enrollment->created_at ? $enrollment->created_at->toIso8601String() : null,
                'updatedAt'        => $enrollment->updated_at ? $enrollment->updated_at->toIso8601String() : null,
            ]?:null,
        ];
    }
}
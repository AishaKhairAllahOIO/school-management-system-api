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
                'id'            => $student->id, // الآي دي الخاص بجدول الطلاب
                'userId'        => $this->id,    // الآي دي الخاص بجدول المستخدمين
                'fullName'      => $this->first_name . ' ' . $this->last_name, // camelCase
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
                'recordStatus'  => $this->record_status,
            ],
            'guardian' => [
                'id'          => $guardian->id,
                'userId'      => $guardianUser->id,
                'fullName'    => $guardianUser->first_name . ' ' . $guardianUser->last_name,
                'fatherName'  => $guardianUser->father_name,
                'motherName'  => $guardianUser->mother_name,
                'birthDate'   => $guardianUser->birth_date,
                'birthPlace'  => $guardianUser->birth_place,
                'address'     => $guardianUser->address,
                'gender'      => $guardianUser->gender,
                'nationality' => $guardianUser->nationality,
                'phoneNumber' => $guardianUser->phone_number,
                'photoUrl'    => $guardianUser->photo_url, // تمت إضافة صورة ولي الأمر
                'accountStatus' => $guardianUser->account_status,
                'recordStatus'  => $guardianUser->record_status,
            ],
            
            // 🚨 هنا السحر: المطابقة الحرفية مع واجهة الـ TypeScript الخاصة بالفرونت إند
            'enrollment' => $enrollment ? [
                'id'               => (string) $enrollment->id,
                'studentId'        => (string) $enrollment->student_id,
                'academicYearId'   => (string) $enrollment->academic_year_id,
                'gradeId'          => (string) $enrollment->grade_level_id, // لاحظوا تحويل الاسم لتطابق الواجهة
                'classroomId'      => (string) $enrollment->class_room_id,  // لاحظوا تحويل الاسم لتطابق الواجهة
                'enrollmentStatus' => $enrollment->enrollment_status,       // سيعيد 'suspended' افتراضياً
                'enrollmentDate'   => $enrollment->enrollment_date?->toDateString(),
                'completedAt'      => $enrollment->completed_at?->toIso8601String(),
                'createdAt'        => $enrollment->created_at?->toIso8601String(),
                'updatedAt'        => $enrollment->updated_at?->toIso8601String(),
            ] : null,
        ];
    }
}
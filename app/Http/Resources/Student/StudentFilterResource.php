<?php

namespace App\Http\Resources\Student;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentFilterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
       $enrollment = $this->enrollments->first(); 

        return [
            'studentId'    => (string) $this->id,
            'userId'       => (string) $this->user_id,
            'guardianId'   => (string) $this->guardian_id,
            'enrollmentId' => $enrollment ? (string) $enrollment->id : null,
            
            // الاسم الثلاثي باستخدام الـ Nullsafe Operator للحماية من الانهيار لو كان المستخدم محذوفاً
            'fullName'     => trim($this->user?->first_name . ' ' . $this->user?->father_name . ' ' . $this->user?->last_name),
            
            // بيانات الصف (استخدمنا ?-> لحماية الكود من الانهيار إذا كان الصف محذوفاً)
            'grade' => [
                'id'    => $enrollment ? (string) $enrollment->grade_level_id : null,
                'name'  => $enrollment?->gradeLevel?->name,
                'level' => $enrollment?->gradeLevel?->level,
            ],
            
            // بيانات الشعبة
            'classroom' => [
                'id'    => $enrollment ? (string) $enrollment->class_room_id : null,
                'name'  => $enrollment?->classRoom?->name,
            ],
            
            // يمكنك إضافة حالة التسجيل لتظهر في الجدول للفرونت إند
            'status' => $enrollment ? $enrollment->enrollment_status : 'none',
        ];
    }
}
<?php

namespace App\Http\Resources\Student;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // $this هنا يمثل كائن (Student)
        $studentUser  = $this->user; // البيانات الشخصية للطالب من جدول users
        $guardian     = $this->guardian; // القيد الخاص بولي الأمر
        $guardianUser = $guardian ? $guardian->user : null; // البيانات الشخصية للولي
        
        return [
            'student' => [
                'id'            => $this->id, // الآي دي الخاص بجدول الطلاب
                'userId'        => $studentUser->id, // الآي دي الخاص بجدول المستخدمين
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
            // نستخدم التحقق الشرطي (Optional Chaining/Ternary) لتفادي الأخطاء إذا لم يكن هناك ولي أمر لأي سبب
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
        ];
    }
}
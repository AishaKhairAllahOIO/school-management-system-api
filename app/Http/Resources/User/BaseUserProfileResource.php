<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BaseUserProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
      
        $user = $this->user;

        // حماية إضافية: في حال (لسبب ما) لم يكن هناك يوزر مرتبط
        if (!$user) {
            return [
                'id' => $this->id,
                'message' => 'No user profile attached to this record.'
            ];
        }

        return [
            'id'             => $this->id,               // ID الخاص بجدول Student أو الموظف
            'user_id'        => $user->id,               // ID الخاص بجدول Users
            'full_name'      => $user->first_name . ' ' . $user->last_name,
            'father_name'    => $user->father_name,
            'mother_name'    => $user->mother_name,
            'birth_date'     => $user->birth_date,
            'birth_place'    => $user->birth_place,
            'address'        => $user->address,
            'phone_number'   => $user->phone_number,
            'nationality'    => $user->nationality,   
            'gender'         => $user->gender,
            'photo_url'      => $user->photo_url? (str_starts_with($user->photo_url, 'http') ? $user->photo_url : asset('storage/' . $user->photo_url)) : null,        // اريد ان اجلب الصورة من الstorage
            
            'account_status' => $user->account_status,   // تعديل: جلب الحالة من الـ user
            'record_status'  => $user->record_status,    // تعديل: جلب حالة السجل من الـ user
        ];
    }
}
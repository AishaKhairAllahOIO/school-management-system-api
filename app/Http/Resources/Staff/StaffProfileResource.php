<?php

namespace App\Http\Resources\Staff;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StaffProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $this->user;

        return [
            'id'               => (string) $this->id,
            'userId'           => (string) $user->id,
            'role'             => $user->getRoleNames(),
            
            'fullName'         => trim($user->first_name . ' ' . $user->father_name . ' ' . $user->last_name),
            'firstName'        => $user->first_name,
            'lastName'         => $user->last_name,
            'fatherName'      =>$user->father_name,
            'motherName'      =>$user->mother_name,
            'phoneNumber'      => $user->phone_number,
            'email'            =>$user->email ?:null,
            'gender'           => $user->gender,
            'birthDate'        => $user->birth_date,
            'birthPlace'       =>$user->birth_place,
            'address'          => $user->address,
            'photoUrl'         => $user->photo_url ? (str_starts_with($user->photo_url, 'http') ? $user->photo_url : asset('storage/' . $user->photo_url)) : null,
            'accountStatus'    => $user->account_status,
            
            'degree'           => $this->degree??null,
            'specialization'   => $this->specialization??null,
            'university'       => $this->university??null,
            'graduationYear'   => (int) $this->graduation_year??null,
            'hireDate'         => $this->hire_date,
            'experienceYears'  => (int) $this->experience_years??null,
            'serviceType'      =>$this->service_type?? null,
            'isDeleted'       =>$this->trashed(),
            'deletedAt' =>$this->deleted_at?->toIso8601String(),

            
            'createdAt'        => $this->created_at?->toIso8601String(),
        ];
    }
}
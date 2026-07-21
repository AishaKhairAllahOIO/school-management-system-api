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
            'phoneNumber'      => $user->phone_number,
            'email'            =>$user->email ?:null,
            'gender'           => $user->gender,
            'birthDate'        => $user->birth_date,
            'address'          => $user->address,
            'photoUrl'         => $user->photo_url ? (str_starts_with($user->photo_url, 'http') ? $user->photo_url : asset('storage/' . $user->photo_url)) : null,
            'accountStatus'    => $user->account_status,
            
            'degree'           => $this->degree,
            'specialization'   => $this->specialization,
            'university'       => $this->university,
            'graduationYear'   => (int) $this->graduation_year,
            'hireDate'         => $this->hire_date,
            'experienceYears'  => (int) $this->experience_years,
            'serviceType'      =>$this->service_type,
            'isDeleted'       =>$this->trashed(),
            'deletedAt' =>$this->deleted_at?->toIso8601String(),

            
            'createdAt'        => $this->created_at?->toIso8601String(),
        ];
    }
}
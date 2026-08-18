<?php

namespace App\Http\Resources\Staff;

use Illuminate\Http\Request;
use App\Support\FileUrl;
use Illuminate\Http\Resources\Json\JsonResource;

class StaffProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $this->user;

        return [
            'id' => (string) $this->id,
            'userId' => (string) $user->id,
            'role' => $user->getRoleNames(),

            'fullName' => trim(preg_replace('/\s+/', ' ', "{$user->first_name} {$user->father_name} {$user->last_name}")),
            'firstName' => $user->first_name,
            'lastName' => $user->last_name,
            'fatherName' => $user->father_name,
            'motherName' => $user->mother_name,
            'phoneNumber' => $user->phone_number,
            'email' => $user->email ?: null,
            'gender' => $user->gender,
            'birthDate' => $user->birth_date,
            'birthPlace' => $user->birth_place,
            'address' => $user->address,
            'nationality' => $user->nationality,
            'photoUrl' => FileUrl::make(
                $user->photo_url,
                config('filesystems.default')
            ),

            'accountStatus' => $user->account_status,

            'degree' => $this->degree ?? null,
            'specialization' => $this->specialization ?? null,
            'university' => $this->university ?? null,
            'graduationYear' => $this->graduation_year ? (int) $this->graduation_year : null,
            'hireDate' => $this->hire_date,
            'experienceYears' => $this->experience_years ? (int) $this->experience_years : null,
            'serviceType' => $this->service_type ?? null,

            'isDeleted' => $this->trashed(),
            'deletedAt' => $this->deleted_at?->toIso8601String(),
            'createdAt' => $this->created_at?->toIso8601String(),
        ];
    }
}

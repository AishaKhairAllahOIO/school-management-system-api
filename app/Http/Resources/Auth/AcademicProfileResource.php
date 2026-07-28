<?php

namespace App\Http\Resources\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AcademicProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'full_name' => $this->first_name . ' ' . $this->last_name,
            'father_name' => $this->father_name,
            'mother_name' => $this->mother_name,
            'birth_date' => $this->birth_date,
            'birth_place' => $this->birth_place,
            'gender' => $this->gender,
            'nationality' => $this->nationality,
            'address' => $this->address,
            'photo' => $this->photo_url
    ? url('/api/documents/photos/' . ltrim(preg_replace('/^.*?(users\/|defaults\/)/', '$1', $this->photo_url), '/'))
    : null,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'is_active' => $this->account_status !== 'disabled',
            'record_status' => $this->record_status,

            'degree' => $this->staff?->degree,
            'specialization' => $this->staff?->specialization,
            'university' => $this->staff?->university,
            'graduation_year' => $this->staff?->graduation_year,
            'experience_years' => $this->staff?->experience_years,
            'hire_date' => $this->staff?->hire_date,

            'roles' => $this->getRoleNames(),
        ];
    }
}

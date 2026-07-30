<?php

namespace App\Http\Resources\User;

use App\Http\Resources\Auth\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CounselorProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            ...(new UserResource($this))->resolve(),
            'email' => $this->user->email,

            'professional_info' => [
                'degree' => $this->staff?->degree,
                'specialization' => $this->staff?->specialization,
                'university' => $this->staff?->university,
                'graduation_year' => $this->staff?->graduation_year,
                'hire_date' => $this->staff?->hire_date,
                'experience_years' => $this->staff?->experience_years,
            ],
        ];
    }
}

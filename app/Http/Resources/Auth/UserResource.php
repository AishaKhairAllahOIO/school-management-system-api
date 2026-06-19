<?php

namespace App\Http\Resources\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'phone_number' => $this->phone_number,
            'first_name' => $this->first_name,
            'last_name' =>  $this->last_name,
            'father_name' => $this->father_name,
            'mother_name' => $this->mother_name,
            'birth_date' => $this->birth_date,
            'birth_place' => $this->birth_place,
            'address' => $this->address,
            'nationality' => $this->nationality,
            'gender' => $this->gender,
            'personal_photo' => $this->personal_photo ? url('api/user/photos/personal' . $this->personal_photo) : null,
            'account_status' => $this->account_status,
            'record_status'=> $this->record_status,
            'roles' => $this->getRoleNames(),
        ];
    }
}

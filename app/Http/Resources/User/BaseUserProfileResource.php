<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use App\Support\FileUrl;
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

        if (!$user) {
            return [
                'id' => $this->id,
                'message' => 'No user profile attached to this record.'
            ];
        }

        return [
            'id' => $this->id,
            'user_id' => $user->id,
            'full_name' => $user->first_name . ' ' . $user->last_name,
            'father_name' => $user->father_name,
            'mother_name' => $user->mother_name,
            'birth_date' => $user->birth_date,
            'birth_place' => $user->birth_place,
            'address' => $user->address,
            'phone_number' => $user->phone_number,
            'nationality' => $user->nationality,
            'gender' => $user->gender,
            'photoUrl' => FileUrl::make(
                $user->photo_url,
                config('filesystems.default')
            ),
            'account_status' => $user->account_status,
            'record_status' => $user->record_status,
        ];
    }
}

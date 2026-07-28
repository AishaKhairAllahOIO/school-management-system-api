<?php

namespace App\Http\Resources\Student;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $studentUser = $this->user;
        $guardian = $this->guardian;
        $guardianUser = $guardian ? $guardian->user : null;

        return [
            'student' => [
                'id' => (string) $this->id,
                'userId' => (string) $studentUser->id,
                'fullName' => trim(preg_replace('/\s+/', ' ', "{$studentUser->first_name} {$studentUser->father_name} {$studentUser->last_name}")),
                'fatherName' => $studentUser->father_name,
                'motherName' => $studentUser->mother_name,
                'birthDate' => $studentUser->birth_date,
                'birthPlace' => $studentUser->birth_place,
                'address' => $studentUser->address,
                'gender' => $studentUser->gender,
                'nationality' => $studentUser->nationality,
                'phoneNumber' => $studentUser->phone_number,

                'photoUrl' => $studentUser->photo_url
                    ? url('/api/documents/photos/' . ltrim(preg_replace('/^.*?(users\/|defaults\/)/', '$1', $studentUser->photo_url), '/'))
                    : null,

                'accountStatus' => $studentUser->account_status,
                'recordStatus' => $studentUser->record_status,
            ],

            'guardian' => $guardianUser ? [
                'id' => (string) $guardian->id,
                'userId' => (string) $guardianUser->id,
                'fullName' => trim(preg_replace('/\s+/', ' ', "{$guardianUser->first_name} {$guardianUser->father_name} {$guardianUser->last_name}")),
                'fatherName' => $guardianUser->father_name,
                'motherName' => $guardianUser->mother_name,
                'birthDate' => $guardianUser->birth_date,
                'birthPlace' => $guardianUser->birth_place,
                'address' => $guardianUser->address,
                'gender' => $guardianUser->gender,
                'nationality' => $guardianUser->nationality,
                'phoneNumber' => $guardianUser->phone_number,

                'photoUrl' => $guardianUser->photo_url
                    ? url('/api/documents/photos/' . ltrim(preg_replace('/^.*?(users\/|defaults\/)/', '$1', $guardianUser->photo_url), '/'))
                    : null,

                'accountStatus' => $guardianUser->account_status,
                'recordStatus' => $guardianUser->record_status,
            ] : null,
        ];
    }
}

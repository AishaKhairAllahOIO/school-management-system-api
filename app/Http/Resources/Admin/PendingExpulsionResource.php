<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use App\Support\FileUrl;
use Illuminate\Http\Resources\Json\JsonResource;

class PendingExpulsionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $student = $this->student;
        $user = $student?->user;

        return [

            'enrollmentId' => (string) $this->id,

            'student' => [
                'id' => $student ? (string) $student->id : null,
                'userId' => $user ? (string) $user->id : null,

                'fullName' => $user
                    ? trim("{$user->first_name} {$user->father_name} {$user->last_name}")
                    : null,

                'firstName' => $user?->first_name,
                'fatherName' => $user?->father_name,
                'lastName' => $user?->last_name,

                'phoneNumber' => $user?->phone_number,

                 'photoUrl' => FileUrl::endpoint(
                $user->photo_url
            ),
            ],


            'grade' => [
                'id' => $this->gradeLevel
                    ? (string) $this->gradeLevel->id
                    : null,

                'name' => $this->gradeLevel?->name,
                'level' => $this->gradeLevel?->level,
            ],


            'classroom' => [
                'id' => $this->classRoom
                    ? (string) $this->classRoom->id
                    : null,

                'name' => $this->classRoom?->name,
            ],

            'createdAt' => $this->created_at?->toIso8601String(),

            'updatedAt' => $this->updated_at?->toIso8601String(),
        ];
    }
}

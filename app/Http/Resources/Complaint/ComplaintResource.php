<?php

namespace App\Http\Resources\Complaint;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ComplaintResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'guardian_id' => $this->guardian_id,

            'complaint_type' => [
                'id' => $this->type?->id,
                'title' => $this->type?->title,
                'severity' => $this->type?->severity,

                'category' => $this->type?->category ? [
                    'id' => $this->type->category->id,
                    'name' => $this->type->category->name,
                ] : null,
            ],

            'student' => $this->student ? [
                'id' => $this->student->id,

                'user' => $this->student->user ? [
                    'id' => $this->student->user->id,
                    'first_name' => $this->student->user->first_name,
                    'last_name' => $this->student->user->last_name,
                    'father_name' => $this->student->user->father_name,
                ] : null,

            ] : null,

            'created_at' => $this->updated_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
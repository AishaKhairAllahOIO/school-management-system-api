<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PendingExpulsionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $this->student->user;

        $expulsionAlert = $this->alerts->first();

        return [
            'enrollment_id' => $this->id,
            'alert_id' => $expulsionAlert ? $expulsionAlert->id : null,
            'first_name' => $user->first_name,
            'father_name' => $user->father_name,
            'last_name' => $user->last_name,
            'photoUrl' => $user->photo_url
                ? url('/api/documents/photos/' . ltrim(preg_replace('/^.*?(users\/|defaults\/)/', '$1', $user->photo_url), '/'))
                : null,
            'grade_name' => $this->gradeLevel ? $this->gradeLevel->name : $this->grade_level_id,
            'class_name' => $this->classRoom ? $this->classRoom->name : $this->class_room_id,
        ];
    }
}

<?php

namespace App\Http\Resources\User;

use App\Support\FileUrl;
use Illuminate\Http\Resources\Json\JsonResource;

class HomeworkAlertResource extends JsonResource
{
    public function toArray($request): array
    {
        return [

            'alert_id' => $this->id,

            'homework_id' => $this->homework_id,

            'homework_title' => $this->homework_title,

            'subject' => $this->subject,

            'title' => $this->title,

            'description' => $this->description,

            'students' => $this->alerts
                ->map(function ($alert) {

                    $enrollment = $alert->notifiable;

                    if (!$enrollment) {
                        return null;
                    }

                    $student = $enrollment->student;
                    $user = $student?->user;
                    $classRoom = $enrollment->classRoom;

                    if (!$student || !$user) {
                        return null;
                    }

                    return [
                        'enrollment_id' => $enrollment->id,

                        'student_id' => $student->id,

                        'student_name' => trim(
                            $user->first_name . ' ' . $user->last_name
                        ),

                        'personal_photo' => FileUrl::make(
                            $user->photo_url,
                            config('filesystems.public_disk')
                        ),

                        'class_room' =>
                            $classRoom?->name ?? 'غير محدد',
                    ];
                })
                ->filter()
                ->values(),
        ];
    }
}

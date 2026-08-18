<?php

namespace App\Http\Resources\Teacher;

use Illuminate\Http\Request;
use App\Support\FileUrl;
use Illuminate\Http\Resources\Json\JsonResource;

class ClassStudentEvaluationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'rating' => $this->rating,
            'rating_arabic' => $this->getRatingArabicName(),
            'notes' => $this->notes,

            'is_read' => (bool) ($this->is_read ?? false),

            'created_at' => $this->created_at?->format('Y-m-d H:i'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i'),


            'subject' => $this->whenLoaded('gradeSubject', function () {
                return [
                    'id' => $this->gradeSubject->subject?->id,
                    'name' => $this->gradeSubject->subject?->subject_name ?? 'مادة غير معرفة',
                ];
            }),


            'student' => $this->whenLoaded('enrollment', function () {

                $student = $this->enrollment?->student;
                $user = $student?->user;


                return [
                    'id' => $student?->id,

                    'full_name' => $user
                        ? trim(
                            preg_replace(
                                '/\s+/',
                                ' ',
                                "{$user->first_name} {$user->father_name} {$user->last_name}"
                            )
                        )
                        : 'طالب غير معرف',


                    'personal_photo' => FileUrl::make(
                        $user->photo_url,
                        config('filesystems.default')
                    ),
                ];
            }),


            'classroom' => $this->whenLoaded('enrollment', function () {
                return [
                    'id' => $this->enrollment->classRoom?->id,
                    'name' => $this->enrollment->classRoom?->name ?? 'شعبة غير معرفة',
                ];
            }),


            'grade' => $this->whenLoaded('enrollment', function () {
                return [
                    'id' => $this->enrollment->gradeLevel?->id,
                    'name' => $this->enrollment->gradeLevel?->name ?? 'صف غير معرف',
                ];
            }),
        ];
    }
}

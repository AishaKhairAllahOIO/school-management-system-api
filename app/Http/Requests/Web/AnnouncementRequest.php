<?php

namespace App\Http\Requests\Web;

use App\ApiResource;
use App\Models\Announcement;
use App\Models\ClassRoom;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AnnouncementRequest extends FormRequest
{
  use ApiResource;

    public function authorize(): bool
    {
        return $this->user()->can('create', [
            Announcement::class,
            $this->input('audience'),
            $this->input('grade_level_id') ? (int) $this->input('grade_level_id') : null,
            $this->input('class_room_ids')
        ]);
    }

    protected function failedAuthorization()
    {
        throw new HttpResponseException(
            $this->errorResponse('غير مصرح لك بنشر هذا الإعلان. تأكد من نوع الإعلان وصلاحياتك ضمن المرحلة الدراسية.', 403)
        );
    }
    public function rules(): array
    {
        return [
            'audience'       => ['required', Rule::in([
                Announcement::AUDIENCE_STUDENT,
                Announcement::AUDIENCE_STAFF,
                Announcement::AUDIENCE_BOTH,
            ])],
            'title'          => ['required', 'string', 'max:255'],
            'description'    => ['nullable', 'string', 'max:2000'],
            'grade_level_id'   => ['required_if:audience,' . Announcement::AUDIENCE_STUDENT,'nullable', 'integer', 'exists:grade_levels,id'],
            'class_room_ids'   => ['nullable', 'array'],
            'class_room_ids.*' => ['integer', 'exists:class_rooms,id'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $classRoomIds = $this->input('class_room_ids');

            if (!$classRoomIds || !is_array($classRoomIds)) return;

            $validRoomsCount = ClassRoom::whereIn('id', $classRoomIds)
                ->where('grade_level_id', $this->input('grade_level_id'))
                ->count();

            if ($validRoomsCount !== count($classRoomIds)) {
                $validator->errors()->add(
                    'class_room_ids',
                    'إحدى الشعب المختارة أو أكثر لا تتبع المرحلة الدراسية المحددة.'
                );
            }
        });
    }
}

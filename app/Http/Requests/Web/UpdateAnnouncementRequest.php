<?php

namespace App\Http\Requests\Web;

use App\ApiResource;
use App\Models\Announcement;
use App\Models\ClassRoom;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateAnnouncementRequest extends FormRequest
{
    use ApiResource;

    public function authorize(): bool
    {
        $announcement = Announcement::find($this->route('id'));

        if (!$announcement) {
            throw new HttpResponseException(
                $this->errorResponse('الإعلان المطلوب غير موجود.', 404)
            );
        }

        return $this->user()->can('update', [
            $announcement,
            $this->input('audience') ?? $announcement->audience,
            $this->has('grade_level_id') ? (int) $this->input('grade_level_id') : $announcement->grade_level_id,
            $this->input('class_room_ids')
        ]);
    }

    protected function failedAuthorization()
    {
        throw new HttpResponseException(
            $this->errorResponse('غير مصرح لك بتعديل هذا الإعلان.', 403)
        );
    }

    public function rules(): array
    {
        return [
            'audience'         => ['sometimes', Rule::in([
                Announcement::AUDIENCE_STUDENT,
                Announcement::AUDIENCE_STAFF,
                Announcement::AUDIENCE_BOTH,
            ])],
            'title'            => ['sometimes', 'string', 'max:255'],
            'description'      => ['nullable', 'string', 'max:2000'],
            'grade_level_id'   => ['nullable', 'integer', 'exists:grade_levels,id'],
            'class_room_ids'   => ['nullable', 'array'],
            'class_room_ids.*' => ['integer', 'exists:class_rooms,id'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $classRoomIds = $this->input('class_room_ids');
            if (!$classRoomIds || !is_array($classRoomIds)) return;

            $announcement = Announcement::find($this->route('id'));
            $gradeLevelId = $this->input('grade_level_id') ?? ($announcement ? $announcement->grade_level_id : null);

            if ($gradeLevelId) {
                $validRoomsCount = ClassRoom::whereIn('id', $classRoomIds)
                    ->where('grade_level_id', $gradeLevelId)
                    ->count();

                if ($validRoomsCount !== count($classRoomIds)) {
                    $validator->errors()->add(
                        'class_room_ids',
                        'إحدى الشعب المختارة أو أكثر لا تتبع المرحلة الدراسية للإعلان.'
                    );
                }
            } else {
                $validator->errors()->add(
                    'class_room_ids',
                    'لا يمكنك تحديد شعب دون وجود مرحلة دراسية.'
                );
            }
        });
    }
}

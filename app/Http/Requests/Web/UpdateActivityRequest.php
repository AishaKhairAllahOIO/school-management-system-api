<?php

namespace App\Http\Requests\Web;

use App\ApiResource;
use App\Models\Activity;
use App\Models\ClassRoom;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Validator;

class UpdateActivityRequest extends FormRequest
{//

use ApiResource;
    public function authorize(): bool
    {
        $activity = Activity::find($this->route('id'));
        if (!$activity)
            return false;

        return $this->user()->can('update', [
            $activity,
            $this->input('grade_level_id') ? (int) $this->input('grade_level_id') : null,
            $this->input('class_room_ids')
        ]);
    }

    protected function failedAuthorization()
    {
        throw new HttpResponseException(
            $this->errorResponse(
                'عذراً، غير مصرح لك بتعديل هذا النشاط. تأكد من تبعية النشاط لصلاحياتك كمعلم أو موجه.',
                403
            )
        );
    }

    public function rules(): array
    {
        return [
            'grade_level_id' => ['sometimes', 'integer', 'exists:grade_levels,id'],
            'class_room_ids' => ['nullable', 'array'],
            'class_room_ids.*' => ['integer', 'exists:class_rooms,id'],
            'type' => ['sometimes', 'string', 'max:255'],
            'activity_name' => ['sometimes', 'string', 'max:255'],
            'activity_date' => ['sometimes', 'date', 'after:today'],
            'start_time' => ['sometimes', 'date_format:H:i'],
            'end_time' => ['sometimes', 'date_format:H:i', 'after:start_time'],
            'description' => ['sometimes', 'string'],
        ];
    }

public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $activity = Activity::find($this->route('id'));
            if (!$activity) return;

            $classRoomIds = $this->input('class_room_ids') !== null
                ? $this->input('class_room_ids')
                : $activity->classRooms()->pluck('class_rooms.id')->toArray();

            $gradeLevelId = $this->input('grade_level_id') ?? $activity->grade_level_id;

            if (!empty($classRoomIds) && $gradeLevelId) {
                $validRoomsCount = ClassRoom::whereIn('id', $classRoomIds)
                    ->where('grade_level_id', $gradeLevelId)
                    ->count();

                if ($validRoomsCount !== count($classRoomIds)) {
                    $errorField = $this->has('grade_level_id') ? 'grade_level_id' : 'class_room_ids';

                    $validator->errors()->add(
                        $errorField,
                        'The selected class_room_ids must belong to the specified grade_level_id.'
                    );
                }
            }
        });
    }
}

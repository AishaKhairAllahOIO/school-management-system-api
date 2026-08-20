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
    public function messages(): array
    {
        return [
            'grade_level_id.integer'   => 'The grade level ID must be an integer.',
            'grade_level_id.exists'    => 'The selected grade level does not exist.',
            
            'class_room_ids.array'     => 'Classroom IDs must be provided as an array.',
            'class_room_ids.*.integer' => 'Each classroom ID must be an integer.',
            'class_room_ids.*.exists'  => 'One or more selected classrooms do not exist.',
            
            'type.string'              => 'The activity type must be a string.',
            'type.max'                 => 'The activity type must not exceed 255 characters.',
            
            'activity_name.string'     => 'The activity name must be a string.',
            'activity_name.max'        => 'The activity name must not exceed 255 characters.',
            
            'activity_date.date'       => 'The activity date must be a valid date.',
            'activity_date.after'      => 'The activity date must be a date after today.',
            
            'start_time.date_format'   => 'The start time must match the format HH:MM.',
            
            'end_time.date_format'     => 'The end time must match the format HH:MM.',
            'end_time.after'           => 'The activity end time must be after the start time.',
            
            'description.string'       => 'The description must be a string.',
        ];
    }
}

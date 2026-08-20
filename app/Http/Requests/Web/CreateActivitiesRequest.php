<?php

namespace App\Http\Requests\Web;

use App\ApiResource;
use App\Models\Activity;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\ClassRoom;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Validator;
class CreateActivitiesRequest extends FormRequest
{
    //
    use ApiResource;
  public function authorize(): bool
    {
        return $this->user()->can('create', [
            Activity::class,
            (int) $this->input('grade_level_id'),
            $this->input('class_room_ids')
        ]);
    }

    protected function failedAuthorization()
    {
        throw new HttpResponseException(
            $this->errorResponse(
                'عذراً، غير مصرح لك! إما أنك اخترت شعبة لا تدرسها، أو مرحلة لا تشرف عليها.',
                403
            )
        );
    }

    public function rules(): array
    {
        return [
            'grade_level_id'   => ['required', 'integer', 'exists:grade_levels,id'],
            'class_room_ids'   => ['nullable', 'array'],
            'class_room_ids.*' => ['integer', 'exists:class_rooms,id'],
            'type'             => ['required', 'string', 'max:255'],
            'activity_name'    => ['required', 'string', 'max:255'],
            'activity_date'    => ['required', 'date', 'after:today'],
            'start_time'       => ['required', 'date_format:H:i'],
            'end_time'         => ['required', 'date_format:H:i', 'after:start_time'],
            'description'      => ['nullable', 'string'],
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
                    'accepted class_room_ids must belong to the specified grade_level_id.'
                );
            }
        });
    }
    public function messages(): array
    {
        return [
            'grade_level_id.required'   => 'The grade level ID field is required.',
            'grade_level_id.integer'    => 'The grade level ID must be an integer.',
            'grade_level_id.exists'     => 'The selected grade level does not exist.',
            
            'class_room_ids.array'      => 'Classroom IDs must be provided as an array.',
            'class_room_ids.*.integer'  => 'Each classroom ID must be an integer.',
            'class_room_ids.*.exists'   => 'One or more selected classrooms do not exist.',
            
            'type.required'             => 'The activity type field is required.',
            'type.string'               => 'The activity type must be a string.',
            'type.max'                  => 'The activity type must not exceed 255 characters.',
            
            'activity_name.required'    => 'The activity name field is required.',
            'activity_name.string'      => 'The activity name must be a string.',
            'activity_name.max'         => 'The activity name must not exceed 255 characters.',
            
            'activity_date.required'    => 'The activity date field is required.',
            'activity_date.date'        => 'The activity date must be a valid date.',
            'activity_date.after'       => 'The activity date must be a date after today.',
            
            'start_time.required'       => 'The start time field is required.',
            'start_time.date_format'    => 'The start time must match the format HH:MM.',
            
            'end_time.required'         => 'The end time field is required.',
            'end_time.date_format'      => 'The end time must match the format HH:MM.',
            'end_time.after'            => 'The activity end time must be after the start time.',
            
            'description.string'        => 'The description must be a string.',
        ];
    }
}

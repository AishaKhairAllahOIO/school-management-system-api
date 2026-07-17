<?php

namespace App\Http\Requests\Web;

use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\ClassRoom;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
class CreateActivitiesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'grade_level_id' => ['required', 'integer', 'exists:grade_levels,id'],
            'class_room_ids'   => ['nullable', 'array'],
            'class_room_ids.*' => ['integer', 'exists:class_rooms,id'],
            'type'           => ['required', 'string', 'max:255'],
            'activity_name'           => ['required', 'string', 'max:255'],
            'activity_date'           => ['required', 'date', 'after:today'],
            'start_time'     => ['required', 'date_format:H:i'],
            'end_time'       => ['required', 'date_format:H:i', 'after:start_time'],
            'description'    => ['nullable', 'string',],


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

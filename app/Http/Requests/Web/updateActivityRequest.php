<?php

namespace App\Http\Requests\Web;

use App\Models\ClassRoom;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateActivityRequest extends FormRequest
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
            'grade_level_id' => ['sometimes', 'integer', 'exists:grade_levels,id'],
            'class_room_id'  => ['sometimes', 'integer', 'exists:class_rooms,id'],

            'type'           => ['sometimes', 'string', 'max:255'],
            'activity_name'           => ['sometimes', 'string', 'max:255'],
            'activity_date'           => ['sometimes', 'date', 'after:today'],
            'start_time'     => ['sometimes', 'date_format:H:i','after:now'],
            'end_time'       => ['sometimes', 'date_format:H:i', 'after:start_time'],
        ];

    }

     public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $classRoomId = $this->input('class_room_id');

            if (! $classRoomId) {
                return;
            }

            $belongsToGrade = ClassRoom::where('id', $classRoomId)
                ->where('grade_level_id', $this->input('grade_level_id'))
                ->exists();

            if (! $belongsToGrade) {
                $validator->errors()->add(
                    'class_room_id',
                    'الشعبة المختارة لا تتبع المرحلة الدراسية المحددة.'
                );
            }
        });
    }
}

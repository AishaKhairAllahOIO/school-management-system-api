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
            // nullable: غيابه يعني أن النشاط لكل المرحلة
            'class_room_id'  => ['nullable', 'integer', 'exists:class_rooms,id'],

            'type'           => ['required', 'string', 'max:255'],
            'activity_name'           => ['required', 'string', 'max:255'],
            'activity_date'           => ['required', 'date'],
            'start_time'     => ['required', 'date_format:H:i'],
            'end_time'       => ['required', 'date_format:H:i', 'after:start_time'],

        ];
    }

        public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $classRoomId = $this->input('class_room_id');

            if (! $classRoomId) {
                return; // نشاط لكل المرحلة — لا شيء نتحقق منه
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

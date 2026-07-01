<?php

namespace App\Http\Requests\Web;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class updateActivityRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
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
            'activity_date'           => ['sometimes', 'date'],
            'start_time'     => ['sometimes', 'date_format:H:i','after:now'],
            'end_time'       => ['sometimes', 'date_format:H:i', 'after:start_time'],
        ];
    }
}

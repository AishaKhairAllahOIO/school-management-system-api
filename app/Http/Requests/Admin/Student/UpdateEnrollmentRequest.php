<?php

namespace App\Http\Requests\Admin\Student;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEnrollmentRequest extends FormRequest
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
            'class_room_id' => 'sometimes|exists:class_rooms,id',
            'grade_level_id' => 'sometimes|exists:grade_levels,id',
           // 'enrollment_status' => 'sometimes|in:suspended,enrolled,completed',
        ];
    }
}

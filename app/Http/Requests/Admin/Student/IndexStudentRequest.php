<?php

namespace App\Http\Requests\Admin\Student;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class IndexStudentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
       return true;
    }

    public function rules(): array
    {
        return [
            'search'         => ['nullable', 'string', 'max:100'],
            'grade_level_id' => ['nullable', 'integer','exists:grade_levels,id'],
            'class_room_id'  => ['nullable', 'string','exists:class_rooms,id' ],
            'sort'           => ['nullable', 'string', 'in:asc,desc,ASC,DESC'],
            'status'         =>['nullable','string'],
            'attendance_date' => ['nullable', 'date'],
            'attendance_status' => ['nullable', 'string', 'in:present,absent'],
            'absence_type' => ['nullable', 'string', 'in:excused,unexcused'],
        ];
    }

    public function messages(): array
    {
        return [
            'grade_level_id.exists' => 'The grade level ID provided does not exist in the school.',
            'class_room_id.exists'  => 'The classroom ID provided is not valid.',
            'sort.in'               => 'The sort value must be either asc or desc.',
            'per_page.max'          => 'The maximum allowed is 100 records per page.',
        ];
    }
}

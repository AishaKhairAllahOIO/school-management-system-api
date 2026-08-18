<?php

namespace App\Http\Requests\Admin\ReportCard;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class GenerateReportCardRequest extends FormRequest
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
            'semester_id' => ['required', 'integer', 'exists:semesters,id'],
            'max_allowed_non_failing_failures' => ['nullable', 'integer', 'min:0'], // عدد المواد المسموح بها (اختياري، افتراضياً 2)
        ];
    }
    public function messages(): array
    {
        return [
            'semester_id.required' => 'The semester ID is required.',
            'semester_id.integer'  => 'The semester ID must be an integer.',
            'semester_id.exists'   => 'The selected semester does not exist in the system.',

            'max_allowed_non_failing_failures.integer' => 'The maximum allowed failures must be an integer.',
            'max_allowed_non_failing_failures.min'     => 'The maximum allowed failures cannot be less than 0.',
        ];
    }
}

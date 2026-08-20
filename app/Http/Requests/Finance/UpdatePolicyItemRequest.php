<?php

namespace App\Http\Requests\Finance;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePolicyItemRequest extends FormRequest
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
            'title'    => ['sometimes', 'string', 'max:100'],
            'dueMonth' => ['sometimes',  'integer', 'min:1', 'max:12'],
            'dueDay'   => ['sometimes',  'integer', 'min:1', 'max:31'],
        ];
    }
    public function messages(): array
    {
        return [
            'title.string'   => 'The title must be a string.',
            'title.max'      => 'The title must not exceed 100 characters.',
            'dueMonth.integer' => 'The due month must be an integer.',
            'dueMonth.min'   => 'The due month must be at least 1.',
            'dueMonth.max'   => 'The due month may not be greater than 12.',
            'dueDay.integer' => 'The due day must be an integer.',
            'dueDay.min'     => 'The due day must be at least 1.',
            'dueDay.max'     => 'The due day may not be greater than 31.',
        ];
    }
}

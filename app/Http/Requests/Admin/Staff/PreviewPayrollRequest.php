<?php

namespace App\Http\Requests\Admin\Staff;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PreviewPayrollRequest extends FormRequest
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
            'staff_id'       => ['required', 'integer', 'exists:staff,id'],
            'year'           => ['required', 'integer', 'min:2020', 'max:2099'],
            'month'          => ['required', 'integer', 'min:1', 'max:12'],
        ];
    }
}

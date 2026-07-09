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
        return $this->user()->can('installment:edit_policy');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // استخدمنا 'sometimes' لأن الفرونت إند قد يرسل حقلاً واحداً فقط لتعديله
            'title'    => ['sometimes', 'string', 'max:100'],
            'dueMonth' => ['sometimes',  'integer', 'min:1', 'max:12'],
            'dueDay'   => ['sometimes',  'integer', 'min:1', 'max:31'],
        ];
    }
}

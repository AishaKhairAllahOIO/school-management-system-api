<?php

namespace App\Http\Requests\Finance;

use App\Http\Requests\BaseRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class InstallmentPolicyRequest extends BaseRequest
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
            'name'                 => ['required', 'string', 'max:100'],
            'items'                => ['required', 'array', 'min:1'],
            'items.*.title'        => ['required', 'string', 'max:100'],
            'items.*.percentage'   => ['required', 'numeric', 'min:0.01', 'max:100'],
            'items.*.dueMonth'     => ['required', 'integer', 'min:1', 'max:12'],
            'items.*.dueDay'       => ['required', 'integer', 'min:1', 'max:31'],
        ];
    }
        public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $items = $this->input('items', []);
            $sum = array_sum(array_column($items, 'percentage'));

            if (abs($sum - 100.00) > 0.01) {
                $validator->errors()->add('items', 'The sum of installment percentages must equal 100%. Current sum is: ' . $sum . '%');
            }
        });
    }
    public function messages(): array
    {
        return [
            'name.required'              => 'The policy name field is required.',
            'name.string'                => 'The policy name must be a string.',
            'name.max'                   => 'The policy name must not exceed 100 characters.',
            
            'items.required'             => 'At least one installment item is required.',
            'items.array'                => 'The items must be structured as an array.',
            'items.min'                  => 'The policy must contain at least 1 installment item.',
            
            'items.*.title.required'     => 'Each installment item must have a title.',
            'items.*.title.string'       => 'Each installment title must be a string.',
            'items.*.title.max'          => 'Each installment title must not exceed 100 characters.',
            
            'items.*.percentage.required'=> 'Each installment item must have a percentage.',
            'items.*.percentage.numeric' => 'The installment percentage must be a number.',
            'items.*.percentage.min'     => 'The installment percentage must be at least 0.01%.',
            'items.*.percentage.max'     => 'The installment percentage cannot exceed 100%.',
            
            'items.*.dueMonth.required'  => 'Each installment item must specify a due month.',
            'items.*.dueMonth.integer'   => 'The due month must be an integer.',
            'items.*.dueMonth.min'       => 'The due month must be between 1 and 12.',
            'items.*.dueMonth.max'       => 'The due month must be between 1 and 12.',
            
            'items.*.dueDay.required'    => 'Each installment item must specify a due day.',
            'items.*.dueDay.integer'     => 'The due day must be an integer.',
            'items.*.dueDay.min'         => 'The due day must be between 1 and 31.',
            'items.*.dueDay.max'         => 'The due day must be between 1 and 31.',
        ];
    }
}

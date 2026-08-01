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
        return $this->user()->can('installment:edit_policy') || $this->user()->can('installment:set_policy') || $this->user()->can('installment:view_policy') ;
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
}

<?php
namespace App\Http\Requests\Admin\Staff;

use App\Http\Requests\BaseRequest;

class UpdateEmploymentStaffRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'degree'           => ['sometimes', 'in:diploma,bachelor,master,phd,other'],
            'specialization'   => ['sometimes', 'string'],
            'university'       => ['sometimes', 'string'],
            'graduation_year'  => ['sometimes', 'integer'],
            'hire_date'        => ['sometimes', 'date'],
            'experience_years' => ['sometimes', 'integer', 'min:0'],
            'service_type'     => ['sometimes','string'], 

        ];
    }
}
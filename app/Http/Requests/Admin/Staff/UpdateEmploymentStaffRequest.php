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
    public function messages(): array
    {
        return [
            'degree.in'                 => 'The selected degree is invalid.',
            'specialization.string'     => 'The specialization must be a valid string.',
            'specialization.max'        => 'The specialization must not exceed 100 characters.',
            'university.string'         => 'The university must be a valid string.',
            'university.max'            => 'The university must not exceed 255 characters.',
            'graduation_year.integer'   => 'The graduation year must be an integer.',
            'graduation_year.min'       => 'The graduation year cannot be earlier than 1950.',
            'graduation_year.max'       => 'The graduation year is invalid.',
            'hire_date.date'            => 'The hire date must be a valid date format.',
            'experience_years.integer'  => 'The experience years must be an integer.',
            'experience_years.min'      => 'The experience years cannot be less than 0.',
            'experience_years.max'      => 'The experience years cannot exceed 50.',
            'service_type.string'       => 'The service type must be a valid string.',
        ];
    }
}
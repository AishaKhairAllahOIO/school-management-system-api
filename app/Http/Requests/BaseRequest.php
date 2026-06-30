<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\ApiResource;


abstract class BaseRequest extends FormRequest
{
    use ApiResource;

    /**
     * تجاوز دالة فشل التحقق الافتراضية
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            $this->errorResponse(
                'Validation failed. Please check your input data.',
                422,
                $validator->errors() 
            )
        );
    }
    protected function failedAuthorization()
    {
        throw new HttpResponseException(
            //اريد الرسالة بالانكليزية

            $this->errorResponse('sorry,you do not have the permission to access this', 403,[])
        );
    }
}
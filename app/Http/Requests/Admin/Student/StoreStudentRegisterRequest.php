<?php

namespace App\Http\Requests\Admin\Student;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\BaseRequest;

class StoreStudentRegisterRequest extends BaseRequest
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
            'guardian.phone_number' => ['required', 'string', 'max:20'],
            'guardian.first_name'   => ['required', 'string', 'max:50'],
            'guardian.last_name'    => ['required', 'string', 'max:50'],
            'guardian.address'      => ['required', 'string', 'max:255'],
            'guardian.father_name'  => ['required', 'string', 'max:50'],
            'guardian.mother_name'  => ['required', 'string', 'max:50'],
            'guardian.birth_date'   => ['required', 'date', 'before:today'],
            'guardian.birth_place'  => ['required', 'string', 'max:100'],
            'guardian.gender'        => ['required', 'in:male,female'],
            'guardian.photo_url'    => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp'],
            'guardian.nationality'     => ['nullable', 'in:syrian,lebanese,palestinian,jordanian,other'],

            'student.phone_number'  => ['required', 'string', 'max:20', 'different:guardian.phone_number', 'unique:users,phone_number'],
            'student.first_name'    => ['required', 'string', 'max:50'],
            'student.last_name'     => ['required', 'string', 'max:50'],
            'student.father_name'   => ['required', 'string', 'max:50'],
            'student.mother_name'   => ['required', 'string', 'max:50'],
            'student.birth_date'    => ['required', 'date', 'before:today'],
            'student.birth_place'   => ['required', 'string', 'max:100'],
            'student.address'       => ['required', 'string', 'max:255'],
            'student.gender'        => ['required', 'in:male,female'],
            'student.photo_url'     => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp'],

            'enrollment.academic_year_id' => ['required', 'exists:academic_years,id'],
            'enrollment.grade_level_id'   => ['required', 'exists:grade_levels,id'],
            'enrollment.class_room_id'    => ['required', 'exists:class_rooms,id'],
            'student.nationality'    => ['nullable', 'in:syrian,lebanese,palestinian,jordanian,other'],

        ];
    }
    public function messages(): array
    {
        return [
            'guardian.phone_number.required' => 'accepted value is required for guardian phone number.',
            'guardian.phone_number.max' => 'The guardian phone number must not exceed 20 characters.',
            'guardian.first_name.required' => 'The guardian first name is required.',
            'guardian.last_name.required' => 'The guardian last name is required.',
            'guardian.address.required' => 'The guardian address is required.',
            'guardian.father_name.required' => 'The guardian father name is required.',
            'guardian.mother_name.required' => 'The guardian mother name is required.',
            'guardian.birth_date.required' => 'The guardian birth date is required.',
            'guardian.birth_date.before' => 'The guardian birth date must be in the past.',
            'guardian.birth_place.required' => 'The guardian birth place is required.',
            'guardian.gender.required' => 'The guardian gender is required.',
            'guardian.photo_url.required' => 'The guardian photo is required.',
            'student.phone_number.required' => 'The student phone number is required.',
            'student.phone_number.max' => 'The student phone number must not exceed 20 characters.',
            'student.phone_number.different' => 'The student phone number must be different from the guardian phone number.',
            'student.phone_number.unique' => 'The student phone number is already in use.',
            'student.first_name.required' => 'The student first name is required.',
            'student.last_name.required' => 'The student last name is required.',
            'student.father_name.required' => 'The student father name is required.',
            'student.mother_name.required' => 'The student mother name is required.',
            'student.birth_date.required' => 'The student birth date is required.',
            'student.birth_date.before' => 'The student birth date must be in the past.',
            'student.birth_place.required' => 'The student birth place is required.',
            'student.address.required' => 'The student address is required.',
            'student.gender.required' => 'The student gender is required.',
            'student.photo_url.required' => 'The student photo is required.',
            'enrollment.academic_year_id.required' => 'The academic year is required.',
            'enrollment.academic_year_id.exists' => 'The specified academic year does not exist.',
            'enrollment.grade_level_id.required' => 'The grade level is required.',
            'enrollment.grade_level_id.exists' => 'The specified grade level does not exist.',
            'enrollment.class_room_id.required' => 'The class room is required.',
            'enrollment.class_room_id.exists' => 'The specified class room does not exist.',



            ];
}}

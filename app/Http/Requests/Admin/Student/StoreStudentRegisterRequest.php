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
        return $this->user()->can('student:create');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // ----- بيانات ولي الأمر -----
            'guardian.phone_number' => ['required', 'string', 'max:20'], 
            'guardian.first_name'   => ['required', 'string', 'max:50'],
            'guardian.last_name'    => ['required', 'string', 'max:50'],
            'guardian.address'      => ['required', 'string', 'max:255'],
            'guardian.father_name'  => ['required', 'string', 'max:50'], 
            'guardian.mother_name'  => ['required', 'string', 'max:50'],
            'guardian.birth_date'   => ['required', 'date', 'before:today'],
            'guardian.birth_place'  => ['required', 'string', 'max:100'],
            'guardian.gender'        => ['required', 'in:male,female'],
            'guardian.photo_url'    => ['required', 'string', 'max:255'], // تم إضافة صورة ولي الأمر            
            'guardian.nationality'     => ['nullable', 'in:syrian,lebanese,palestinian,jordanian,other'],


            

            // ----- بيانات الطالب -- ---
            'student.phone_number'  => ['required', 'string', 'max:20', 'different:guardian.phone_number', 'unique:users,phone_number'],
            'student.first_name'    => ['required', 'string', 'max:50'],
            'student.last_name'     => ['required', 'string', 'max:50'],
            'student.father_name'   => ['required', 'string', 'max:50'],
            'student.mother_name'   => ['required', 'string', 'max:50'],
            'student.birth_date'    => ['required', 'date', 'before:today'],
            'student.birth_place'   => ['required', 'string', 'max:100'],
            'student.address'       => ['required', 'string', 'max:255'],
            'student.gender'        => ['required', 'in:male,female'],
            'student.photo_url'     => ['required', 'string', 'max:255'], // تم إضافة صورة الطالب
            // ----- الهوية والالتحاق (مطابقة للـ Enums المحمية) -----
            'enrollment.academic_year_id' => ['required', 'exists:academic_years,id'],
            'enrollment.grade_level_id'   => ['required', 'exists:grade_levels,id'],
            'enrollment.class_room_id'    => ['required', 'exists:class_rooms,id'],
            'student.nationality'    => ['nullable', 'in:syrian,lebanese,palestinian,jordanian,other'],

        ];
    }
    public function messages(): array
    {
        return [
            'guardian.phone_number.required' => 'رقم الهاتف لولي الأمر مطلوب.',
            'guardian.phone_number.max' => 'رقم الهاتف لولي الأمر يجب ألا يزيد عن 20 حرفًا.',
            'guardian.first_name.required' => 'الاسم الأول لولي الأمر مطلوب.',
            'guardian.last_name.required' => 'الاسم الأخير لولي الأمر مطلوب.',
            'guardian.address.required' => 'عنوان ولي الأمر مطلوب.',
            'guardian.father_name.required' => 'اسم الأب لولي الأمر مطلوب.',
            'guardian.mother_name.required' => 'اسم الأم لولي الأمر مطلوب.',
            'guardian.birth_date.required' => 'تاريخ ميلاد ولي الأمر مطلوب.',
            'guardian.birth_date.before' => 'تاريخ ميلاد ولي الأمر يجب أن يكون قبل اليوم الحالي.',
            'guardian.birth_place.required' => 'مكان ميلاد ولي الأمر مطلوب.',
            'guardian.gender.required' => 'جنس ولي الأمر مطلوب.',
            'guardian.photo_url.required' => 'صورة ولي الأمر مطلوبة.',
            'student.phone_number.required' => 'رقم الهاتف للطالب مطلوب.',
            'student.phone_number.max' => 'رقم الهاتف للطالب يجب ألا يزيد عن 20 حرفًا.',
            'student.phone_number.different' => 'رقم الهاتف للطالب يجب أن يكون مختلفًا عن رقم الهاتف لولي الأمر.',
            'student.phone_number.unique' => 'رقم الهاتف للطالب مستخدم بالفعل.',
            'student.first_name.required' => 'الاسم الأول للطالب مطلوب.',
            'student.last_name.required' => 'الاسم الأخير للطالب مطلوب.',
            'student.father_name.required' => 'اسم الأب للطالب مطلوب.',
            'student.mother_name.required' => 'اسم الأم للطالب مطلوب.',
            'student.birth_date.required' => 'تاريخ ميلاد الطالب مطلوب.',
            'student.birth_date.before' => 'تاريخ ميلاد الطالب يجب أن يكون قبل اليوم الحالي.',
            'student.birth_place.required' => 'مكان ميلاد الطالب مطلوب.',
            'student.address.required' => 'عنوان الطالب مطلوب.',
            'student.gender.required' => 'جنس الطالب مطلوب.',
            'student.photo_url.required' => 'صورة الطالب مطلوبة.',
            'enrollment.academic_year_id.required' => 'السنة الدراسية مطلوبة.',
            'enrollment.academic_year_id.exists' => 'السنة الدراسية المحددة غير موجودة.',
            'enrollment.grade_level_id.required' => 'المستوى الدراسي مطلوب.',
            'enrollment.grade_level_id.exists' => 'المستوى الدراسي المحدد غير موجود',
            'enrollment.class_room_id.required' => 'الصف الدراسي مطلوب.',
            'enrollment.class_room_id.exists' => 'الصف الدراسي المحدد غير موجود.',

                
              
            ];
}}

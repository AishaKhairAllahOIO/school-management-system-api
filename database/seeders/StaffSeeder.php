<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Staff;
use App\Models\Role;

class StaffSeeder extends Seeder
{
    public function run(): void
    {
        // جلب معرفات الأدوار لربطها بالموظفين
  

        // 1. المدير العام (Super Admin)
        Staff::create([
            'role_id' => 1,
            'first_name' => 'طارق', 'last_name' => 'العلي', 'father_name' => 'سعيد', 'mother_name' => 'هند',
            'birth_date' => '1975-04-12', 'birth_place' => 'دمشق', 'gender' => 'male', 'nationality' => 'syrian',
            'phone_number' => '0911111111', 'address' => 'المزة', 'hire_date' => '2015-09-01',
        ]);

        // 2. المدرس (Teacher)
        Staff::create([
            'role_id' =>2,
            'first_name' => 'أحمد', 'last_name' => 'المحمد', 'father_name' => 'محمود', 'mother_name' => 'فاطمة',
            'birth_date' => '1990-05-15', 'birth_place' => 'حمص', 'gender' => 'male', 'nationality' => 'syrian',
            'phone_number' => '0922222222', 'address' => 'الوعر', 'hire_date' => '2020-09-01',
        ]);

        // 3. أمين السر (Secretary)
        Staff::create([
            'role_id' =>3,
            'first_name' => 'سارة', 'last_name' => 'الخالد', 'father_name' => 'خالد', 'mother_name' => 'مريم',
            'birth_date' => '1985-08-20', 'birth_place' => 'حلب', 'gender' => 'female', 'nationality' => 'syrian',
            'phone_number' => '0933333333', 'address' => 'الفرقان', 'hire_date' => '2018-02-10',
        ]);

        // 4. الموجه (Supervisor)
        Staff::create([
            'role_id' => 4,
            'first_name' => 'محمود', 'last_name' => 'سالم', 'father_name' => 'رضوان', 'mother_name' => 'ليلى',
            'birth_date' => '1982-11-30', 'birth_place' => 'حماة', 'gender' => 'male', 'nationality' => 'syrian',
            'phone_number' => '0944444444', 'address' => 'القصور', 'hire_date' => '2017-09-01',
        ]);

        // 5. المرشد النفسي (Counselor)
        Staff::create([
            'role_id' => 5,
            'first_name' => 'ندى', 'last_name' => 'كريم', 'father_name' => 'مصطفى', 'mother_name' => 'سعاد',
            'birth_date' => '1988-02-14', 'birth_place' => 'اللاذقية', 'gender' => 'female', 'nationality' => 'syrian',
            'phone_number' => '0955555555', 'address' => 'الزراعة', 'hire_date' => '2021-08-15',
        ]);

        // 6. عامل الخدمة (Service Staff - حارس/مستخدم)
        Staff::create([
            'role_id' => 6,
            'first_name' => 'عبد الله', 'last_name' => 'الزين', 'father_name' => 'حسن', 'mother_name' => 'نور',
            'birth_date' => '1970-11-05', 'birth_place' => 'دمشق', 'gender' => 'male', 'nationality' => 'syrian',
            'phone_number' => '0966666666', 'address' => 'الميدان', 'hire_date' => '2022-01-01',
            'employee_type' => 'حارس أمن',
        ]);
    }
}
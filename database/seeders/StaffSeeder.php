<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Staff;
use App\Models\Role;

class StaffSeeder extends Seeder
{
    public function run(): void
    {
        // مصفوفة بيانات الموظفين السبعة يدوياً
        $staffMembers = [
            ['first_name' => 'Ahmed', 'last_name' => 'Ali', 'phone' => '0911111111', 'birth' => '1990-05-15', 'hire' => '2020-01-01', 'gender' => 'M', 'role' => Role::SUPER_ADMIN],
            ['first_name' => 'Sara', 'last_name' => 'Hassan', 'phone' => '0922222222', 'birth' => '1992-06-20', 'hire' => '2021-02-15', 'gender' => 'F', 'role' => Role::TEACHER],
            ['first_name' => 'Khaled', 'last_name' => 'Omar', 'phone' => '0933333333', 'birth' => '1985-03-10', 'hire' => '2019-09-01', 'gender' => 'M', 'role' => Role::ADVISOR],
            ['first_name' => 'Layla', 'last_name' => 'Mahmoud', 'phone' => '0944444444', 'birth' => '1995-11-12', 'hire' => '2022-03-10', 'gender' => 'F', 'role' => Role::SECRETARY],
            ['first_name' => 'Mona', 'last_name' => 'Said', 'phone' => '0955555555', 'birth' => '1988-08-30', 'hire' => '2020-05-05', 'gender' => 'F', 'role' => Role::COUNSELOR],
            ['first_name' => 'Youssef', 'last_name' => 'Ibrahim', 'phone' => '0966666666', 'birth' => '1970-01-01', 'hire' => '2023-01-01', 'gender' => 'M', 'role' => Role::PARENT],
            ['first_name' => 'Nour', 'last_name' => 'Kamal', 'phone' => '0977777777', 'birth' => '2008-04-05', 'hire' => '2024-09-01', 'gender' => 'F', 'role' => Role::STUDENT],
        ];

        foreach ($staffMembers as $member) {
            // جلب الرول بناءً على الاسم الثابت
            $role = Role::where('role_name', $member['role'])->first();

            if ($role) {
                Staff::create([
                    'role_id'      => $role->id,
                    'first_name'   => $member['first_name'],
                    'last_name'    => $member['last_name'],
                    'phone_number' => $member['phone'],
                    'birth_date'   => $member['birth'],
                    'hire_date'    => $member['hire'],
                    'gender'       => $member['gender'],
                    'address'      => 'Damascus, Syria' // عنوان افتراضي عادي
                ]);
            }
        }
    }
}
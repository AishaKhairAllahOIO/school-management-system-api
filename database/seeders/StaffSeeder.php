<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Staff;
use App\Models\Role;

class StaffSeeder extends Seeder
{
    public function run(): void
    {
        $staffMembers = [
            [
                'first_name' => 'فادي', 'last_name' => 'الحسن', 'father_name' => 'أيمن', 'mother_name' => 'سلوى',
                'phone' => '0931111111', 'birth' => '2010' . '-04-15', 'birth_place' => 'دمشق', 'hire' => '2024-09-01',
                'gender' => 'male', 'nationality' => 'syrian', 'address' => 'دمشق - التجارة', 'role' => 'STUDENT'
            ],
            [
                'first_name' => 'أحمد', 'last_name' => 'المنصور', 'father_name' => 'محمود', 'mother_name' => 'فاطمة',
                'phone' => '0933111222', 'birth' => '1988-05-12', 'birth_place' => 'دمشق', 'hire' => '2018-09-01',
                'gender' => 'male', 'nationality' => 'syrian', 'address' => 'دمشق - الميدان', 'role' => 'TEACHER'
            ],
            [
                'first_name' => 'سامر', 'last_name' => 'الخطيب', 'father_name' => 'يسار', 'mother_name' => 'هند',
                'phone' => '0932222222', 'birth' => '1978-10-05', 'birth_place' => 'حلب', 'hire' => '2024-09-01',
                'gender' => 'male', 'nationality' => 'syrian', 'address' => 'دمشق - الشعلان', 'role' => 'PARENT'
            ],
            [
                'first_name' => 'رنا', 'last_name' => 'الحمصي', 'father_name' => 'محمد', 'mother_name' => 'عائشة',
                'phone' => '0944333444', 'birth' => '1994-11-20', 'birth_place' => 'حمص', 'hire' => '2021-02-15',
                'gender' => 'female', 'nationality' => 'syrian', 'address' => 'حمص - المحطة', 'role' => 'SECRETARY'
            ],
            [
                'first_name' => 'حسام', 'last_name' => 'الدين', 'father_name' => 'عمر', 'mother_name' => 'ندى',
                'phone' => '0955666777', 'birth' => '1982-03-14', 'birth_place' => 'اللاذقية', 'hire' => '2017-10-01',
                'gender' => 'male', 'nationality' => 'syrian', 'address' => 'اللاذقية - المشروع الأول', 'role' => 'SUPERVISOR'
            ],
            [
                'first_name' => 'منى', 'last_name' => 'سعيد', 'father_name' => 'تيسير', 'mother_name' => 'سعاد',
                'phone' => '0988777666', 'birth' => '1989-08-30', 'birth_place' => 'طرطوس', 'hire' => '2020-05-05',
                'gender' => 'female', 'nationality' => 'syrian', 'address' => 'دمشق - مشروع دمر', 'role' => 'COUNSELOR'
            ],
            [
                'first_name' => 'خالد', 'last_name' => 'العبيد', 'father_name' => 'صالح', 'mother_name' => 'مريم',
                'phone' => '0955555666', 'birth' => '1980-02-01', 'birth_place' => 'حلب', 'hire' => '2015-01-10',
                'gender' => 'male', 'nationality' => 'syrian', 'address' => 'ريف دمشق - جرمانا', 'role' => 'SERVICE_STAFF'
            ],
            [
                'first_name' => 'محمد', 'last_name' => 'العلي', 'father_name' => 'عبد الله', 'mother_name' => 'هناء',
                'phone' => '0930000000', 'birth' => '1980-01-01', 'birth_place' => 'دمشق', 'hire' => '2015-01-01',
                'gender' => 'male', 'nationality' => 'syrian', 'address' => 'دمشق - أبو رمانة', 'role' => 'SUPER_ADMIN'
            ],
                ];

        foreach ($staffMembers as $member) {
            $role = Role::where('role_name', $member['role'])->first();

            if ($role) {
                Staff::create([
                    'role_id'      => $role->id,
                    'first_name'   => $member['first_name'],
                    'last_name'    => $member['last_name'],
                    'father_name'  => $member['father_name'],
                    'mother_name'  => $member['mother_name'],
                    'birth_date'   => $member['birth'],
                    'birth_place'  => $member['birth_place'],
                    'gender'       => $member['gender'],
                    'nationality'  => $member['nationality'],
                    'phone_number' => $member['phone'],
                    'address'      => $member['address'],
                    'personal_photo'    => null,
                    'hire_date'    => $member['hire'],
                    'record_status'=> 'active',
                ]);
            }
        }
    }
}
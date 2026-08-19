<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultPassword = Hash::make(env('DEFAULT_USER_PASSWORD', 'password'));

        $user1 = User::updateOrCreate(
            ['phone_number' => '0996930692'],
            [
                'first_name' => 'احمد',
                'last_name' => 'العلي الصالح',
                'father_name' => 'حسن',
                'mother_name' => 'أمل ',
                'birth_date' => '1990-01-01',
                'birth_place' => 'دمشق',
                'address' => 'معضمية الشام , دمشق',
                'nationality' => 'syrian',
                'gender' => 'male',
                'record_status' => 'active',
                'account_status' => 'enabled',
                'photo_url' => 'users/guardians/guardian.jpg',
                'password' => $defaultPassword,
            ]
        );
        $user1->assignRole('guardian');

        $user2 = User::updateOrCreate(
            ['phone_number' => '0981915237'],
            [
                'first_name' => 'سارة',
                'last_name' => 'سطيف',
                'father_name' => 'عدنان',
                'mother_name' => 'منى',
                'birth_date' => '1990-01-01',
                'birth_place' => 'حمص',
                'address' => 'الساعة, حمص',
                'nationality' => 'syrian',
                'gender' => 'female',
                'record_status' => 'active',
                'account_status' => 'enabled',
                'photo_url' => 'users/students/student_1.jpg',
                'password' => $defaultPassword,
            ]
        );
        $user2->assignRole('student');

        $user3 = User::updateOrCreate(
            ['phone_number' => '0960657750'],
            [
                'email' => 'nnnnahhmad@gmail.com',
                'first_name' => 'عائشة',
                'last_name' => 'خيرالله',
                'father_name' => 'عماد الدين',
                'mother_name' => 'سوزان',
                'birth_date' => '1990-01-01',
                'birth_place' => 'حلب',
                'address' => 'القلعة حلب',
                'nationality' => 'syrian',
                'gender' => 'female',
                'account_status' => 'enabled',
                'record_status' => 'active',
                'photo_url' => 'users/staff/teacher_1.jpg',
                'password' => $defaultPassword,
            ]
        );
        $user3->assignRole('teacher');

        $user4 = User::updateOrCreate(
            ['phone_number' => '0960657741'],
            [
                'email' => 'shahdeslim0@gmail.com',
                'account_status' => 'enabled',
                'record_status' => 'active',
                'first_name' => 'محمود',
                'last_name' => 'القاسم',
                'father_name' => 'سامر',
                'mother_name' => 'كنانة',
                'birth_date' => '1992-05-15',
                'birth_place' => 'دمشق',
                'address' => 'كفرسوسة, دمشق',
                'nationality' => 'syrian',
                'gender' => 'male',
                'photo_url' => 'users/staff/admin_1.jpg',
                'password' => $defaultPassword,
            ]
        );
        $user4->assignRole('super_admin');

        $user5 = User::updateOrCreate(
            ['phone_number' => '0967774111'],
            [
                'first_name' => 'يزن',
                'last_name' => 'سطيف',
                'father_name' => 'سالم',
                'mother_name' => 'لمى',
                'birth_date' => '1985-12-10',
                'birth_place' => 'دمشق',
                'address' => 'شارع خالد ابن الوليد',
                'nationality' => 'syrian',
                'account_status' => 'enabled',
                'record_status' => 'active',
                'gender' => 'male',
                'photo_url' => 'users/students/student_2.jpg',
                'password' => $defaultPassword,
            ]
        );
        $user5->assignRole('student');

        $user6 = User::updateOrCreate(
            ['phone_number' => '0980612500'],
            [
                'first_name' => 'عبد الرحمن',
                'last_name' => 'محمد',
                'father_name' => 'بلال',
                'mother_name' => 'هالة',
                'birth_date' => '1995-08-22',
                'birth_place' => 'دمشق',
                'address' => 'قدسيا',
                'nationality' => 'syrian',
                'gender' => 'male',
                'photo_url' => 'users/staff/admin_2.jpg',
                'password' => $defaultPassword,
                'email' => 'aishakhairallah3@gmail.com',
                'account_status' => 'enabled',
                'record_status' => 'active',
            ]
        );
        $user6->assignRole('super_admin');

        $user7 = User::updateOrCreate(
            ['phone_number' => '0994416081'],
            [
                'first_name' => 'شهد',
                'last_name' => 'الخطيب',
                'father_name' => 'طلال',
                'mother_name' => 'سنا',
                'birth_date' => '1988-03-30',
                'birth_place' => 'دمشق',
                'address' => 'ميدان',
                'nationality' => 'syrian',
                'gender' => 'female', // تم التصحيح هنا
                'photo_url' => 'users/staff/admin_3.jpg',
                'email' => 'shadooalkhateeb1234@gmail.com',
                'account_status' => 'enabled',
                'record_status' => 'active',
                'password' => $defaultPassword,
            ]
        );
        $user7->assignRole('super_admin');

        $user8 = User::updateOrCreate(
            ['phone_number' => '0983846541'],
            [
                'first_name' => 'عابدة',
                'last_name' => 'خير الله',
                'father_name' => 'معاذ',
                'mother_name' => 'سارة',
                'birth_date' => '1988-03-30',
                'birth_place' => 'دمشق',
                'address' => 'أبو رمانة',
                'nationality' => 'syrian',
                'gender' => 'female', // تم التصحيح هنا أيضاً
                'photo_url' => 'users/staff/secretary_1.jpg',
                'email' => 'aishakhairallah2025@gmail.com',
                'account_status' => 'enabled',
                'record_status' => 'active',
                'password' => $defaultPassword,
            ]
        );
        $user8->assignRole('secretary');

        $user9 = User::updateOrCreate(
            ['phone_number' => '0994416082'],
            [
                'first_name' => 'امين',
                'last_name' => 'المحاسني',
                'father_name' => 'جميل',
                'mother_name' => 'شيرين',
                'birth_date' => '1990-06-04',
                'birth_place' => 'ريف دمشق',
                'address' => 'جمرايا',
                'nationality' => 'syrian',
                'gender' => 'male',
                'photo_url' => 'users/staff/service_1.jpg',
                'password' => $defaultPassword,
                'account_status' => 'enabled',
                'record_status' => 'active',
            ]
        );
        $user9->assignRole('service_staff');

        $user10 = User::updateOrCreate(
            ['phone_number' => '0994416083'],
            [
                'first_name' => 'عيسى',
                'last_name' => 'سلمان',
                'father_name' => 'فؤاد',
                'mother_name' => 'حياة',
                'birth_date' => '1985-11-19',
                'birth_place' => 'ريف دمشق',
                'address' => 'جمرايا',
                'email' => 'aishakhairallah262005@gmail.com',
                'password' => $defaultPassword,
                'nationality' => 'jordanian',
                'gender' => 'male',
                'photo_url' => 'users/staff/adviser_1.jpg',
                'account_status' => 'enabled',
                'record_status' => 'active',
            ]
        );
        $user10->assignRole('adviser');

        $user11 = User::updateOrCreate(
            ['phone_number' => '0994416084'],
            [
                'first_name' => 'فداء',
                'last_name' => 'لؤي',
                'father_name' => 'كريم',
                'mother_name' => 'طيبة',
                'birth_date' => '1992-02-14',
                'birth_place' => 'دمشق',
                'address' => 'كفرسوسة',
                'email' => 'fidaaahmadd@gmail.com',
                'password' => $defaultPassword,
                'nationality' => 'other',
                'gender' => 'female',
                'photo_url' => 'users/staff/secretary_2.jpg',
                'account_status' => 'enabled',
                'record_status' => 'active',
            ]
        );
        $user11->assignRole('secretary');

        $user12 = User::updateOrCreate(
            ['phone_number' => '0993790629'],
            [
                'first_name' => 'ثائر',
                'last_name' => 'العلي',
                'father_name' => 'عمر',
                'mother_name' => 'سالي',
                'birth_date' => '1985-12-10',
                'birth_place' => 'دمشق',
                'address' => 'ميدان',
                'nationality' => 'syrian',
                'account_status' => 'enabled',
                'record_status' => 'active',
                'gender' => 'male',
                'photo_url' => 'users/students/student_2.jpg',
                'password' => $defaultPassword,
            ]
        );
        $user12->assignRole('student');

        $user13 = User::updateOrCreate(
            ['phone_number' => '0935026786'],
            [
                'first_name' => 'ماجدة',
                'last_name' => 'الشلبي',
                'father_name' => 'محمد',
                'mother_name' => 'نور',
                'birth_date' => '1985-12-10',
                'birth_place' => 'دمشق',
                'address' => 'قدسيا',
                'nationality' => 'syrian',
                'account_status' => 'enabled',
                'record_status' => 'active',
                'gender' => 'female',
                'email' => 'alshalabimajeda@gmail.com',
                'photo_url' => 'users/staff/teacher_2.jpg',
                'password' => $defaultPassword,
            ]
        );
        $user13->assignRole('teacher');

        $user14 = User::updateOrCreate(
            [
                'phone_number' => '0992006689',
            ],
            [
                'first_name' => 'مريم',
                'last_name' => 'الخالد',
                'father_name' => 'سالم',
                'mother_name' => 'نهى',
                'birth_date' => '1985-12-10',
                'birth_place' => 'دمشق',
                'address' => 'باب مصلى',
                'nationality' => 'syrian',
                'account_status' => 'enabled',
                'record_status' => 'active',
                'gender' => 'female',
                'email' => 'mouhamedalshalabi@gmail.com',
                'photo_url' => 'users/staff/counselor_1.jpg',
                'password' => $defaultPassword,
            ]
        );
        $user14->assignRole('counselor');

        $user15 = User::updateOrCreate(
            [
                'phone_number' => '0984227325',
            ],
            [
                'first_name' => 'امل',
                'last_name' => 'الصالح',
                'father_name' => 'احمد',
                'mother_name' => 'فاطمة',
                'birth_date' => '1985-12-10',
                'birth_place' => 'دمشق',
                'address' => 'ميدان',
                'nationality' => 'syrian',
                'account_status' => 'enabled',
                'record_status' => 'active',
                'gender' => 'female',
                'email' => 'mouhaedalshalab@gmail.com',
                'photo_url' => 'users/staff/teacher_3.jpg',
                'password' => $defaultPassword,
            ]
        );
        $user15->assignRole('teacher');

        $user16 = User::updateOrCreate(
            [
                'phone_number' => '0951287463',
            ],
            [
                'first_name' => 'سالي',
                'last_name' => 'اسليم',
                'father_name' => 'محمد',
                'mother_name' => 'رنيم',
                'birth_date' => '1985-12-10',
                'birth_place' => 'دمشق',
                'address' => 'ميدان',
                'nationality' => 'syrian',
                'account_status' => 'enabled',
                'record_status' => 'active',
                'gender' => 'female',
                'email' => 'mouhamealshaabi@gmail.com',
                'photo_url' => 'users/staff/teacher_4.jpg',
                'password' => $defaultPassword,
            ]
        );
        $user16->assignRole('teacher');

        $user17 = User::updateOrCreate(
            [
                'phone_number' => '0911254786',
            ],
            [
                'first_name' => 'دعاء',
                'last_name' => 'القاسم',
                'father_name' => 'حبيب',
                'mother_name' => 'جنى',
                'birth_date' => '1985-12-10',
                'birth_place' => 'دمشق',
                'address' => 'ركن الدين',
                'nationality' => 'syrian',
                'account_status' => 'enabled',
                'record_status' => 'active',
                'gender' => 'female',
                'email' => 'mouhamedalsabi@gmail.com',
                'photo_url' => 'users/staff/teacher_5.jpg',
                'password' => $defaultPassword,
            ]
        );
        $user17->assignRole('teacher');

        $user18 = User::updateOrCreate(
            [
                'phone_number' => '0911354789',
            ],
            [
                'first_name' => 'معاذ',
                'last_name' => 'حمزة',
                'father_name' => 'عمر',
                'mother_name' => 'هدى',
                'birth_date' => '1985-12-10',
                'birth_place' => 'ريف دمشق',
                'address' => 'دوما',
                'nationality' => 'syrian',
                'account_status' => 'enabled',
                'record_status' => 'active',
                'gender' => 'male',
                'email' => 'ahmadahmadahmad@gmail.com',
                'photo_url' => 'users/staff/teacher_6.jpg',
                'password' => $defaultPassword,
            ]
        );
        $user18->assignRole('teacher');

        $user19 = User::updateOrCreate(
            [
                'phone_number' => '0911304789',
            ],
            [
                'first_name' => 'حمد',
                'last_name' => 'كامل',
                'father_name' => 'حاتم',
                'mother_name' => 'رغد',
                'birth_date' => '1985-12-10',
                'birth_place' => 'ريف دمشق',
                'address' => 'دوما',
                'nationality' => 'syrian',
                'account_status' => 'enabled',
                'record_status' => 'active',
                'gender' => 'male',
                'email' => 'ahmadahmahmad@gmail.com',
                'photo_url' => 'users/staff/teacher_7.jpg',
                'password' => $defaultPassword,
            ]
        );
        $user19->assignRole('teacher');
    }
}

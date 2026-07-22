<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Hash;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\Guardian;
use App\Models\User;
use App\Models\Staff;
use App\Models\Role;

class TestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
 public function run(): void
    {
        // 1. التأكد من إنشاء الأدوار الأساسية في النظام


        $defaultPassword = Hash::make('password123');

        // ==========================================
        // 2. Super Admin (مدير النظام)
        // ==========================================
        $adminUser = User::firstOrCreate(
            ['phone_number' => '0911111111'],
            [
                'email'          => 'admin@school.com',
                'first_name'     => 'مدير',
                'last_name'      => 'النظام',
                'father_name'    => 'العام',
                'mother_name'    => 'فاطمة',
                'birth_date'     => '1985-05-10',
                'birth_place'    => 'دمشق',
                'address'        => 'دمشق - المزة',
                'gender'         => 'male',
                'nationality'    => 'syrian',
                'photo_url'      => 'users\staff\1ijKaa9OWZhMM0pL2dOWyGTyGGVtuuhuSshHjugS.png',
                'password'       => $defaultPassword,
                'account_status' => 'enabled',
            ]
        );
        $adminUser->assignRole('super_admin');

        Staff::firstOrCreate(
            ['user_id' => $adminUser->id],
            [
                'degree'           => 'phd',
                'specialization'   => 'إدارة تقنية المعلومات',
                'university'       => 'جامعة دمشق',
                'graduation_year'  => 2010,
                'hire_date'        => '2020-01-01',
                'experience_years' => 15,
                'service_type'     => null,
            ]
        );

        // ==========================================
        // 3. Teacher (معلم)
        // ==========================================
        $teacherUser = User::firstOrCreate(
            ['phone_number' => '0922222222'],
            [
                'email'          => 'teacher@school.com',
                'first_name'     => 'محمد',
                'last_name'      => 'الأحمد',
                'father_name'    => 'خالد',
                'mother_name'    => 'مريم',
                'birth_date'     => '1990-03-15',
                'birth_place'    => 'حلب',
                'address'        => 'حلب - الشهباء',
                'gender'         => 'male',
                'nationality'    => 'syrian',
                'photo_url'      => 'users\staff\1ijKaa9OWZhMM0pL2dOWyGTyGGVtuuhuSshHjugS.png',
                'password'       => $defaultPassword,
                'account_status' => 'enabled',
            ]
        );
        $teacherUser->assignRole('teacher');

        Staff::firstOrCreate(
            ['user_id' => $teacherUser->id],
            [
                'degree'           => 'bachelor',
                'specialization'   => 'رياضيات',
                'university'       => 'جامعة حلب',
                'graduation_year'  => 2013,
                'hire_date'        => '2022-09-01',
                'experience_years' => 10,
                'service_type'     => null,
            ]
        );
         $teacherUser1 = User::firstOrCreate(
            ['phone_number' => '0922222223'],
            [
                'email'          => 'teacher1@school.com',
                'first_name'     => 'محمد',
                'last_name'      => 'الأحمد',
                'father_name'    => 'خالد',
                'mother_name'    => 'مريم',
                'birth_date'     => '1990-03-15',
                'birth_place'    => 'حلب',
                'address'        => 'حلب - الشهباء',
                'gender'         => 'male',
                'nationality'    => 'syrian',
                'photo_url'      => 'users\staff\1ijKaa9OWZhMM0pL2dOWyGTyGGVtuuhuSshHjugS.png',
                'password'       => $defaultPassword,
                'account_status' => 'enabled',
            ]
        );
        $teacherUser1->assignRole('teacher');

        Staff::firstOrCreate(
            ['user_id' => $teacherUser1->id],
            [
                'degree'           => 'bachelor',
                'specialization'   => 'عربي',
                'university'       => 'جامعة حلب',
                'graduation_year'  => 2013,
                'hire_date'        => '2022-09-01',
                'experience_years' => 7,
                'service_type'     => null,
            ]
        );

        // ==========================================
        // 4. Secretary (أمين سر)
        // ==========================================
        $secretaryUser = User::firstOrCreate(
            ['phone_number' => '0933333333'],
            [
                'email'          => 'secretary@school.com',
                'first_name'     => 'سارة',
                'last_name'      => 'محمود',
                'father_name'    => 'إبراهيم',
                'mother_name'    => 'هدى',
                'birth_date'     => '1995-07-20',
                'birth_place'    => 'حمص',
                'address'        => 'حمص - الإنشاءات',
                'gender'         => 'female',
                'nationality'    => 'syrian',
                'photo_url'      => 'users\staff\1ijKaa9OWZhMM0pL2dOWyGTyGGVtuuhuSshHjugS.png',
                'password'       => $defaultPassword,
                'account_status' => 'enabled',
            ]
        );
        $secretaryUser->assignRole('secretary');

        Staff::firstOrCreate(
            ['user_id' => $secretaryUser->id],
            [
                'degree'           => 'diploma',
                'specialization'   => 'إدارة أعمال',
                'university'       => 'جامعة البعث',
                'graduation_year'  => 2017,
                'hire_date'        => '2023-01-15',
                'experience_years' => 5,
                'service_type'     => null,
            ]
        );

        //
            $adviserUser = User::firstOrCreate(
            ['phone_number' => '0933333334'],
            [
                'email'          => 'adviser@school.com',
                'first_name'     => 'مروة',
                'last_name'      => 'محمود',
                'father_name'    => 'إبراهيم',
                'mother_name'    => 'هدى',
                'birth_date'     => '1995-07-20',
                'birth_place'    => 'حمص',
                'address'        => 'حمص - الإنشاءات',
                'gender'         => 'female',
                'nationality'    => 'syrian',
                'photo_url'      => 'users\staff\1ijKaa9OWZhMM0pL2dOWyGTyGGVtuuhuSshHjugS.png',
                'password'       => $defaultPassword,
                'account_status' => 'enabled',
            ]
        );

         $adviserUser->assignRole('adviser');

        Staff::firstOrCreate(
            ['user_id' => $adviserUser->id],
            [
                'degree'           => 'diploma',
                'specialization'   => 'إدارة أعمال',
                'university'       => 'جامعة البعث',
                'graduation_year'  => 2017,
                'hire_date'        => '2023-01-15',
                'experience_years' => 5,
                'service_type'     => null,
            ]
        );

         $adviserUser1 = User::firstOrCreate(
            ['phone_number' => '0933333335'],
            [
                'email'          => 'adviser1@school.com',
                'first_name'     => 'مروة',
                'last_name'      => 'محمود',
                'father_name'    => 'إبراهيم',
                'mother_name'    => 'هدى',
                'birth_date'     => '1995-07-20',
                'birth_place'    => 'حمص',
                'address'        => 'حمص - الإنشاءات',
                'gender'         => 'female',
                'nationality'    => 'syrian',
                'photo_url'      => 'users\staff\1ijKaa9OWZhMM0pL2dOWyGTyGGVtuuhuSshHjugS.png',
                'password'       => $defaultPassword,
                'account_status' => 'enabled',
            ]
        );

         $adviserUser1->assignRole('adviser');

        Staff::firstOrCreate(
            ['user_id' => $adviserUser1->id],
            [
                'degree'           => 'diploma',
                'specialization'   => 'إدارة الفنون',
                'university'       => 'جامعة البعث',
                'graduation_year'  => 2017,
                'hire_date'        => '2023-01-15',
                'experience_years' => 2,
                'service_type'     => null,
            ]
        );
        //
         $counselorUser = User::firstOrCreate(
            ['phone_number' => '0933333354'],
            [
                'email'          => 'counselor@school.com',
                'first_name'     => 'مروة',
                'last_name'      => 'محمود',
                'father_name'    => 'إبراهيم',
                'mother_name'    => 'هدى',
                'birth_date'     => '1995-07-20',
                'birth_place'    => 'حمص',
                'address'        => 'حمص - الإنشاءات',
                'gender'         => 'female',
                'nationality'    => 'syrian',
                'photo_url'      => 'users\staff\1ijKaa9OWZhMM0pL2dOWyGTyGGVtuuhuSshHjugS.png',
                'password'       => $defaultPassword,
                'account_status' => 'enabled',
            ]
        );
         $counselorUser->assignRole('counselor');

        Staff::firstOrCreate(
            ['user_id' => $counselorUser->id],
            [
                'degree'           => 'diploma',
                'specialization'   => 'إدارة أعمال',
                'university'       => 'جامعة دمشق',
                'graduation_year'  => 2018,
                'hire_date'        => '2023-01-15',
                'experience_years' => 3,
                'service_type'     => null,
            ]
        );
        //


        // ==========================================
        // 5. Service Staff (موظف خدمة - بدون إيميل)
        // ==========================================
        $serviceUser = User::firstOrCreate(
            ['phone_number' => '0944444444'],
            [
                'email'          => null, // قيد: بدون إيميل
                'first_name'     => 'قاسم',
                'last_name'      => 'الحارس',
                'father_name'    => 'صالح',
                'mother_name'    => 'عائشة',
                'birth_date'     => '1980-11-10',
                'birth_place'    => 'اللاذقية',
                'address'        => 'اللاذقية - المشروع السابع',
                'gender'         => 'male',
                'nationality'    => 'syrian',
                'photo_url'      => 'users\staff\1ijKaa9OWZhMM0pL2dOWyGTyGGVtuuhuSshHjugS.png',
                'password'       => $defaultPassword,
                'account_status' => 'enabled',
            ]
        );
        $serviceUser->assignRole('service_staff');

        Staff::firstOrCreate(
            ['user_id' => $serviceUser->id],
            [
                'degree'           => null, // الحقول الأخرى فارغة
                'specialization'   => null,
                'university'       => null,
                'graduation_year'  => null,
                'experience_years' => 0,
                'hire_date'        => '2021-06-01', // تاريخ التعيين فقط
                'service_type'     => 'guard',      // نوع الخدمة فقط
            ]
        );

        // ==========================================
        // 6. Parent (ولي الأمر - بدون إيميل)
        // ==========================================
        $parentUser = User::firstOrCreate(
            ['phone_number' => '0955555555'],
            [
                'email'          => null, // قيد: بدون إيميل
                'first_name'     => 'سامر',
                'last_name'      => 'الناصر',
                'father_name'    => 'عبدالله',
                'mother_name'    => 'سعاد',
                'birth_date'     => '1975-12-05',
                'birth_place'    => 'حماة',
                'address'        => 'حماة - باغية',
                'gender'         => 'male',
                'nationality'    => 'syrian',
                'photo_url'      => 'users\staff\1ijKaa9OWZhMM0pL2dOWyGTyGGVtuuhuSshHjugS.png',
                'password'       => $defaultPassword,
                'account_status' => 'enabled',
            ]
        );
        $parentUser->assignRole('guardian');

        $guardian = Guardian::firstOrCreate(
            ['user_id' => $parentUser->id]
           
        );

        // ==========================================
        // 7. Student (الطالب - بدون إيميل)
        // ==========================================
        $studentUser = User::firstOrCreate(
            ['phone_number' => '0966666666'],
            [
                'email'          => null, // قيد: بدون إيميل
                'first_name'     => 'كنان',
                'last_name'      => 'الناصر',
                'father_name'    => 'سامر',
                'mother_name'    => 'منى',
                'birth_date'     => '2012-04-10',
                'birth_place'    => 'حماة',
                'address'        => 'حماة - باغية',
                'gender'         => 'male',
                'nationality'    => 'syrian',
                'photo_url'      => 'users\staff\1ijKaa9OWZhMM0pL2dOWyGTyGGVtuuhuSshHjugS.png',
                'password'       => $defaultPassword,
                'account_status' => 'enabled',
            ]
        );
        $studentUser->assignRole('student');

        Student::firstOrCreate(
            ['user_id' => $studentUser->id],
            [
                'guardian_id'   => $guardian->id,
            ]
        );
    }
}

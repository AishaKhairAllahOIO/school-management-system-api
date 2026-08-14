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
            ['phone_number' => '0968661500'],
            [
                'first_name' => 'Nour',
                'last_name' => 'Alali_Alsaleh',
                'father_name' => 'Ahmad',
                'mother_name' => 'Amal Alali_Alsaleh',
                'birth_date' => '1990-01-01',
                'birth_place' => 'Cityville',
                'address' => '123 Main St, Cityville',
                'nationality' => 'syrian',
                'gender' => 'female',
                'record_status' => 'active',
                'account_status' => 'enabled',
                'photo_url' => 'defaults/guardian.jpg',
                'password' => $defaultPassword,
            ]
        );
        $user1->assignRole('guardian');

        $user2 = User::updateOrCreate(
            ['phone_number' => '0981915237'],
            [
                'first_name' => 'Sara',
                'last_name' => 'Staif',
                'father_name' => 'Adnan',
                'mother_name' => 'Jane Doe',
                'birth_date' => '1990-01-01',
                'birth_place' => 'Cityville',
                'address' => '123 Main St, Cityville',
                'nationality' => 'syrian',
                'gender' => 'female',
                'record_status' => 'active',
                'account_status' => 'enabled',
                'photo_url' => 'defaults/student_female.jpg',
                'password' => $defaultPassword,
            ]
        );
        $user2->assignRole('student');

        $user3 = User::updateOrCreate(
            ['phone_number' => '0960657750'],
            [
                'email' => 'aishakhairallah@gmail.com',
                'first_name' => 'aisha',
                'last_name' => 'khair allah',
                'father_name' => 'emad aldeen',
                'mother_name' => 'suzan ktait',
                'birth_date' => '1990-01-01',
                'birth_place' => 'Cityville',
                'address' => '123 Main St, Cityville',
                'nationality' => 'syrian',
                'gender' => 'female',
                'account_status' => 'enabled',
                'record_status' => 'active',
                'photo_url' => 'defaults/teacher.jpg',
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
                'first_name' => 'Mohammed',
                'last_name' => 'Al_Kassem',
                'father_name' => 'Samer',
                'mother_name' => 'Mary Khory',
                'birth_date' => '1992-05-15',
                'birth_place' => 'Townsville',
                'address' => '456 Oak Ave, Townsville',
                'nationality' => 'syrian',
                'gender' => 'female',
                'photo_url' => 'defaults/admin.jpg',
                'password' => $defaultPassword,
            ]
        );
        $user4->assignRole('super_admin');

        $user5 = User::updateOrCreate(
            ['phone_number' => '0983964422'],
            [
                'first_name' => 'Yazan',
                'last_name' => 'Al_khalid',
                'father_name' => 'Salem',
                'mother_name' => 'Lisa Johnson',
                'birth_date' => '1985-12-10',
                'birth_place' => 'Villageburg',
                'address' => '789 Pine Rd, Villageburg',
                'nationality' => 'syrian',
                'account_status' => 'enabled',
                'record_status' => 'active',
                'gender' => 'male',
                'photo_url' => 'defaults/student_male.jpg',
                'password' => $defaultPassword,
            ]
        );
        $user5->assignRole('student');

        $user6 = User::updateOrCreate(
            ['phone_number' => '0980612500'],
            [
                'first_name' => 'Alice',
                'last_name' => 'Williams',
                'father_name' => 'David',
                'mother_name' => 'Susan Williams',
                'birth_date' => '1995-08-22',
                'birth_place' => 'Hamletville',
                'address' => '321 Elm St, Hamletville',
                'nationality' => 'syrian',
                'gender' => 'female',
                'photo_url' => 'defaults/counselor.jpg',
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
                'first_name' => 'Charlie',
                'last_name' => 'Brown',
                'father_name' => 'Charles Brown Sr.',
                'mother_name' => 'Sally Brown',
                'birth_date' => '1988-03-30',
                'birth_place' => 'Metropolis',
                'address' => '654 Maple Ave, Metropolis',
                'nationality' => 'syrian',
                'gender' => 'male',
                'photo_url' => 'defaults/adviser.jpg',
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
                'first_name' => 'aisha',
                'last_name' => 'kher',
                'father_name' => 'Charles Brown Sr.',
                'mother_name' => 'Sally Brown',
                'birth_date' => '1988-03-30',
                'birth_place' => 'Metropolis',
                'address' => '654 Maple Ave, Metropolis',
                'nationality' => 'syrian',
                'gender' => 'male',
                'photo_url' => 'defaults/secretary.jpg',
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
                'first_name' => 'Diana',
                'last_name' => 'Prince',
                'father_name' => 'Hippolyta Prince',
                'mother_name' => 'Queen Hippolyta',
                'birth_date' => '1990-06-04',
                'birth_place' => 'Themyscira',
                'address' => '987 Amazon St, Themyscira',
                'nationality' => 'syrian',
                'gender' => 'female',
                'photo_url' => 'https://example.com/photo6.jpg',
                'password' => $defaultPassword,
                'account_status' => 'enabled',
                'record_status' => 'active',
            ]
        );
        $user9->assignRole('service_staff');

        $user10 = User::updateOrCreate(
            ['phone_number' => '0994416083'],
            [
                'first_name' => 'Ethan',
                'last_name' => 'Hunt',
                'father_name' => 'Ethan Hunt Sr.',
                'mother_name' => 'Julia Hunt',
                'birth_date' => '1985-11-19',
                'birth_place' => 'Spy City',
                'address' => '123 Mission St, Spy City',
                'email' => 'aishakhairallah262005@gmail.com',
                'password' => $defaultPassword,
                'nationality' => 'jordanian',
                'gender' => 'male',
                'photo_url' => 'https://example.com/photo7.jpg',
                'account_status' => 'enabled',
                'record_status' => 'active',
            ]
        );
        $user10->assignRole('adviser');

        $user11 = User::updateOrCreate(
            ['phone_number' => '0994416084'],
            [
                'first_name' => 'Fiona',
                'last_name' => 'Gallagher',
                'father_name' => 'Frank Gallagher',
                'mother_name' => 'Monica Gallagher',
                'birth_date' => '1992-02-14',
                'birth_place' => 'Shameless Town',
                'address' => '456 Chaos Ave, Shameless Town',
                'email' => 'fidaaahmadd@gmail.com',
                'password' => $defaultPassword,
                'nationality' => 'other',
                'gender' => 'female',
                'photo_url' => 'https://example.com/photo8.jpg',
                'account_status' => 'enabled',
                'record_status' => 'active',
            ]
        );
        $user11->assignRole('secretary');

        $user12 = User::updateOrCreate(
            ['phone_number' => '0993790629'],
            [
                'first_name' => 'Samer',
                'last_name' => 'Al_ali',
                'father_name' => 'Salem',
                'mother_name' => 'Lisa Johnson',
                'birth_date' => '1985-12-10',
                'birth_place' => 'Villageburg',
                'address' => '789 Pine Rd, Villageburg',
                'nationality' => 'syrian',
                'account_status' => 'enabled',
                'record_status' => 'active',
                'gender' => 'male',
                'photo_url' => 'defaults/student_male.jpg',
                'password' => $defaultPassword,
            ]
        );
        $user12->assignRole('student');

        $user13 = User::updateOrCreate(
            ['phone_number' => '0935026786'],
            [
                'first_name' => 'Majeda',
                'last_name' => 'Al_Shalabi',
                'father_name' => 'Mohammed',
                'mother_name' => 'Nour',
                'birth_date' => '1985-12-10',
                'birth_place' => 'Villageburg',
                'address' => '789 Pine Rd, Villageburg',
                'nationality' => 'syrian',
                'account_status' => 'enabled',
                'record_status' => 'active',
                'gender' => 'female',
                'email' => 'alshalabimajeda@gmail.com',
                'photo_url' => 'defaults/teacher.jpg',
                'password' => $defaultPassword,
            ]
        );
        $user13->assignRole('teacher');

        $user14 = User::updateOrCreate(
            [
                'phone_number' => '0992006689',
            ],
            [
                'first_name' => 'Mariam',
                'last_name' => 'Al_khalid',
                'father_name' => 'Salem',
                'mother_name' => 'Lisa Johnson',
                'birth_date' => '1985-12-10',
                'birth_place' => 'Villageburg',
                'address' => '789 Pine Rd, Villageburg',
                'nationality' => 'syrian',
                'account_status' => 'enabled',
                'record_status' => 'active',
                'gender' => 'female',
                'email' => 'mouhamedalshalabi@gmail.com',
                'photo_url' => 'defaults/counselor.jpg
            ',
                'password' => $defaultPassword,
            ]
        );

        $user14->assignRole('counselor');






        $user15 = User::updateOrCreate(
            [
                'phone_number' => '0984227325',
            ],
            [
                'first_name' => 'Amal',
                'last_name' => 'Alsaleh',
                'father_name' => 'Salem',
                'mother_name' => 'Lisa Johnson',
                'birth_date' => '1985-12-10',
                'birth_place' => 'Villageburg',
                'address' => '789 Pine Rd, Villageburg',
                'nationality' => 'syrian',
                'account_status' => 'enabled',
                'record_status' => 'active',
                'gender' => 'female',
                'email' => 'mouhaedalshalab@gmail.com',
                'photo_url' => 'defaults/teacher.jpg
            ',
                'password' => $defaultPassword,
            ]
        );

        $user15->assignRole('teacher');

        $user16 = User::updateOrCreate(
            [
                'phone_number' => '0951287463',
            ],
            [
                'first_name' => 'Mariam',
                'last_name' => 'Al_khalid',
                'father_name' => 'Salem',
                'mother_name' => 'Lisa Johnson',
                'birth_date' => '1985-12-10',
                'birth_place' => 'Villageburg',
                'address' => '789 Pine Rd, Villageburg',
                'nationality' => 'syrian',
                'account_status' => 'enabled',
                'record_status' => 'active',
                'gender' => 'female',
                'email' => 'mouhamealshaabi@gmail.com',
                'photo_url' => 'defaults/counselor.jpg
            ',
                'password' => $defaultPassword,
            ]
        );

        $user16->assignRole('teacher');
        $user17 = User::updateOrCreate(
            [
                'phone_number' => '0911254786',
            ],
            [
                'first_name' => 'Mariam',
                'last_name' => 'Al_khalid',
                'father_name' => 'Salem',
                'mother_name' => 'Lisa Johnson',
                'birth_date' => '1985-12-10',
                'birth_place' => 'Villageburg',
                'address' => '789 Pine Rd, Villageburg',
                'nationality' => 'syrian',
                'account_status' => 'enabled',
                'record_status' => 'active',
                'gender' => 'female',
                'email' => 'mouhamedalsabi@gmail.com',
                'photo_url' => 'defaults/counselor.jpg
            ',
                'password' => $defaultPassword,
            ]
        );

        $user17->assignRole('teacher');
        $user18 = User::updateOrCreate(
            [
                'phone_number' => '0911354789',
            ],
            [
                'first_name' => 'Ahmad',
                'last_name' => 'Al_khalid',
                'father_name' => 'Salem',
                'mother_name' => 'Lisa Johnson',
                'birth_date' => '1985-12-10',
                'birth_place' => 'Villageburg',
                'address' => '789 Pine Rd, Villageburg',
                'nationality' => 'syrian',
                'account_status' => 'enabled',
                'record_status' => 'active',
                'gender' => 'male',
                'email' => 'ahmadahmadahmad@gmail.com',
                'photo_url' => 'defaults/teacher.jpg
            ',
                'password' => $defaultPassword,
            ]
        );

        $user18->assignRole('teacher');
        $user19 = User::updateOrCreate(
            [
                'phone_number' => '0911304789',
            ],
            [
                'first_name' => 'hmad',
                'last_name' => 'Al_khalid',
                'father_name' => 'Salem',
                'mother_name' => 'Lisa Johnson',
                'birth_date' => '1985-12-10',
                'birth_place' => 'Villageburg',
                'address' => '789 Pine Rd, Villageburg',
                'nationality' => 'syrian',
                'account_status' => 'enabled',
                'record_status' => 'active',
                'gender' => 'male',
                'email' => 'ahmadahmahmad@gmail.com',
                'photo_url' => 'defaults/teacher.jpg
            ',
                'password' => $defaultPassword,
            ]
        );

        $user19->assignRole('teacher');
    }
}

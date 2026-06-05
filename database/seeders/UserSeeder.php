<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
           // 'id' => 1,
            'phone_number' => '0981915237',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'father_name' => 'Michael Doe',
            'mother_name' => 'Jane Doe',
            'birth_date' => '1990-01-01',
            'birth_place' => 'Cityville',
            'address' => '123 Main St, Cityville',
            'nationality' => 'syrian',
            'gender'=>'male',
            'photo_url'=>'https://example.com/photo.jpg',
            'role_id' => 3, // Assuming role_id 1 corresponds to a valid role in the roles table
            'password' => bcrypt('123456789'),

        ]);

          User::create([
           // 'id' => 1,
            'phone_number' => '0968661500',
            'first_name' => 'Sara',
            'last_name' => 'Stef',
            'father_name' => 'Adnan Stef',
            'mother_name' => 'Jane Doe',
            'birth_date' => '1990-01-01',
            'birth_place' => 'Cityville',
            'address' => '123 Main St, Cityville',
            'nationality' => 'syrian',
            'gender'=>'female',
            'photo_url'=>'https://example.com/photo.jpg',
            'role_id' => 2, // Assuming role_id 1 corresponds to a valid role in the roles table
            'password' => bcrypt('123456789'),

        ]);



         User::create([
            //'id'=>2,
            'phone_number' => '0960657740',
            'email'=>'shahdeslim0@gmail.com',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'father_name' => 'John Smith',
            'mother_name' => 'Mary Smith',
            'birth_date' => '1992-05-15',
            'birth_place' => 'Townsville',
            'address' => '456 Oak Ave, Townsville',
            'nationality' => 'syrian',
            'gender'=>'female',
            'photo_url'=>'https://example.com/photo2.jpg',
            'role_id' => 4, // Assuming role_id 4 corresponds to a valid role in the roles table
            'password' => bcrypt('123456789'),
        ]);

         User::create([
            //'id'=>3,
            'phone_number' => '0983964422',
            'first_name' => 'Bob',
            'last_name' => 'Johnson',
            'father_name' => 'Robert Johnson',
            'mother_name' => 'Lisa Johnson',
            'birth_date' => '1985-12-10',
            'birth_place' => 'Villageburg',
            'address' => '789 Pine Rd, Villageburg',
            'nationality' => 'syrian',
            'gender'=>'male',
            'photo_url'=>'https://example.com/photo3.jpg',
            'role_id' => 2, // Assuming role_id 2 corresponds to a valid role in the roles table
            'password' => bcrypt('123456789'),
        ]);

        User::create([
           // 'id'=>4,
            'phone_number'=>'0980612500',
            'first_name' => 'Alice',
            'last_name' => 'Williams',
            'father_name' => 'David Williams',
            'mother_name' => 'Susan Williams',
            'birth_date' => '1995-08-22',
            'birth_place' => 'Hamletville',
            'address' => '321 Elm St, Hamletville',
            'nationality' => 'syrian',
            'gender'=>'female',
            'photo_url'=>'https://example.com/photo4.jpg',
            'role_id'=>6, // Assuming RoleSeeder::TEACHER_ROLE_ID corresponds to a valid role in the roles table
            'password'=>bcrypt('123456789'),
        ]);

        User::create([
           // 'id'=>5,
            'phone_number'=>'0994416081',
            'first_name' => 'Charlie',
            'last_name' => 'Brown',
            'father_name' => 'Charles Brown Sr.',
            'mother_name' => 'Sally Brown',
            'birth_date' => '1988-03-30',
            'birth_place' => 'Metropolis',
            'address' => '654 Maple Ave, Metropolis',
            'nationality' => 'syrian',
            'gender' => 'male',
            'photo_url' => 'https://example.com/photo5.jpg',
            'role_id' => 4, // Assuming role_id 3 corresponds to a valid
            'email' => 'shadooalkhateeb1234@gmail.com',
            'password' => bcrypt('123456789'),
        ]);

            User::create([
            // 'id'=>6,
                'phone_number'=>'0994416082',
                'first_name' => 'Diana',
                'last_name' => 'Prince',
                'father_name' => 'Hippolyta Prince',
                'mother_name' => 'Queen Hippolyta',
                'birth_date' => '1990-06-04',
                'birth_place' => 'Themyscira',
                'address' => '987 Amazon St, Themyscira',
                'nationality' => 'syrian',
                'gender'=>'female',
                'photo_url'=>'https://example.com/photo6.jpg',
                'role_id'=>7, // Assuming RoleSeeder::STAFF_SERVICES_ROLE_ID corresponds to a valid role in the roles table
                'employee_type' => 'security',
                'password'=>bcrypt('123456789'),


                ]);
    }
}

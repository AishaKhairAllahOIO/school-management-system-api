<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
    Student::create([
        'user_id'=>5,
        'guardian_id'=>1,
    ]);

    Student::create([
        'user_id'=>2,
        'guardian_id'=>1,
    ]);
    Student::create([
        'user_id'=>12,
        'guardian_id'=>2,
    ]);



    }
}

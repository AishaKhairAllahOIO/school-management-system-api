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
            'phone_number' => '0981915237',
            'password' => bcrypt('123456789'),
            'role_id' => 1,
        ]);

         User::create([
            'phone_number' => '0960657740',
            'password' => bcrypt('123456789'),
            'role_id' => 1,
        ]);

         User::create([
            'phone_number' => '0983964422',
            'password' => bcrypt('123456789'),
            'role_id' => 1,
        ]);
    }
}

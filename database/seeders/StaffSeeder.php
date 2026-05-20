<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SystemAccess;

class StaffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SystemAccess::create([
          'email'=>'shadooalkhateeb1234@gmail.com',
          'password'=> bcrypt('123456789'),
         
        ]);
          SystemAccess::create([
          'email'=>'shahdeslim0@gmail.com',
          'password'=> bcrypt('123456789'),
         // 'is_active'=> false,
         
        ]);
    }
}

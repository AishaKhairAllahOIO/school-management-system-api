<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SystemAccess;

class SystemAccessSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SystemAccess::create([
          'staff_id'=>1,
          'email'=>'shadooalkhateeb1234@gmail.com',
          'password'=> bcrypt('123456789'),
         
        ]);
          SystemAccess::create([
          'staff_id'=>4,
          'email'=>'shahdeslim0@gmail.com',
          'password'=> bcrypt('123456789'),
         // 'is_active'=> false,
         
        ]);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['role_name' => 'SUPER_ADMIN'], 
            ['role_name' => 'STUDENT'],
            ['role_name' => 'TEACHER'],
            ['role_name' => 'PARENT'],
            ['role_name' => 'SECRETARY'],
            ['role_name' => 'SUPERVISOR'],
            ['role_name' => 'COUNSELOR'],
            ['role_name' => 'SERVICE_STAFF'],
        ];

     foreach ($roles as $role) {
            Role::create($role);
    }
    }
}

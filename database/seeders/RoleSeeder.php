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
        $roles=[
            Role::TEACHER,
            Role::PARENT,
            Role::STUDENT,
            Role::SUPER_ADMIN,
            Role::ADVISOR,
            Role::SECRETARY,
            Role::COUNSELOR
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['role_name'=>$role]
                );
        }
    }
}

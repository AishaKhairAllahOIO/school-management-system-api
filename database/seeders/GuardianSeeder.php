<?php

namespace Database\Seeders;

use App\Models\Guardian;
use App\Models\User;
use Illuminate\Database\Seeder;

class GuardianSeeder extends Seeder
{
    public function run(): void
    {
        $guardians = User::whereHas('role', function ($query) {
            $query->where('role_name', 'GUARDIAN');
        })->get();

        // 2. الدوران على كل ولي أمر
        foreach ($guardians as $guardian) {
            Guardian::updateOrCreate(
                [
                    'user_id' => $guardian->id,
                ]
            );
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AcademicStage;

class AcademicStageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
 public function run(): void
    {
        $stages = ['primary', 'middle', 'secondary'];

        foreach ($stages as $stage) {
            AcademicStage::firstOrCreate(['type' => $stage]);
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AcademicYearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
 // AcademicYearSeeder.php
public function run(): void
{
    AcademicYear::updateOrCreate(
        ['id' => 1],
        ['year_name' => '2023-2024', 'start_date' => '2023-09-01', 'end_date' => '2024-12-30']
    );
}
}

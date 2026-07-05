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
        ['year_name' => '2025-2026', 'start_date' => '2025-09-01', 'end_date' => '2026-12-30']
    );
}
}

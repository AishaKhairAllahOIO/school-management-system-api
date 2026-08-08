<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AcademicStage;
use App\Enums\AcademicStageType;

class AcademicStageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AcademicStage::updateOrCreate([
            'type' => AcademicStageType::Middle,
        ],
        []
    );
    }
}


    
        
         
        

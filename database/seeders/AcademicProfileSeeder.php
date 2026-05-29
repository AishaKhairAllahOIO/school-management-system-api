<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AcademicProfile;
use App\Models\Staff;

class AcademicProfileSeeder extends Seeder
{
    public function run(): void
    {

            AcademicProfile::create([
                'staff_id' => 1,
                'degree' => 'bachelor', 'specialization' => 'الرياضيات التطبيقية',
                'university' => 'جامعة البعث', 'graduation_year' => 2012, 'experience_years' => 8,
            ]);
        

            AcademicProfile::create([
                'staff_id' => 2,
                'degree' => 'master', 'specialization' => 'مناهج وطرائق تدريس',
                'university' => 'جامعة دمشق', 'graduation_year' => 2010, 'experience_years' => 12,
            ]);
        

            AcademicProfile::create([
                'staff_id' => 3,
                'degree' => 'bachelor', 'specialization' => 'علم نفس وتوجيه إرشادي',
                'university' => 'جامعة تشرين', 'graduation_year' => 2015, 'experience_years' => 5,
            ]);
        
    }
}
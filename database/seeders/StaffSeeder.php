<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Staff;
use App\Models\Role;

class StaffSeeder extends Seeder
{
    public function run(): void
    {
        // جلب معرفات الأدوار لربطها بالموظفين

 Staff::create([
                'user_id' => 3,
                'degree' => 'bachelor',
                'specialization' => 'الرياضيات التطبيقية',
                'university' => 'جامعة البعث',
                'graduation_year' => 2012,
                'experience_years' => 8,
            ]);


            Staff::create([
                'user_id' => 8,
                'degree' => 'master',
                'specialization' => 'مناهج وطرائق تدريس',
                'university' => 'جامعة دمشق',
                'graduation_year' => 2010,
                'experience_years' => 12,
            ]);


            Staff::create([
                'user_id' => 4,
                'degree' => 'bachelor',
                'specialization' => 'علم نفس وتوجيه إرشادي',
                'university' => 'جامعة تشرين',
                'graduation_year' => 2015,
                'experience_years' => 5,
            ]);
    }
}

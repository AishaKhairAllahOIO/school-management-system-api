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
                'hire_date' => '2022-08-11'
            ]);


            Staff::create([
                'user_id' => 8,
                'degree' => 'master',
                'specialization' => 'مناهج وطرائق تدريس',
                'university' => 'جامعة دمشق',
                'graduation_year' => 2010,
                'experience_years' => 12,
                'hire_date' => '2010-09-01',
            ]);


            Staff::create([
                'user_id' => 6,
                'degree' => 'bachelor',
                'specialization' => 'علم نفس وتوجيه إرشادي',
                'university' => 'جامعة تشرين',
                'graduation_year' => 2015,
                'experience_years' => 5,
                'hire_date' => '2015-09-01',
            ]);
            Staff::create([
                'user_id' => 4,
                'degree' => 'bachelor',
                'specialization' => 'ادارة',
                'university' => 'جامعة دمشق',
                'graduation_year' => 2015,
                'experience_years' => 5,
                'hire_date' => '2015-09-01',
            ]);
                Staff::create([
                'user_id' => 10,
                'degree' => 'bachelor',
                'specialization' => 'توجيه',
                'university' => 'جامعة دمشق',
                'graduation_year' => 2015,
                'experience_years' => 6,
                'hire_date' => '2015-09-01',
            ]);
                Staff::create([
                'user_id' => 11,
                'degree' => 'bachelor',
                'specialization' => 'اداة المكاتب',
                'university' => 'جامعة دمشق',
                'graduation_year' => 2015,
                'experience_years' => 6,
                'hire_date' => '2015-09-01',
            ]);
             Staff::create([
                'user_id' => 9,
                'degree' => null,
                'specialization' => null,
                'university' => null,
                'graduation_year' => null,
                'experience_years' => 0,
                'hire_date' => '2015-09-01',
                'service_type'=>'cleaner'
            ]);

    }
}

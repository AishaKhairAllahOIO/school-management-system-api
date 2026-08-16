<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Staff;
use App\Models\Role;

class StaffSeeder extends Seeder
{
    public function run(): void
    {

        Staff::updateOrCreate(
            [
                'user_id' => 3,
            ],
            [
                'degree' => 'bachelor',
                'specialization' => 'الرياضيات التطبيقية',
                'university' => 'جامعة البعث',
                'graduation_year' => 2012,
                'experience_years' => 8,
                'hire_date' => '2022-08-11'
            ]
        );
        Staff::updateOrCreate(
            [
                'user_id' => 8,
            ],
            [
                'degree' => 'master',
                'specialization' => 'مناهج وطرائق تدريس',
                'university' => 'جامعة دمشق',
                'graduation_year' => 2010,
                'experience_years' => 12,
                'hire_date' => '2010-09-01',
            ]
        );
        Staff::updateOrCreate(
            [
                'user_id' => 6,
            ],
            [
                'degree' => 'bachelor',
                'specialization' => 'الفيزياء والعلوم الحيوية',
                'university' => 'جامعة البعث',
                'graduation_year' => 2015,
                'experience_years' => 5,
                'hire_date' => '2015-09-01',
            ]
        );
        Staff::updateOrCreate(
            [
                'user_id' => 4,
            ],
            [
                'degree' => 'bachelor',
                'specialization' => 'ادارة',
                'university' => 'جامعة دمشق',
                'graduation_year' => 2015,
                'experience_years' => 5,
                'hire_date' => '2015-09-01',
            ]
        );
        Staff::updateOrCreate(
            [
                'user_id' => 10,
            ],
            [
                'degree' => 'bachelor',
                'specialization' => 'توجيه',
                'university' => 'جامعة دمشق',
                'graduation_year' => 2015,
                'experience_years' => 6,
                'hire_date' => '2015-09-01',
            ]
        );
        Staff::updateOrCreate(
            [
                'user_id' => 11,
            ],
            [
                'degree' => 'bachelor',
                'specialization' => 'اداة المكاتب',
                'university' => 'جامعة دمشق',
                'graduation_year' => 2015,
                'experience_years' => 6,
                'hire_date' => '2015-09-01',
            ]
        );
        Staff::updateOrCreate(
            [
                'user_id' => 9,
            ],
            [
                'degree' => 'other',
                'specialization' =>null ,
                'university' => null,
                'graduation_year' => null,
                'experience_years' => 3,
                'hire_date' => '2015-09-01',
                'service_type' => 'cleaner'
            ]
        );

        Staff::updateOrCreate(
            [
                'user_id' => 13,
            ],
            [
                'degree' => 'bachelor',
                'specialization' => 'العلوم الحيوية',
                'university' => 'جامعة دمشق',
                'graduation_year' => 2010,
                'experience_years' => 15,
                'hire_date' => '2022-09-01',
            ]
        );


        Staff::updateOrCreate(
            [
                'user_id' => 14,
            ],
            [
                'degree' => 'master',
                'specialization' => 'علم نفس وتوجيه إرشادي',
                'university' => 'جامعة دمشق',
                'graduation_year' => 2020,
                'experience_years' => 4,
                'hire_date' => '2023-09-01',
            ]
        );

        Staff::updateOrCreate(
            [
                'user_id' => 15,
            ],
            [
                'degree' => 'master',
                'specialization' => 'كيمياء حيوية',
                'university' => 'جامعة دمشق',
                'graduation_year' => 2022,
                'experience_years' => 3,
                'hire_date' => '2023-09-01',
            ]
        );

        Staff::updateOrCreate(
            [
                'user_id' => 16,
            ],
            [
                'degree' => 'master',
                'specialization' => 'لغة فرنسية',
                'university' => 'جامعة دمشق',
                'graduation_year' => 2020,
                'experience_years' => 2,
                'hire_date' => '2023-09-01',
            ]
        );

        Staff::updateOrCreate(
            [
                'user_id' => 17,
            ],
            [
                'degree' => 'phd',
                'specialization' => 'رياضة ومهارات بدنية',
                'university' => 'جامعة دمشق',
                'graduation_year' => 2019,
                'experience_years' => 6,
                'hire_date' => '2023-09-01',
            ]
        );

        Staff::updateOrCreate(
            [
                'user_id' => 18,
            ],
            [
                'degree' => 'bachelor',
                'specialization' => 'فنون جميلة',
                'university' => 'جامعة دمشق',
                'graduation_year' => 2022,
                'experience_years' => 4,
                'hire_date' => '2023-09-01',
            ]
        );
        Staff::updateOrCreate(
            [
                'user_id' => 19,
            ],
            [
                'degree' => 'bachelor',
                'specialization' => 'لغة عربية',
                'university' => 'جامعة دمشق',
                'graduation_year' => 2022,
                'experience_years' => 4,
                'hire_date' => '2023-09-01',
            ]
        );
      


    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AcademicProfile;
use App\Models\Staff;

class AcademicProfileSeeder extends Seeder
{
    public function run(): void
    {
        $profiles = [
            [
                'first_name' => 'أحمد', 'last_name' => 'المنصور',
                'degree' => 'bachelor', 'specialization' => 'الرياضيات',
                'university' => 'جامعة دمشق', 'graduation_year' => 2010, 'experience_years' => 14
            ],
            [
                'first_name' => 'رنا', 'last_name' => 'الحمصي',
                'degree' => 'diploma', 'specialization' => 'إدارة مكاتب والمعلوماتية',
                'university' => 'المعهد التجاري بدمشق', 'graduation_year' => 2015, 'experience_years' => 8
            ],
            [
                'first_name' => 'حسام', 'last_name' => 'الدين',
                'degree' => 'bachelor', 'specialization' => 'اللغة العربية',
                'university' => 'جامعة تشرين', 'graduation_year' => 2005, 'experience_years' => 19
            ],
            [
                'first_name' => 'منى', 'last_name' => 'سعيد',
                'degree' => 'master', 'specialization' => 'الإرشاد النفسي والتربوي',
                'university' => 'جامعة دمشق', 'graduation_year' => 2013, 'experience_years' => 11
            ],
            [
            'first_name' => 'محمد', 'last_name' => 'العلي',
            'degree' => 'phd', 'specialization' => 'الإدارة التربوية وتكنولوجيا التعليم',
            'university' => 'جامعة دمشق', 'graduation_year' => 2008, 'experience_years' => 18
           ],
        ];

        foreach ($profiles as $profile) {
            $staff = Staff::where('first_name', $profile['first_name'])
                          ->where('last_name', $profile['last_name'])
                          ->first();

            if ($staff) {
                AcademicProfile::create([
                    'staff_id'        => $staff->id,
                    'degree'          => $profile['degree'],
                    'specialization'  => $profile['specialization'],
                    'university'      => $profile['university'],
                    'graduation_year' => $profile['graduation_year'],
                    'experience_years'=> $profile['experience_years'],
                ]);
            }
        }
    }
}
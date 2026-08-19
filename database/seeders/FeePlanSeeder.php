<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FeePlan;
use App\Models\FeePlanExtraService;
use App\Models\AcademicYear;
use App\Models\GradeLevel;

class FeePlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. جلب السنة الدراسية النشطة
        $academicYear = AcademicYear::where('is_current', true)->first();

        if (!$academicYear) {
            $this->command->warn('تنبيه: لا يوجد سنة دراسية نشطة في قاعدة البيانات!');
            return;
        }

        // 2. جلب الصفوف الدراسية الموجودة فعلياً في قاعدة البيانات فقط
        $gradeLevels = GradeLevel::all();

        if ($gradeLevels->isEmpty()) {
            $this->command->warn('تنبيه: لا توجد صفوف دراسية في قاعدة البيانات! يرجى تشغيل GradeLevelSeeder أولاً.');
            return;
        }

        // 3. تحديد الرسوم الأساسية لكل صف بناءً على مستواه (level)
        $gradesFeeData = [
            7 => 1200000.00, // الصف السابع
            8 => 1300000.00, // الصف الثامن
            9 => 1600000.00, // الصف التاسع
        ];

        // 4. المرور على الصفوف وتوليد خطة مالية وخدماتها لكل منها
        foreach ($gradeLevels as $grade) {
            
            $baseAmount = $gradesFeeData[$grade->level] ?? 1000000.00;

            // إنشاء أو تحديث خطة الرسوم للصف
            $feePlan = FeePlan::updateOrCreate(
                [
                    'academic_year_id' => $academicYear->id,
                    'grade_level_id'   => $grade->id,
                ],
                [
                    'name'        => "خطة الرسوم لـ {$grade->name} ({$academicYear->name})",
                    'base_amount' => $baseAmount,
                ]
            );

            // 5. الخدمات الإضافية الموحدة لجميع الصفوف
            $services = [
                ['type' => 'books',      'name' => 'الكتب المدرسية والمقررات',    'amount' => 150000.00],
                ['type' => 'uniform',    'name' => 'الزي المدرسي الرسمي',         'amount' => 250000.00],
                ['type' => 'activities', 'name' => 'الأنشطة والرحلات الترفيهية',  'amount' => 75000.00],
                ['type' => 'insurance',  'name' => 'التأمين الصحي المدرسي',       'amount' => 50000.00],
            ];

            // 6. ربط الخدمات الإضافية بالخطة المالية
            foreach ($services as $service) {
                FeePlanExtraService::updateOrCreate(
                    [
                        'fee_plan_id' => $feePlan->id,
                        'type'        => $service['type'],
                    ],
                    [
                        'name'   => $service['name'],
                        'amount' => $service['amount'],
                    ]
                );
            }
        }

        $this->command->info('تم زراعة الخطط المالية والخدمات الموحدة بنجاح! ✅');
    }
}
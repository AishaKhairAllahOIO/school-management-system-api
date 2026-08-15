<?php

namespace Database\Seeders;

use App\Models\Staff;
use App\Models\AcademicYear;
use App\Models\StaffFinancialContract;
use Illuminate\Database\Seeder;

class StaffFinancialContractSeeder extends Seeder
{
    public function run(): void
    {
        $currentYear = AcademicYear::where('is_current', true)->first();
        $staffMembers = Staff::take(2)->get(); // سنجلب أول موظفين للتجربة

        if (!$currentYear || $staffMembers->count() < 2) return;

        // 1. عقد لموظف إداري (راتب مقطوع)
        StaffFinancialContract::updateOrCreate(
            ['staff_id' =>2, 'academic_year_id' => $currentYear->id],
            [
                'salary_type' => 'fixed_monthly',
                'salary_amount' => 3000.00, // 3000 دولار أو عملة محلية
                'start_date' => $currentYear->start_date,
                'end_date' => $currentYear->end_date,
            ]
        );

        // 2. عقد لمعلم (على الحصة)
        StaffFinancialContract::updateOrCreate(
            ['staff_id' => 1, 'academic_year_id' => $currentYear->id],
            [
                'salary_type' => 'per_period',
                'salary_amount' => 50.00, // 50 للحصة الواحدة
                'start_date' => $currentYear->start_date,
                'end_date' => $currentYear->end_date,
            ]
        );
    }
}
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
        // جلبنا موظفين اثنين لنجرب عليهم الحالتين
        $staffMembers = Staff::take(2)->get(); 

        if (!$currentYear || $staffMembers->count() < 2) return;

        // 1. الموظف الأول (راتب مقطوع شهرياً)
        StaffFinancialContract::updateOrCreate(
            ['staff_id' => $staffMembers[1]->id, 'academic_year_id' => $currentYear->id],
            [
                'salary_type'   => 'fixed_monthly',
                'salary_amount' => 3000.00, 
            ]
        );

        // 2. الموظف الثاني (راتب بناءً على الحصص المعطاة)
        // 💡 تذكري: يجب أن يكون لهذا الموظف سجل في جدول teacher_workloads وإلا لن يُصرف راتبه
        StaffFinancialContract::updateOrCreate(
            ['staff_id' => $staffMembers[0]->id, 'academic_year_id' => $currentYear->id],
            [
                'salary_type'   => 'per_period',
                'salary_amount' => 50.00, // أجرة الحصة الواحدة
            ]
        );
    }
}
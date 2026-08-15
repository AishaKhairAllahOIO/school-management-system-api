<?php

namespace Database\Seeders;

use App\Models\Payroll;
use App\Models\StaffFinancialContract;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class PayrollSeeder extends Seeder
{
    public function run(): void
    {
        $contracts = StaffFinancialContract::all();
        $lastMonth = Carbon::now()->subMonth(); // رواتب الشهر الماضي

        foreach ($contracts as $contract) {
            // راتب وهمي مبسط (في الواقع سيُحسب آلياً عبر PayrollService)
            // لكن هنا نضع داتا افتراضية لاختبار التقارير
            $netSalary = $contract->salary_type === 'fixed_monthly' ? $contract->salary_amount : 1500; // للمعلم افترضنا 1500

            Payroll::updateOrCreate(
                [
                    'staff_id' => $contract->staff_id,
                    'year' => $lastMonth->year,
                    'month' => $lastMonth->month,
                ],
                [
                    'contract_id' => $contract->id,
                    'payment_date' => $lastMonth->endOfMonth()->toDateString(),
                    'net_salary' => $netSalary,
                ]
            );
        }
    }
}
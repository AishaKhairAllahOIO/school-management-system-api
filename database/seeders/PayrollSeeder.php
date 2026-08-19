<?php

namespace Database\Seeders;

use App\Models\StaffFinancialContract;
use App\Services\Staff\PayrollService;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Exception;

class PayrollSeeder extends Seeder
{
    protected PayrollService $payrollService;

    public function __construct(PayrollService $payrollService)
    {
        $this->payrollService = $payrollService;
    }

    public function run(): void
    {
        $contracts = StaffFinancialContract::all();
        
        // 💡 التعديل الجوهري هنا: نحسب راتب (الشهر الحالي) لأن غياباتنا مزروعة فيه
        $targetMonth = Carbon::now(); 

        if ($contracts->isEmpty()) {
            $this->command->warn('لا يوجد عقود مالية للموظفين لإنشاء رواتب لها.');
            return;
        }

        foreach ($contracts as $contract) {
            try {
                $this->payrollService->commitSalary([
                    'staff_id'     => $contract->staff_id,
                    'year'         => $targetMonth->year,
                    'month'        => $targetMonth->month, // الآن سيبحث في نفس شهر الغياب!
                    'payment_date' => $targetMonth->endOfMonth()->toDateString(),
                ]);

                $this->command->info("تم صرف راتب الموظف رقم {$contract->staff_id} بنجاح مع تطبيق الخصميات إن وجدت.");

            } catch (Exception $e) {
                $this->command->error("خطأ في صرف راتب الموظف رقم {$contract->staff_id}: " . $e->getMessage());
            }
        }
    }
}
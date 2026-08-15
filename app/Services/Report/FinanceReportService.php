<?php

namespace App\Services\Report;

use App\Models\Payroll;
use App\Models\PaymentTransaction;
use App\Models\ScheduledInstallment;

class FinanceReportService
{
    /**
     * 💰 تقرير الإيرادات المجمل للطلاب (كشف تحصيل الأموال)
     */
    public function getOverallStudentsFinanceReport(): array
    {
        // تم التعديل لاستخدام amount_due بدلاً من amount
        $totalExpectedRevenue = ScheduledInstallment::sum('amount_due') ?? 0;

        // تم التعديل لاستخدام paid_amount بدلاً من amount
        $totalCollectedRevenue = PaymentTransaction::sum('paid_amount') ?? 0;
        $totalPaymentsCount = PaymentTransaction::count();

        $totalOutstanding = max(0, $totalExpectedRevenue - $totalCollectedRevenue);

        $collectionRate = $totalExpectedRevenue > 0 
            ? round(($totalCollectedRevenue / $totalExpectedRevenue) * 100, 1) 
            : 0;

        return [
            'total_expected_revenue'   => round($totalExpectedRevenue, 2),
            'total_collected_revenue'  => round($totalCollectedRevenue, 2),
            'total_outstanding_amount' => round($totalOutstanding, 2),
            'overall_collection_rate'  => $collectionRate,
            'total_payments_count'     => $totalPaymentsCount,
        ];
    }

    /**
     * 💸 تقرير المصروفات المجمل للموظفين والمعلمين (رواتب وخصميات)
     */
    public function getOverallStaffFinanceReport(): array
    {
        $totalPayrollsCount = Payroll::count();

        $totalNetSalariesPaid = Payroll::sum('net_salary') ?? 0;

        $averageSalary = $totalPayrollsCount > 0 
            ? round($totalNetSalariesPaid / $totalPayrollsCount, 2) 
            : 0;

        return [
            'total_payrolls_processed' => $totalPayrollsCount,
            'total_net_salaries_paid'  => round($totalNetSalariesPaid, 2),
            'average_salary_paid'      => $averageSalary,
        ];
    }
}
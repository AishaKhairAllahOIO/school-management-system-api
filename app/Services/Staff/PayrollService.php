<?php

namespace App\Services\Staff;

use App\Models\Payroll;
use App\Models\Staff;
use App\Models\StaffFinancialContract;
use App\Models\StaffAttendance;
use App\Models\TeacherPeriodAttendance;
use App\Models\TeacherWorkload;
use App\Services\User\AlertService;
use Illuminate\Support\Facades\DB;
use Exception;

class PayrollService
{
    protected AlertService $alertService;

    public function __construct(AlertService $alertService)
    {
        $this->alertService = $alertService;
    }


    public function previewSalary(int $staffId, int $year, int $month): array
    {
        $contract = StaffFinancialContract::where('staff_id', $staffId)->latest()->first();

        if (!$contract) {
            throw new Exception('لا يوجد عقد مالي مهيأ لهذا الموظف.', 404);
        }

        $alreadyPaid = Payroll::where('staff_id', $staffId)
            ->where('year', $year)
            ->where('month', $month)
            ->exists();

        if ($alreadyPaid) {
            throw new Exception("تم صرف واعتماد راتب شهر {$month} لعام {$year} لهذا الموظف مسبقاً.", 422);
        }

        $salaryType = $contract->salary_type;
        $rate = $contract->salary_amount;
        $deductions = 0;
        $netSalary = 0;
        $missedUnits = 0;
        $expectedUnits = 0;
        $deductionDetails = [];

        if ($salaryType === 'fixed_monthly') {
            $dailyRate = $rate / 30;

            $unexcusedAbsences = StaffAttendance::where('staff_id', $staffId)
                ->whereYear('attendance_date', $year)
                ->whereMonth('attendance_date', $month)
                ->where('status', 'absent')
                ->where('absence_type', 'unexcused')
                ->count();

            $unpaidLeaves = StaffAttendance::where('staff_id', $staffId)
                ->whereYear('attendance_date', $year)
                ->whereMonth('attendance_date', $month)
                ->where('status', 'on_leave')
                ->whereHas('leave.leaveType', function ($q) {
                    $q->where('payment_type', 'unpaid');
                })->count();

            $missedUnits = $unexcusedAbsences + $unpaidLeaves;
            $deductions = $missedUnits * $dailyRate;
            $netSalary = $rate - $deductions;
            $expectedUnits = 30; 

            if ($missedUnits > 0) {
                $deductionDetails[] = "تم خصم {$missedUnits} يوم (غياب غير مبرر أو إجازة غير مدفوعة).";
            }

        } else {

            $workload = TeacherWorkload::where('teacher_id', $staffId)
                ->where('academic_year_id', $contract->academic_year_id)
                ->first();

            if (!$workload) {
                throw new Exception("هذا المعلم ليس لديه نصاب (Workload) محدد في هذه السنة الدراسية، لا يمكن حساب راتبه.", 422);
            }

            $expectedUnits = $workload->assigned_monthly_periods;

            $missedPeriodsCount = TeacherPeriodAttendance::whereHas('dailyAttendance', function ($q) use ($staffId, $year, $month) {
                $q->where('staff_id', $staffId)
                    ->whereYear('attendance_date', $year)
                    ->whereMonth('attendance_date', $month)
                    ->where(function ($subQ) {
                        $subQ->whereIn('status', ['absent', 'partial_absence'])
                            ->where('absence_type', 'unexcused')
                            ->orWhere(function ($leaveQ) {
                                $leaveQ->where('status', 'on_leave')
                                    ->whereHas('leave.leaveType', function ($typeQ) {
                                        $typeQ->where('payment_type', 'unpaid');
                                    });
                            });
                    });
            })->count();

            $missedUnits = $missedPeriodsCount;
            $actualWorkedUnits = max(0, $expectedUnits - $missedUnits);

            $netSalary = $actualWorkedUnits * $rate;
            $deductions = $missedUnits * $rate;

            if ($missedUnits > 0) {
                $deductionDetails[] = "تم خصم {$missedUnits} حصص (بسبب غياب أو إجازة غير مدفوعة).";
            }
        }

        return [
            'staff_id' => $staffId,
            'contract_id' => $contract->id,
            'year' => $year,
            'month' => $month,
            'salary_type' => $salaryType,
            'contract_rate' => $rate,
            'expected_units' => $expectedUnits,
            'missed_units' => $missedUnits,
            'deductions' => round($deductions, 2),
            'net_salary' => round($netSalary, 2),
        ];
    }


    public function commitSalary(array $data): Payroll
    {
        return DB::transaction(function () use ($data) {

            $preview = $this->previewSalary(
                $data['staff_id'],
                $data['year'],
                $data['month']
            );

            $payroll = Payroll::create([
                'staff_id' => $preview['staff_id'],
                'contract_id' => $preview['contract_id'],
                'year' => $preview['year'],
                'month' => $preview['month'],
                'payment_date' => $data['payment_date'] ?? now()->toDateString(),
                'net_salary' => $preview['net_salary'],
            ]);

            $staff = Staff::find($preview['staff_id']);
            if ($staff) {
                $this->alertService->createStaffSalary($staff, [
                    'amount' => $preview['net_salary'],
                    'month' => $preview['month'],
                    'year' => $preview['year']
                ]);
            }

            return $payroll;
        });
    }


    public function getPayrollsByMonth(int $year, int $month)
    {
        return Payroll::with(['staff.user', 'contract'])
            ->where('year', $year)
            ->where('month', $month)
            ->get();
    }

    public function getPayrollById(int $id): Payroll
    {
        return Payroll::with(['staff.user', 'contract'])->findOrFail($id);
    }


    public function getStaffPayrolls(int $staffId)
    {
        return Payroll::with(['contract'])
            ->where('staff_id', $staffId)
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->get();
    }


    public function updatePayroll(int $id, array $data): Payroll
    {
        return DB::transaction(function () use ($id, $data) {
            $payroll = Payroll::findOrFail($id);

            $payroll->update([
                'payment_date' => $data['payment_date'] ?? $payroll->payment_date,
            ]);

            return $payroll->refresh()->load(['staff.user', 'contract']);
        });
    }


    public function deletePayroll(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $payroll = Payroll::findOrFail($id);
            $payroll->delete();

            return true;
        });
    }

    public function getMyPayrollHistory(int $staffId)
{
    return Payroll::query()
        ->where('staff_id', $staffId)
        ->with([
            'contract.academicYear',
        ])
        ->orderByDesc('year')
        ->orderByDesc('month')
        ->get()
        ->map(function ($payroll) {

            return [
                'id' => $payroll->id,

                'period' => [
                    'month' => $payroll->month,
                    'year' => $payroll->year,
                ],

                'salary' => [
                    'net_salary' => $payroll->net_salary,
                    'payment_date' => $payroll->payment_date,
                ],

                'contract' => [
                    'id' => $payroll->contract?->id,
                    'salary_type' => $payroll->contract?->salary_type,
                    'salary_amount' => $payroll->contract?->salary_amount,
                    'academic_year' => $payroll->contract?->academicYear?->year_name,
                ],
            ];

        });
}
}
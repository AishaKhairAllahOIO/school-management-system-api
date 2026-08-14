<?php 
namespace App\Services\Staff;
use App\Models\Payroll;
use App\Models\Staff;
use App\Models\StaffFinancialContract;
use App\Models\StaffAttendance;
use App\Models\TeacherPeriodAttendance;
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

    /**
     * 1️⃣ الحساب المبدئي للراتب (المعاينة قبل الحفظ)
     * يأخذ الموظف، السنة، الشهر، وإجمالي الوحدات المتوقعة (للمعلم: الحصص المتوقعة)
     */
    public function previewSalary(int $staffId, int $year, int $month, float $expectedUnits = 30): array
    {
        // 1. جلب العقد المالي الفعال للموظف (أحدث عقد)
        $contract = StaffFinancialContract::where('staff_id', $staffId)->latest()->first();

        if (!$contract) {
            throw new Exception('لا يوجد عقد مالي مهيأ لهذا الموظف.', 404);
        }

        // 2. التحقق من عدم صرف الراتب مسبقاً
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
        $missedUnits = 0; // أيام غياب للإداري، أو حصص فائتة للمعلم
        $deductionDetails = []; // لشرح سبب الخصم للمحاسب

        // 3. الحساب بناءً على نوع العقد
        if ($salaryType === 'fixed_monthly') {
            // -- حساب الإداري (راتب ثابت يخصم منه أيام الغياب) --
            $dailyRate = $rate / 30; // التسعيرة اليومية

            // جلب أيام الغياب غير المبرر في هذا الشهر
            $unexcusedAbsences = StaffAttendance::where('staff_id', $staffId)
                ->whereYear('attendance_date', $year)
                ->whereMonth('attendance_date', $month)
                ->where('status', 'absent')
                ->where('absence_type', 'unexcused')
                ->count();

            // جلب الإجازات غير المدفوعة (unpaid) في هذا الشهر
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

            if ($missedUnits > 0) {
                $deductionDetails[] = "تم خصم {$missedUnits} يوم (غياب غير مبرر أو إجازة غير مدفوعة).";
            }

        } else {
            // -- حساب المعلم (على الحصة) --
            // expectedUnits هنا تمثل (إجمالي الحصص التي كان يجب أن يعطيها في الشهر)

            // جلب الحصص التي غاب عنها (غير مبرر أو إجازة غير مدفوعة)
            $missedPeriodsCount = TeacherPeriodAttendance::whereHas('staffAttendance', function ($q) use ($staffId, $year, $month) {
                $q->where('staff_id', $staffId)
                  ->whereYear('attendance_date', $year)
                  ->whereMonth('attendance_date', $month)
                  ->where(function ($subQ) {
                      $subQ->where('status', 'absent')->where('absence_type', 'unexcused')
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
            // في نظام الحصة، الراتب الأساسي يتغير حسب العمل، الخصم هو الحصص الضائعة
            $deductions = $missedUnits * $rate; 

            if ($missedUnits > 0) {
                $deductionDetails[] = "تم خصم {$missedUnits} حصص (غياب غير مبرر أو إجازة غير مدفوعة).";
            }
        }

        // إرجاع مصفوفة المعاينة
        return [
            'staff_id'      => $staffId,
            'contract_id'   => $contract->id,
            'year'          => $year,
            'month'         => $month,
            'salary_type'   => $salaryType,
            'contract_rate' => $rate, // للقراءة فقط
            'expected_units'=> $expectedUnits,
            'missed_units'  => $missedUnits,
            'deductions'    => round($deductions, 2),
            'net_salary'    => round($netSalary, 2),
            'notes'         => implode(' | ', $deductionDetails),
        ];
    }

    /**
     * 2️⃣ اعتماد وصرف الراتب (حفظ في قاعدة البيانات)
     */
    public function commitSalary(array $data): Payroll
    {
        return DB::transaction(function () use ($data) {
            
            // إعادة استدعاء المعاينة برمجياً للتأكد من أن البيانات لم يتم التلاعب بها
            $preview = $this->previewSalary(
                $data['staff_id'], 
                $data['year'], 
                $data['month'], 
                $data['expected_units'] ?? 30
            );

            // إنشاء السجل في جدول الرواتب المبسط
            $payroll = Payroll::create([
                'staff_id'     => $preview['staff_id'],
                'contract_id'  => $preview['contract_id'],
                'year'         => $preview['year'],
                'month'        => $preview['month'],
                'payment_date' => $data['payment_date'] ?? now()->toDateString(),
                'net_salary'   => $preview['net_salary'],
                'notes'        => $data['notes'] ?? $preview['notes'], // نأخذ ملاحظات المحاسب أو ملاحظات النظام
            ]);

            // 🚀 إرسال إشعار الراتب للموظف آلياً!
            $staff = Staff::find($preview['staff_id']);
            if ($staff) {
                $this->alertService->createStaffSalary($staff, [
                    'amount' => $preview['net_salary'],
                    'month'  => $preview['month'],
                    'year'   => $preview['year']
                ]);
            }

            return $payroll;
        });
    }

    /**
     * جلب رواتب شهر معين لجميع الموظفين
     */
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

    /**
     * 4️⃣ جلب كشف حساب (سجل الرواتب) لموظف محدد عبر الزمن
     */
    public function getStaffPayrolls(int $staffId)
    {
        return Payroll::with(['contract'])
            ->where('staff_id', $staffId)
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->get();
    }

    /**
     * 5️⃣ تعديل سجل الراتب (Update)
     * 💡 معمارياً: يُسمح بتعديل تاريخ الدفع والملاحظات فقط للحفاظ على الدقة المالية
     */
    public function updatePayroll(int $id, array $data): Payroll
    {
        return DB::transaction(function () use ($id, $data) {
            $payroll = Payroll::findOrFail($id);

            // تحديث الحقول المسموحة فقط
            $payroll->update([
                'payment_date' => $data['payment_date'] ?? $payroll->payment_date,
                'notes'        => $data['notes'] ?? $payroll->notes,
                
                // إذا أردتِ السماح للمحاسب بتغيير الصافي يدوياً (تجاوز النظام)، ألغي التعليق عن السطر التالي:
                // 'net_salary'   => $data['net_salary'] ?? $payroll->net_salary,
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
}
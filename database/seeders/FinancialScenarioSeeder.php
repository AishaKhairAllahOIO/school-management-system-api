<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\AcademicYear;
use App\Models\FeePlan;
use App\Models\InstallmentPolicy;
use App\Models\FinancialAccount;
use App\Models\ScheduledInstallment;
use App\Models\PaymentTransaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FinancialScenarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            // 1. جلب المتطلبات الأساسية (بدون إنشائها لأنها موجودة مسبقاً)
            $academicYear = AcademicYear::where('is_current', true)->firstOrFail();
            $feePlan      = FeePlan::firstOrFail();
            $policy       = InstallmentPolicy::with('items')->firstOrFail();
            $accountant   = User::whereHas('roles', fn($q) => $q->where('name', 'super_admin')->orWhere('name', 'secretary'))->first(); // محاكي للمحاسب

            // 2. جلب 3 طلاب من الداتابيز (الذين ليس لديهم حساب مالي بعد)
            // سنستخدم whereDoesntHave لضمان أننا نختار طلاباً بلا محافظ مالية
            $students = Student::whereDoesntHave('financialAccount')->take(3)->get();

            if ($students->count() < 3) {
                $this->command->warn('تنبيه: لا يوجد 3 طلاب بدون حساب مالي في النظام لتطبيق السيناريو عليهم.');
                return;
            }

            // 3. تطبيق السيناريوهات على الطلاب الثلاثة
            $grandTotal = $feePlan->base_amount; // افترضنا عدم وجود خدمات إضافية للتبسيط
            $startYear  = Carbon::parse($academicYear->start_date)->year;

            foreach ($students as $index => $student) {
                
                // ==========================================
                // أ) إنشاء الحساب المالي (المحفظة) للطالب
                // ==========================================
                $account = FinancialAccount::create([
                    'student_id'            => $student->id,
                    'academic_year_id'      => $academicYear->id,
                    'fee_plan_id'           => $feePlan->id,
                    'installment_policy_id' => $policy->id,
                    'total_required_amount' => $grandTotal,
                    'remaining_balance'     => $grandTotal,
                    'payment_status'        => 'unpaid',
                ]);

                // ==========================================
                // ب) توليد الأقساط المجدولة بناءً على بنود السياسة
                // ==========================================
                $installments = [];
                foreach ($policy->items as $item) {
                    $amountDue = ($grandTotal * $item->percentage) / 100;
                    
                    // حساب سنة الاستحقاق (إذا كان الشهر بين 7 و 12 فهو في نفس سنة البداية، وإلا في السنة التالية)
                    $calcYear = ($item->due_month >= 7 && $item->due_month <= 12) ? $startYear : $startYear + 1;
                    $dueDate  = Carbon::createFromDate($calcYear, $item->due_month, $item->due_day)->format('Y-m-d');

                    $installments[] = ScheduledInstallment::create([
                        'financial_account_id' => $account->id,
                        'installment_number'   => $item->installment_number,
                        'title'                => $item->title,
                        'amount_due'           => $amountDue,
                        'amount_paid'          => 0.00,
                        'due_date'             => $dueDate,
                        'status'               => 'pending',
                    ]);
                }

                // ==========================================
                // ج) تطبيق سيناريوهات الدفع (Payment Transactions)
                // ==========================================
                
                // الطالب 0: لن يدفع شيئاً (يبقى Unpaid والأقساط Pending)
                
                // الطالب 1: سيدفع مبلغ القسط الأول فقط (Partially Paid)
                if ($index === 1) {
                    $paidAmount = $installments[0]->amount_due; // مبلغ القسط الأول

                    PaymentTransaction::create([
                        'financial_account_id' => $account->id,
                        'paid_amount'          => $paidAmount,
                        'payment_method'       => 'cash',
                        'paper_receipt_no'     => 'RCP-1001',
                        'collected_by_user_id' => $accountant?->id ?? 1,
                    ]);

                    // تحديث القسط الأول ليكون مدفوعاً
                    $installments[0]->update([
                        'amount_paid' => $paidAmount,
                        'status'      => 'paid',
                    ]);

                    // تحديث محفظة الطالب
                    $account->update([
                        'remaining_balance' => $account->total_required_amount - $paidAmount,
                        'payment_status'    => 'partially_paid',
                    ]);
                }

                // الطالب 2: سيدفع كامل المبلغ المترتب عليه (Fully Paid)
                if ($index === 2) {
                    $paidAmount = $grandTotal; // دفع كل شيء

                    PaymentTransaction::create([
                        'financial_account_id' => $account->id,
                        'paid_amount'          => $paidAmount,
                        'payment_method'       => 'bank_transfer',
                        'digital_reference'    => 'TRX-' . rand(1000, 9999),
                        'collected_by_user_id' => $accountant?->id ?? 1,
                    ]);

                    // تحديث جميع الأقساط لتكون مدفوعة
                    foreach ($installments as $installment) {
                        $installment->update([
                            'amount_paid' => $installment->amount_due,
                            'status'      => 'paid',
                        ]);
                    }

                    // تحديث محفظة الطالب وتصفير الديون
                    $account->update([
                        'remaining_balance' => 0,
                        'payment_status'    => 'fully_paid',
                    ]);
                }
            }
        });
    }
}
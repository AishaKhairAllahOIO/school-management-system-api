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

            // =========================================================
            // 1. المتطلبات الأساسية
            // =========================================================

            $academicYear = AcademicYear::where('is_current', true)
                ->firstOrFail();

            $feePlan = FeePlan::firstOrFail();

            /*
             * نستخدم سياسة الثلاث دفعات بشكل صريح.
             * لا نعتمد على first() لأن ترتيب السجلات غير مضمون.
             */
            $policy = InstallmentPolicy::where(
                'name',
                'تقسيط على ثلاث دفعات'
            )
                ->with('items')
                ->firstOrFail();

            /*
             * التأكد من وجود بنود السياسة.
             * هذا يمنع مشكلة Undefined array key 0.
             */
            if ($policy->items->isEmpty()) {
                throw new \RuntimeException(
                    'سياسة التقسيط لا تحتوي على أي بنود. تأكد من تشغيل InstallmentPolicySeeder أولاً.'
                );
            }

            /*
             * ترتيب الأقساط حسب رقم القسط.
             */
            $policyItems = $policy->items
                ->sortBy('installment_number')
                ->values();

            /*
             * نحتاج على الأقل إلى قسط واحد.
             */
            if ($policyItems->isEmpty()) {
                throw new \RuntimeException(
                    'لا توجد أقساط صالحة في سياسة التقسيط.'
                );
            }

            /*
             * المستخدم الذي سيقوم بتحصيل الدفعة.
             */
            $accountant = User::whereHas('roles', function ($query) {
                $query
                    ->where('name', 'super_admin')
                    ->orWhere('name', 'secretary');
            })->first();

            // =========================================================
            // 2. جلب 3 طلاب ليس لديهم حساب مالي
            // =========================================================

            $students = Student::whereDoesntHave('financialAccount')
                ->take(3)
                ->get();

            /*
             * إذا لم يوجد 3 طلاب، لا ننفذ السيناريو.
             */
            if ($students->count() < 3) {
                return;
            }

            // =========================================================
            // 3. البيانات العامة
            // =========================================================

            $grandTotal = (float) $feePlan->base_amount;

            $startYear = Carbon::parse(
                $academicYear->start_date
            )->year;

            // =========================================================
            // 4. إنشاء السيناريوهات
            // =========================================================

            foreach ($students->values() as $index => $student) {

                // =====================================================
                // أ) إنشاء الحساب المالي
                // =====================================================

                $account = FinancialAccount::create([
                    'student_id' => $student->id,
                    'academic_year_id' => $academicYear->id,
                    'fee_plan_id' => $feePlan->id,
                    'installment_policy_id' => $policy->id,
                    'total_required_amount' => $grandTotal,
                    'remaining_balance' => $grandTotal,
                    'payment_status' => 'unpaid',
                ]);

                // =====================================================
                // ب) إنشاء الأقساط
                // =====================================================

                $installments = collect();

                foreach ($policyItems as $item) {

                    $amountDue = round(
                        ($grandTotal * (float) $item->percentage) / 100,
                        2
                    );

                    /*
                     * إذا كان الشهر من 7 إلى 12:
                     * يكون في سنة بداية السنة الأكاديمية.
                     *
                     * وإذا كان من 1 إلى 6:
                     * يكون في السنة التالية.
                     */
                    $calcYear = (
                        $item->due_month >= 7 &&
                        $item->due_month <= 12
                    )
                        ? $startYear
                        : $startYear + 1;

                    $dueDate = Carbon::createFromDate(
                        $calcYear,
                        $item->due_month,
                        $item->due_day
                    )->format('Y-m-d');

                    $installment = ScheduledInstallment::create([
                        'financial_account_id' => $account->id,
                        'installment_number' => $item->installment_number,
                        'title' => $item->title,
                        'amount_due' => $amountDue,
                        'amount_paid' => 0.00,
                        'due_date' => $dueDate,
                        'status' => 'pending',
                    ]);

                    $installments->push($installment);
                }

                // =====================================================
                // ج) الطالب الأول
                // Unpaid
                // =====================================================

                if ($index === 0) {

                    /*
                     * لا توجد أي عملية دفع.
                     *
                     * الحساب:
                     * unpaid
                     *
                     * الأقساط:
                     * pending
                     */

                    continue;
                }

                // =====================================================
                // د) الطالب الثاني
                // Partially Paid
                // =====================================================

                if ($index === 1) {

                    /*
                     * نأخذ أول قسط بشكل آمن.
                     * لا يوجد وصول مباشر إلى [0] قبل التأكد من وجوده.
                     */
                    $firstInstallment = $installments->first();

                    if (!$firstInstallment) {
                        throw new \RuntimeException(
                            'تعذر إنشاء القسط الأول للطالب رقم ' . $student->id
                        );
                    }

                    $paidAmount = (float) $firstInstallment->amount_due;

                    // إنشاء عملية الدفع
                    PaymentTransaction::create([
                        'financial_account_id' => $account->id,
                        'paid_amount' => $paidAmount,
                        'payment_method' => 'cash',
                        'paper_receipt_no' => 'RCP-1001',
                        'collected_by_user_id' => $accountant?->id ?? 1,
                    ]);

                    // تحديث القسط الأول
                    $firstInstallment->update([
                        'amount_paid' => $paidAmount,
                        'status' => 'paid',
                    ]);

                    // تحديث الحساب المالي
                    $account->update([
                        'remaining_balance' => max(
                            0,
                            $grandTotal - $paidAmount
                        ),
                        'payment_status' => 'partially_paid',
                    ]);

                    continue;
                }

                // =====================================================
                // هـ) الطالب الثالث
                // Fully Paid
                // =====================================================

                if ($index === 2) {

                    $paidAmount = $grandTotal;

                    // إنشاء عملية الدفع
                    PaymentTransaction::create([
                        'financial_account_id' => $account->id,
                        'paid_amount' => $paidAmount,
                        'payment_method' => 'bank_transfer',
                        'digital_reference' => 'TRX-' . rand(1000, 9999),
                        'collected_by_user_id' => $accountant?->id ?? 1,
                    ]);

                    // تحديث جميع الأقساط
                    foreach ($installments as $installment) {

                        $installment->update([
                            'amount_paid' => $installment->amount_due,
                            'status' => 'paid',
                        ]);
                    }

                    // تحديث الحساب المالي
                    $account->update([
                        'remaining_balance' => 0,
                        'payment_status' => 'fully_paid',
                    ]);
                }
            }
        });
    }
}
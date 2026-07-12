<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\AcademicYear;
use App\Models\GradeLevel;
use App\Models\InstallmentPolicy;
use App\Models\FeePlan;
use App\Models\FinancialAccount;
use App\Models\ScheduledInstallment;
use App\Models\PaymentTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FinancialTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. التأكد من وجود طالب في النظام (نجلب أول طالب أو نطلب من المبرمج إضافة واحد)
        $student = Student::first();
        if (!$student) {
            $this->command->error('❌ يرجى إضافة طالب واحد على الأقل في النظام أولاً (عن طريق البوست مان أو الإكسل) لكي يعمل السيدر.');
            return;
        }

        // 2. جلب أو إنشاء عام دراسي وصف دراسي
        $year = AcademicYear::firstOrCreate(
            ['is_current' => true],
            ['year_name' => '2025-2026', 'start_date' => '2025-09-01', 'end_date' => '2026-06-30']
        );

        $grade = GradeLevel::firstOrCreate(
            ['level' => 1],
            ['academic_stage_id' => 1, 'name' => 'الصف التجريبي', 'is_graduation_grade' => false]
        );

        DB::transaction(function () use ($student, $year, $grade) {
            
            // 3. إنشاء سياسة تقسيط تجريبية
            $policy = InstallmentPolicy::updateOrCreate(
                ['name' => 'سياسة اختبار الكوماند (3 دفعات)'],
                ['installments_count' => 3]
            );

            // 4. إنشاء خطة مالية تجريبية (مليون ليرة)
            $plan = FeePlan::updateOrCreate(
                ['name' => 'خطة اختبار الكوماند'],
                [
                    'academic_year_id'      => $year->id,
                    'grade_level_id'        => $grade->id,
                    'base_amount'           => 1000000.00,
                ]
            );

            // 5. مسح أي حساب مالي قديم لهذا الطالب في هذه السنة لتجنب التضارب
            FinancialAccount::where('student_id', $student->id)->where('academic_year_id', $year->id)->delete();

            // 6. إنشاء المحفظة المالية للطالب
            $account = FinancialAccount::create([
                'student_id'                   => $student->id,
                'academic_year_id'             => $year->id,
                'fee_plan_id'                  => $plan->id,
                'installment_policy_id'        => $policy->id,
                'total_required_amount'        => 1000000.00,
                'remaining_balance'            => 600000.00, // افترضنا أنه دفع 400 ألف
                'payment_status'               => 'partially_paid',
            ]);

            // =========================================================
            // 🚀 السحر هنا: التلاعب بالزمن (Time Travel) لإنشاء الأقساط
            // =========================================================
            
            // القسط 1: مدفوع بالكامل (موعده كان الشهر الماضي) - الكوماند يجب أن يتجاهله
            $installment1 = ScheduledInstallment::create([
                'financial_account_id' => $account->id,
                'installment_number'   => 1,
                'title'                => 'الدفعة الأولى (مسددة)',
                'amount_due'           => 400000.00,
                'amount_paid'          => 400000.00,
                'due_date'             => Carbon::now()->subDays(30)->format('Y-m-d'),
                'status'               => 'paid',
            ]);

            PaymentTransaction::create([
                'financial_account_id' => $account->id,
                'paid_amount'          => 400000.00,
                'payment_method'       => 'cash',
                'paper_receipt_no'     => 'TEST-001',
            ]);

            // القسط 2: لم يُدفع، وموعده بعد 3 أيام تماماً - الكوماند يجب أن يرسل (تذكير)
            ScheduledInstallment::create([
                'financial_account_id' => $account->id,
                'installment_number'   => 2,
                'title'                => 'الدفعة الثانية (تستحق قريباً)',
                'amount_due'           => 300000.00,
                'amount_paid'          => 0.00,
                'due_date'             => Carbon::now()->addDays(3)->format('Y-m-d'), // 👈 بعد 3 أيام
                'status'               => 'pending',
            ]);

            // القسط 3: لم يُدفع، وموعده كان قبل 5 أيام - الكوماند يجب أن يحوله لـ overdue ويرسل (إنذار)
            ScheduledInstallment::create([
                'financial_account_id' => $account->id,
                'installment_number'   => 3,
                'title'                => 'الدفعة الثالثة (متأخرة)',
                'amount_due'           => 300000.00,
                'amount_paid'          => 0.00,
                'due_date'             => Carbon::now()->subDays(5)->format('Y-m-d'), // 👈 قبل 5 أيام
                'status'               => 'pending', // نتركها pending لكي يكتشفها الكوماند ويعدلها
            ]);

            $this->command->info('✅ student added successfuly ' . $student->first_name);
            $this->command->warn('👉 turn on this command: php artisan finance:check-installments');
        });
    }
}
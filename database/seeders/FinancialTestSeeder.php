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
        $student = Student::first();
        if (!$student) {
            $this->command->error('❌ يرجى إضافة طالب واحد على الأقل في النظام أولاً (عن طريق البوست مان أو الإكسل) لكي يعمل السيدر.');
            return;
        }

 



        DB::transaction(function () use ($student) {

            $policy = InstallmentPolicy::updateOrCreate(
                ['name' => 'تقسيط الرسوم الدراسية السنوي (3 دفعات)'],
                ['installments_count' => 3]
            );

            $plan = FeePlan::updateOrCreate(
                ['name' => 'الرسوم الدراسية السنوية'],
                [
                    'academic_year_id'      => 1,
                    'grade_level_id'        => 1,
                    'base_amount'           => 1000000.00,
                ]
            );

            FinancialAccount::where('student_id', $student->id)->where('academic_year_id', 1)->delete();

            $account = FinancialAccount::create([
                'student_id'                   => $student->id,
                'academic_year_id'             => 1,
                'fee_plan_id'                  => $plan->id,
                'installment_policy_id'        => $policy->id,
                'total_required_amount'        => 1000000.00,
                'remaining_balance'            => 600000.00,
                'payment_status'               => 'partially_paid',
            ]);

            $installment1 = ScheduledInstallment::create([
                'financial_account_id' => $account->id,
                'installment_number'   => 1,
                'title'                => 'الدفعة الأولى (رسوم التسجيل)',
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

            ScheduledInstallment::create([
                'financial_account_id' => $account->id,
                'installment_number'   => 2,
                'title'                => 'الدفعة الثانية (منتصف العام الدراسي)',
                'amount_due'           => 300000.00,
                'amount_paid'          => 0.00,
                'due_date'             => Carbon::now()->addDays(3)->format('Y-m-d'), 
                'status'               => 'pending',
            ]);

            ScheduledInstallment::create([
                'financial_account_id' => $account->id,
                'installment_number'   => 3,
                'title'                => 'الدفعة الثالثة (الدفعة الختامية)',
                'amount_due'           => 300000.00,
                'amount_paid'          => 0.00,
                'due_date'             => Carbon::now()->subDays(5)->format('Y-m-d'), 
                'status'               => 'pending', 
            ]);

            $this->command->info('✅ student added successfuly ' . $student->first_name);
            $this->command->warn('👉 turn on this command: php artisan finance:check-installments');
        });
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ScheduledInstallment;
use App\Services\User\AlertService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CheckInstallmentsCommand extends Command
{
    protected $signature = 'finance:check-installments';

    protected $description = 'Check financial installments and automatically send reminder and overdue alerts to parents';

    public function handle(AlertService $alertService)
    {
        $this->info('Started checking financial installments for today: ' . now()->format('Y-m-d'));

        $today = Carbon::today();
        $targetReminderDate = $today->copy()->addDays(3);

        $remindersCount = 0;
        $overdueCount = 0;

        // 1. تذكير الأقساط القادمة (قبل موعدها بـ 3 أيام)
        $upcomingInstallments = ScheduledInstallment::with('account.student.enrollments')
            ->where('status', 'pending')
            ->whereDate('due_date', $targetReminderDate)
            ->get();

        foreach ($upcomingInstallments as $installment) {
            $enrollment = $installment->account->student->enrollments()->latest()->first();

            if ($enrollment) {
                $alertService->createStudentPayment($enrollment, [
                    'amount_due'     => $installment->amount_due - $installment->amount_paid,
                    'due_date'       => $installment->due_date->format('Y-m-d'),
                    'installment_id' => $installment->id,
                    'is_overdue'     => false
                ]);
                $remindersCount++;
            }
        }
        $this->info("✅ Successfully sent {$remindersCount} payment reminder notices.");

        // 2. فحص الأقساط المتأخرة (التي مر تاريخ استحقاقها ولم تدفع)
        $overdueInstallments = ScheduledInstallment::with('account.student.enrollments')
            ->where('status', 'pending')
            ->whereDate('due_date', '<', $today)
            ->get();

        foreach ($overdueInstallments as $installment) {
            $installment->update(['status' => 'overdue']);

            $enrollment = $installment->account->student->enrollments()->latest()->first();

            if ($enrollment) {
                $alertService->createStudentPayment($enrollment, [
                    'amount_due'     => $installment->amount_due - $installment->amount_paid,
                    'due_date'       => $installment->due_date->format('Y-m-d'),
                    'installment_id' => $installment->id,
                    'is_overdue'     => true 
                ]);
                $overdueCount++;
            }
        }
        $this->info("🚨 Converted {$overdueCount} installments to overdue status and sent warnings to parents.");

        Log::info("Finance Check Installments Command ran successfully. Reminders: {$remindersCount}, Overdue: {$overdueCount}");

        return Command::SUCCESS;
    }
}
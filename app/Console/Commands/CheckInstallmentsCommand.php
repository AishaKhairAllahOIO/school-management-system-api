<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ScheduledInstallment;
use App\Services\User\AlertService; 
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CheckInstallmentsCommand extends Command
{
    // 1. اسم الأمر الذي سنكتبه في التيرمينال لتشغيله
    protected $signature = 'finance:check-installments';
    
    // 2. وصف الأمر (يظهر عند كتابة php artisan list)
    protected $description = 'فحص الأقساط المالية وإرسال إشعارات التذكير والتأخير آلياً لآباء الطلاب';

    /**
     * الدالة الرئيسية التي يتم تنفيذها عند تشغيل الأمر
     */
    public function handle(AlertService $alertService)
    {
        // هذه الجملة ستنطبع في الشاشة السوداء (التيرمينال) عند التشغيل
        $this->info('بدأ فحص الأقساط المالية لليوم: ' . now()->format('Y-m-d'));

        $today = Carbon::today();
        $targetReminderDate = $today->copy()->addDays(3); // تذكير قبل 3 أيام من الاستحقاق

        $remindersCount = 0;
        $overdueCount = 0;

        // =========================================================
        // 🟡 أولاً: إرسال تذكير للأقساط التي اقترب موعدها (بعد 3 أيام)
        // =========================================================
        $upcomingInstallments = ScheduledInstallment::with('account.student.enrollments')
            ->where('status', 'pending')
            ->whereDate('due_date', $targetReminderDate) 
            ->get();

        foreach ($upcomingInstallments as $installment) {
            $enrollment = $installment->account->student->enrollments()->latest()->first();
            
            if ($enrollment) {
                // استدعاء خدمة التنبيهات لإرسال إشعار "تذكير"
                $alertService->createStudentPayment($enrollment, [
                    'amount_due'     => $installment->amount_due - $installment->amount_paid,
                    'due_date'       => $installment->due_date->format('Y-m-d'),
                    'installment_id' => $installment->id,
                    'is_overdue'     => false // ميتاداتا للفرونت إند: هذا تذكير وليس تأخير
                ]);
                $remindersCount++;
            }
        }
        $this->info("✅ تم إرسال {$remindersCount} إشعارات تذكير.");

        // =========================================================
        // 🔴 ثانياً: تحديث وإرسال إنذار للأقساط التي انقضى موعدها (متأخرة)
        // =========================================================
        $overdueInstallments = ScheduledInstallment::with('account.student.enrollments')
            ->where('status', 'pending') // كان بانتظار الدفع
            ->whereDate('due_date', '<', $today) // وأصبح تاريخ استحقاقه في الماضي
            ->get();

        foreach ($overdueInstallments as $installment) {
            // 1. تحويل حالة القسط في الداتابيز إلى "متأخر"
            $installment->update(['status' => 'overdue']);

            $enrollment = $installment->account->student->enrollments()->latest()->first();
            
            if ($enrollment) {
                // 2. استدعاء خدمة التنبيهات لإرسال إشعار "تأخير"
                $alertService->createStudentPayment($enrollment, [
                    'amount_due'     => $installment->amount_due - $installment->amount_paid,
                    'due_date'       => $installment->due_date->format('Y-m-d'),
                    'installment_id' => $installment->id,
                    'is_overdue'     => true // ميتاداتا للفرونت إند: هذا إنذار تأخير!
                ]);
                $overdueCount++;
            }
        }
        $this->info("🚨 تم تحويل {$overdueCount} أقساط إلى متأخرة وإرسال إنذارات لأولياء الأمور.");

        Log::info("Finance Check Installments Command ran successfully. Reminders: {$remindersCount}, Overdue: {$overdueCount}");

        return Command::SUCCESS;
    }
}
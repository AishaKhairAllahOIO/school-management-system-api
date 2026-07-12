<?php

namespace App\Services\Finance;

use App\Models\FinancialAccount;
use App\Models\PaymentTransaction;
use App\Models\ScheduledInstallment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;
use App\Models\Alert;
use App\Services\Notification\PushNotificationService;
use Illuminate\Support\Facades\Log; // 👈 استدعاء الـ Log لتسجيل أخطاء الفايربيز بصمت
use App\Services\User\AlertService;


class PaymentService
{
    
    public function __construct(private AlertService $alertService) {}

    public function getAllPayments(array $filters = [])
    {
        $query = PaymentTransaction::with(['account.student.user'])->latest();

        // هنا يمكنك إضافة فلاتر مستقبلاً (مثلاً البحث برقم الإيصال أو طريقة الدفع)
        if (!empty($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }

        return $query->get();
    }

    /**
     * 🔍 جلب إيصال دفع محدد
     */
    public function getPaymentById(int $id)
    {
        return PaymentTransaction::with(['account.student.user'])->findOrFail($id);
    }

    public function processPayment(array $data): array
    {
        return DB::transaction(function () use ($data) {
            
            // 1. جلب المحفظة المالية الفعالة للطالب
            $account = FinancialAccount::where('student_id', $data['studentId'])
                                       ->whereIn('payment_status', ['unpaid', 'partially_paid'])
                                       ->first();

            if (!$account) {
                throw new Exception('لا يوجد حساب مالي نشط أو غير مدفوع لهذا الطالب.', 404);
            }

            $paidAmount = (float) $data['paidAmount'];

            // 2. حماية: منع الدفع بأكثر من الرصيد المتبقي
            if ($paidAmount > $account->remaining_balance) {
                throw new Exception("المبلغ المدفوع ($paidAmount) أكبر من الرصيد المتبقي على الطالب ({$account->remaining_balance}).", 422);
            }


            $pendingInstallments = ScheduledInstallment::where('financial_account_id', $account->id)
                ->whereIn('status', ['pending', 'overdue']) // قد نستخدم overdue لاحقاً
                ->orderBy('due_date', 'asc')
                ->get();

            $remainingMoneyToDistribute = $paidAmount;

            foreach ($pendingInstallments as $installment) {
                if ($remainingMoneyToDistribute <= 0) break; // نفد مبلغ الدفعة

                // كم يتبقى لسداد هذا القسط تحديداً؟
                $amountNeededForThisInstallment = $installment->amount_due - $installment->amount_paid;

                if ($remainingMoneyToDistribute >= $amountNeededForThisInstallment) {
                    // المبلغ يكفي لإغلاق هذا القسط بالكامل
                    $installment->update([
                        'amount_paid' => $installment->amount_due,
                        'status'      => 'paid'
                    ]);
                    $remainingMoneyToDistribute -= $amountNeededForThisInstallment;
                } else {
                    // المبلغ لا يكفي لإغلاق القسط، سيسدد جزءاً منه فقط
                    $installment->update([
                        'amount_paid' => $installment->amount_paid + $remainingMoneyToDistribute,
                        // يبقى status كما هو (pending) أو يمكننا إضافة حالة (partially_paid) للقسط مستقبلاً
                    ]);
                    $remainingMoneyToDistribute = 0;
                }
            }

            // =========================================================
            // 💰 4. تحديث المحفظة وإصدار الإيصال
            // =========================================================
            
            $newRemainingBalance = $account->remaining_balance - $paidAmount;
            
            $account->update([
                'remaining_balance' => $newRemainingBalance,
                'payment_status'    => $newRemainingBalance == 0 ? 'fully_paid' : 'partially_paid',
            ]);

            // إنشاء إيصال الدفع (Transaction)
            $transaction = PaymentTransaction::create([
                'financial_account_id' => $account->id,
                'paid_amount'          => $paidAmount,
                'payment_method'       => $data['paymentMethod'],
                'paper_receipt_no'     => $data['paperReceiptNo'] ?? null,
                'digital_reference'    => $data['digitalReference'] ?? null,
                'collected_by_user_id' => Auth::id(), // المحاسب الذي قام بالعملية
            ]);
           
            $enrollment = $account->student->enrollments()->latest()->first();

            if ($enrollment) {
                // =============================
                // تحديث Enrollment status
                // enrolled  => تم سداد دفعة واحدة على الأقل (والطالب ما زال لديه التزامات)
                // completed => تم سداد كل القسط/القسط المترتبط عليه
                // =============================

                $allInstallmentsPaid = ScheduledInstallment::where('financial_account_id', $account->id)
                    ->whereRaw('amount_due = amount_paid')
                    ->count() > 0;

                $hasOutstandingInstallments = ScheduledInstallment::where('financial_account_id', $account->id)
                    ->whereRaw('amount_due > amount_paid')
                    ->exists();

                $newEnrollmentStatus = null;
                $enrollmentMeta = [
                    'amount'            => $transaction->paid_amount,
                    'transaction_id'    => $transaction->id,
                    'remaining_balance' => $account->remaining_balance,
                ];

                if (!$hasOutstandingInstallments && ($account->remaining_balance ?? 0) == 0 && $allInstallmentsPaid) {
                    // completed
                    $newEnrollmentStatus = 'completed';
                    $enrollmentMeta['completed_at'] = now()->toDateTimeString();
                } else {
                    // enrolled (دفعة واحدة على الأقل)
                    $newEnrollmentStatus = 'enrolled';
                    if (empty($enrollment->enrollment_date)) {
                        $enrollmentMeta['enrollment_date'] = now()->toDateString();
                    }
                }

                $enrollmentUpdate = [
                    'enrollment_status' => $newEnrollmentStatus,
                ];

                if ($newEnrollmentStatus === 'enrolled') {
                    $enrollmentUpdate['enrollment_date'] = $enrollment->enrollment_date ?? now()->toDateString();
                }

                if ($newEnrollmentStatus === 'completed') {
                    $enrollmentUpdate['completed_at'] = $enrollment->completed_at ?? now();
                }

                $enrollment->update($enrollmentUpdate);

                // =============================
                // إرسال إشعار لولي الأمر
                // =============================
                // يتم استخدام createStudentPayed كإشعار دفع (موجود حاليا)
                // مع تضمين حالة الانتقال في meta
                $this->alertService->createStudentPayed($enrollment->fresh(), [
                    ...$enrollmentMeta,
                    'new_enrollment_status' => $newEnrollmentStatus,
                ]);
            }


            // إعادة الإيصال + حالة الحساب المحدثة
            return [
                'transaction' => $transaction,
                'account'     => $account->fresh('scheduledInstallments')
            ];
        });
    }
}
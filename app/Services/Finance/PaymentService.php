<?php

namespace App\Services\Finance;

use App\Models\FinancialAccount;
use App\Models\PaymentTransaction;
use App\Models\ScheduledInstallment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;
use App\Models\Alert;

class PaymentService
{
    public function getAllPayments(array $filters = [])
    {
        $query = PaymentTransaction::with(['financialAccount.student.user', 'cashier'])->latest();

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
        return PaymentTransaction::with(['financialAccount.student.user', 'cashier'])->findOrFail($id);
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
            $guardianUser = $account->student->guardian->user;
            $student = $account->student->user;
            
            if ($guardianUser) {
                Alert::create([
                    'notifiable_type' => get_class($guardianUser),
                    'notifiable_id'   => $guardianUser->id,
                    'type'            => 'payment_received',
                    'audience'        => 'guardian',
                    'title'           => 'تأكيد استلام دفعة مالية',
                    'description'     => "تم استلام مبلغ {$transaction->paid_amount} ل.س لحساب الطالب {$student->first_name}.",
                    'meta'            => [
                        'transaction_id'    => $transaction->id,
                        'paid_amount'       => $transaction->paid_amount,
                        'remaining_balance' => $account->remaining_balance,
                        'student_id'        => $student->id
                    ],
                    'created_by'      => Auth::id() // الموظف الذي أصدر الإشعار
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
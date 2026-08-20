<?php

namespace App\Services\Finance;

use App\Models\FinancialAccount;
use App\Models\FeePlan;
use App\Models\InstallmentPolicy;
use App\Models\ScheduledInstallment;
use App\Models\AcademicYear;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;
use App\Models\FeePlanExtraService;

class FinancialContractService
{

    public function getAllAccounts(array $filters = [])
    {
        $query = FinancialAccount::with(['student.user', 'feePlan', 'installmentPolicy', 'scheduledInstallments'])
            ->latest();

        if (!empty($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }

        if (!empty($filters['academic_year_id'])) {
            $query->where('academic_year_id', $filters['academic_year_id']);
        }

        return $query->get();
    }
    public function getAccountByStudentId(int $studentId): FinancialAccount
    {
        return FinancialAccount::with(['student.user', 'feePlan', 'installmentPolicy', 'scheduledInstallments'])
            ->where('student_id', $studentId)
            ->firstOrFail();
    }
    public function getAllInstallments(array $filters = [])
    {
        $query = ScheduledInstallment::with(['account.student.user'])->orderBy('due_date', 'asc');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->get();
    }
    public function getInstallmentById(int $id): ScheduledInstallment
    {
        return ScheduledInstallment::with(['account.student.user', 'account.feePlan'])->findOrFail($id);
    }
    public function finalizeContract(array $data): FinancialAccount
    {
        return DB::transaction(function () use ($data) {

            $account = FinancialAccount::where('student_id', $data['studentId'])
                ->where('academic_year_id', $data['academicYearId'])
                ->firstOrFail();

            if ($account->payment_status !== 'draft') {
throw new Exception(
    'The contract cannot be finalized because this account has already been contracted.'
);            }

            $feePlan = FeePlan::with('extraServices')->findOrFail($data['feePlanId']);
            $policy = InstallmentPolicy::with('items')->findOrFail($data['installmentPolicyId']);

            $baseAmount = $feePlan->base_amount;

            $extraServicesAmount = 0;
            if (!empty($data['selectedExtraServiceIds'])) {
                $extraServicesAmount = $feePlan->extraServices
                    ->whereIn('id', $data['selectedExtraServiceIds'])
                    ->sum('amount');
            }

            $grandTotal = $baseAmount + $extraServicesAmount;

            $account->update([
                'fee_plan_id' => $feePlan->id,
                'installment_policy_id' => $policy->id,
                'total_required_amount' => $grandTotal,
                'remaining_balance' => $grandTotal,
                'payment_status' => 'unpaid',
            ]);

            $academicYear = AcademicYear::findOrFail($account->academic_year_id);
            $startYear = Carbon::parse($academicYear->start_date)->year;

            $installmentsToInsert = [];

            foreach ($policy->items as $item) {
                $amountDue = ($grandTotal * $item->percentage) / 100;

                $calcYear = ($item->due_month >= 7 && $item->due_month <= 12)
                    ? $startYear
                    : $startYear + 1;

                $dueDate = Carbon::createFromDate($calcYear, $item->due_month, $item->due_day)->format('Y-m-d');

                $installmentsToInsert[] = [
                    'financial_account_id' => $account->id,
                    'installment_number' => $item->installment_number,
                    'title' => $item->title,
                    'amount_due' => $amountDue,
                    'amount_paid' => 0.00,
                    'due_date' => $dueDate,
                    'status' => 'pending',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            ScheduledInstallment::insert($installmentsToInsert);

            return $account->fresh(['scheduledInstallments', 'feePlan', 'installmentPolicy']);
        });
    }
    public function updateContract(int $accountId, array $data): FinancialAccount
    {
        return DB::transaction(function () use ($accountId, $data) {
            $account = FinancialAccount::findOrFail($accountId);
            if (!$account)
throw new ModelNotFoundException(
    'The financial account was not found.'
);
            if (!in_array($account->payment_status, ['draft', 'unpaid'])) {
throw new Exception(
    'The financial contract cannot be modified because payments have already been made. Please perform a financial settlement instead.',
    422
);         }

            ScheduledInstallment::where('financial_account_id', $account->id)->delete();

            return $this->processContractCreation($account, $data);
        });
    }
    private function processContractCreation(FinancialAccount $account, array $data): FinancialAccount
    {
        $feePlan = FeePlan::findOrFail($data['feePlanId']);
        $policy = InstallmentPolicy::with('items')->findOrFail($data['installmentPolicyId']);

        $baseAmount = $feePlan->base_amount;
        $extraServicesTotal = 0;

        if (!empty($data['selectedExtraServiceIds'])) {
            $extraServicesTotal = FeePlanExtraService::whereIn('id', $data['selectedExtraServiceIds'])
                ->where('fee_plan_id', $feePlan->id)
                ->sum('amount');
        }

        $grandTotal = $baseAmount + $extraServicesTotal;

        $account->update([
            'fee_plan_id' => $feePlan->id,
            'installment_policy_id' => $policy->id,
            'total_required_amount' => $grandTotal,
            'remaining_balance' => $grandTotal,
            'payment_status' => 'unpaid',
        ]);

        $academicYear = AcademicYear::findOrFail($account->academic_year_id);
        $startYear = Carbon::parse($academicYear->start_date)->year;

        $installmentsToInsert = [];

        foreach ($policy->items as $item) {
            $amountDue = ($grandTotal * $item->percentage) / 100;

            $calcYear = ($item->due_month >= 7 && $item->due_month <= 12)
                ? $startYear
                : $startYear + 1;

            $dueDate = Carbon::createFromDate($calcYear, $item->due_month, $item->due_day)->format('Y-m-d');

            $installmentsToInsert[] = [
                'financial_account_id' => $account->id,
                'installment_number' => $item->installment_number,
                'title' => $item->title,
                'amount_due' => $amountDue,
                'amount_paid' => 0.00,
                'due_date' => $dueDate,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        ScheduledInstallment::insert($installmentsToInsert);

        return $account->fresh('scheduledInstallments');
    }
         public function getInstallmentsByStudentId(int $studentId)
    {
        $account = FinancialAccount::where('student_id', $studentId)->firstOrFail();

        return ScheduledInstallment::where('financial_account_id', $account->id)->get();
    }
}
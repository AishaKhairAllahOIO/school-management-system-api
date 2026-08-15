<?php

namespace App\Services\Finance;

use App\Models\Guardian;
use App\Models\FinancialAccount;
use Exception;

class GuardianFinanceService
{


    public function getChildrenFinancialSummary(int $guardianId)
    {
        return Guardian::findOrFail($guardianId)
            ->students()
            ->with([
                'user',
                'financialAccount'
            ])
            ->get()
            ->map(function ($student) {

                $account = $student->financialAccount;

                return [
                    'student_id' => $student->id,

                    'student' => [
                        'id' => $student->id,
                        'name' => $student->user->first_name . ' ' . $student->user->last_name,
                        'photo_url' => $student->user->photo_url,
                    ],


                    'financial_status' => $account ? [

                        'account_id' => $account->id,

                        'total_amount' => $account->total_required_amount,

                        'paid_amount' => 
                            $account->total_required_amount 
                            - $account->remaining_balance,

                        'remaining_balance' => $account->remaining_balance,

                        'payment_status' => $account->payment_status,

                    ] : null,
                ];

            });
    }




    public function getChildFinancialDetails(
        int $guardianId,
        int $studentId
    )
    {

        $account = FinancialAccount::query()
            ->where('student_id',$studentId)

            ->whereHas('student',function($q) use ($guardianId){
                $q->where('guardian_id',$guardianId);
            })

            ->with([
                'feePlan',
                'installmentPolicy',
                'scheduledInstallments',
                'Transactions',
            ])

            ->first();


        if(!$account){
            throw new Exception(
                'Financial account not found for this student.'
            );
        }


        return $account;
    }


}
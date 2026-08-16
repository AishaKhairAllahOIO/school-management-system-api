<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

use App\Models\Student;
use App\Models\FinancialAccount;
use App\Models\ScheduledInstallment;
use App\Models\PaymentTransaction;
use App\Models\FeePlan;
use App\Models\InstallmentPolicy;
use App\Models\Alert;
use App\Models\Enrollment;

class StudentFinancialSeeder extends Seeder
{

    public function run(): void
    {

        // DB::transaction(function () {


        //     $students = Student::whereIn('id',[1,3,2])->get();




        //     foreach ($students as $student) {


        //         $account = FinancialAccount::firstOrCreate(
        //             [
        //                 'student_id'=>$student->id,
        //                 'academic_year_id'=>1,
        //             ],
        //             [
        //                 'fee_plan_id'=>FeePlan::first()?->id,

        //                 'installment_policy_id'
        //                     =>InstallmentPolicy::first()?->id,


        //                 'total_required_amount'=>3000,

        //                 'remaining_balance'=>3000,

        //                 'payment_status'=>'unpaid',
        //             ]
        //         );





        //         $installments = [

        //             [
        //                 'number'=>1,
        //                 'title'=>'القسط الأول - رسوم التسجيل',
        //                 'amount'=>1000,
        //             ],

        //             [
        //                 'number'=>2,
        //                 'title'=>'القسط الثاني',
        //                 'amount'=>1000,
        //             ],

        //             [
        //                 'number'=>3,
        //                 'title'=>'القسط الثالث',
        //                 'amount'=>1000,
        //             ],

        //         ];



        //         foreach($installments as $item){


        //             ScheduledInstallment::firstOrCreate(
        //                 [
        //                     'financial_account_id'=>$account->id,

        //                     'installment_number'
        //                         =>$item['number'],
        //                 ],
        //                 [

        //                     'title'=>$item['title'],

        //                     'amount_due'=>$item['amount'],

        //                     'amount_paid'=>0,

        //                     'status'=>'pending',

        //                     'due_date'=>now()
        //                         ->addMonths($item['number'])
        //                         ->toDateString(),

        //                 ]
        //             );

        //         }




        //         /*
        //          | إنشاء دفعة مالية
        //          */


        //         $transaction = PaymentTransaction::create([


        //             'financial_account_id'=>$account->id,


        //             'paid_amount'=>1000,


        //             'payment_method'=>'cash',


        //             'paper_receipt_no'
        //                 =>'REC-'.$student->id.'-001',


        //             'collected_by_user_id'=>1,


        //         ]);




        //         ScheduledInstallment::where(
        //             'financial_account_id',
        //             $account->id
        //         )
        //         ->where(
        //             'installment_number',
        //             1
        //         )
        //         ->update([

        //             'amount_paid'=>1000,

        //             'status'=>'paid',

        //         ]);




        //         $account->update([

        //             'remaining_balance'=>2000,

        //             'payment_status'=>'partially_paid',

        //         ]);


        //     }


        // });

    }

}

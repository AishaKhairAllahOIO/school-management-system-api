<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\InstallmentPolicyItem;
use App\Models\InstallmentPolicy;

class InstallmentPolicySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $policy1 = InstallmentPolicy::UpdateOrCreate([
            'name'               => 'الدفع الكامل (دفعة واحدة)',
            'installments_count' => 1,
        ]);

        InstallmentPolicyItem::UpdateOrCreate([
            'installment_policy_id' => $policy1->id,
            'installment_number'    => 1,
            'title'                 => 'الدفعة الشاملة',
            'percentage'            => 100.00,
            'due_month'             => 9, // سبتمبر
            'due_day'               => 1,
        ]);

        // 2. سياسة التقسيط على دفعتين (نصفين 50% - 50%)
        $policy2 = InstallmentPolicy::UpdateOrCreate([
            'name'               => 'تقسيط على دفعتين (نصفين)',
            'installments_count' => 2,
        ]);

        InstallmentPolicyItem::UpdateOrCreate([
            'installment_policy_id' => $policy2->id,
            'installment_number'    => 1,
            'title'                 => 'الدفعة الأولى (التأسيسية)',
            'percentage'            => 50.00,
            'due_month'             => 9, // منتصف سبتمبر
            'due_day'               => 15,
        ]);

        InstallmentPolicyItem::UpdateOrCreate([
            'installment_policy_id' => $policy2->id,
            'installment_number'    => 2,
            'title'                 => 'الدفعة الثانية (الختامية)',
            'percentage'            => 50.00,
            'due_month'             => 1, // منتصف يناير
            'due_day'               => 15,
        ]);

        // 3. سياسة التقسيط على ثلاث دفعات (40% - 30% - 30%)
        $policy3 = InstallmentPolicy::UpdateOrCreate([
            'name'               => 'تقسيط على ثلاث دفعات',
            'installments_count' => 3,
        ]);

        InstallmentPolicyItem::UpdateOrCreate([
            'installment_policy_id' => $policy3->id,
            'installment_number'    => 1,
            'title'                 => 'الدفعة الأولى',
            'percentage'            => 40.00,
            'due_month'             => 9, // منتصف سبتمبر
            'due_day'               => 15,
        ]);

        InstallmentPolicyItem::UpdateOrCreate([
            'installment_policy_id' => $policy3->id,
            'installment_number'    => 2,
            'title'                 => 'الدفعة الثانية',
            'percentage'            => 30.00,
            'due_month'             => 12, // منتصف ديسمبر
            'due_day'               => 15,
        ]);

        InstallmentPolicyItem::UpdateOrCreate([
            'installment_policy_id' => $policy3->id,
            'installment_number'    => 3,
            'title'                 => 'الدفعة الثالثة',
            'percentage'            => 30.00,
            'due_month'             => 3, // منتصف مارس
            'due_day'               => 15,
        ]);
    }
}

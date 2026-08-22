<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InstallmentPolicy;
use App\Models\InstallmentPolicyItem;

class InstallmentPolicySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // =========================================================
        // 1. الدفع الكامل - دفعة واحدة
        // =========================================================

        $policy1 = InstallmentPolicy::updateOrCreate(
            [
                'name' => 'الدفع الكامل (دفعة واحدة)',
            ],
            [
                'installments_count' => 1,
            ]
        );

        InstallmentPolicyItem::updateOrCreate(
            [
                'installment_policy_id' => $policy1->id,
                'installment_number' => 1,
            ],
            [
                'title' => 'الدفعة الشاملة',
                'percentage' => 100.00,
                'due_month' => 9,
                'due_day' => 1,
            ]
        );

        // =========================================================
        // 2. التقسيط على دفعتين
        // =========================================================

        $policy2 = InstallmentPolicy::updateOrCreate(
            [
                'name' => 'تقسيط على دفعتين (نصفين)',
            ],
            [
                'installments_count' => 2,
            ]
        );

        InstallmentPolicyItem::updateOrCreate(
            [
                'installment_policy_id' => $policy2->id,
                'installment_number' => 1,
            ],
            [
                'title' => 'الدفعة الأولى (التأسيسية)',
                'percentage' => 50.00,
                'due_month' => 9,
                'due_day' => 15,
            ]
        );

        InstallmentPolicyItem::updateOrCreate(
            [
                'installment_policy_id' => $policy2->id,
                'installment_number' => 2,
            ],
            [
                'title' => 'الدفعة الثانية (الختامية)',
                'percentage' => 50.00,
                'due_month' => 1,
                'due_day' => 15,
            ]
        );

        // =========================================================
        // 3. التقسيط على ثلاث دفعات
        // =========================================================

        $policy3 = InstallmentPolicy::updateOrCreate(
            [
                'name' => 'تقسيط على ثلاث دفعات',
            ],
            [
                'installments_count' => 3,
            ]
        );

        InstallmentPolicyItem::updateOrCreate(
            [
                'installment_policy_id' => $policy3->id,
                'installment_number' => 1,
            ],
            [
                'title' => 'الدفعة الأولى',
                'percentage' => 40.00,
                'due_month' => 9,
                'due_day' => 15,
            ]
        );

        InstallmentPolicyItem::updateOrCreate(
            [
                'installment_policy_id' => $policy3->id,
                'installment_number' => 2,
            ],
            [
                'title' => 'الدفعة الثانية',
                'percentage' => 30.00,
                'due_month' => 12,
                'due_day' => 15,
            ]
        );

        InstallmentPolicyItem::updateOrCreate(
            [
                'installment_policy_id' => $policy3->id,
                'installment_number' => 3,
            ],
            [
                'title' => 'الدفعة الثالثة',
                'percentage' => 30.00,
                'due_month' => 3,
                'due_day' => 15,
            ]
        );
    }
}
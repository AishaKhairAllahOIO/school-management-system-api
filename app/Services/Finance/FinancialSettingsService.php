<?php

namespace App\Services\Finance;

use App\Models\InstallmentPolicy;
use App\Models\FeePlan;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;
use Exception;
use App\Models\FeePlanExtraService;
use App\Models\InstallmentPolicyItem;
use App\Models\FinancialAccount;

class FinancialSettingsService
{
    // =========================================================
    // 1. سياسات التقسيط (Installment Policies)
    // =========================================================

    public function getPolicies(): Collection
    {
        return InstallmentPolicy::with('items')->get();
    }

    // 👈 إضافة دالة جلب سياسة واحدة
    public function getPolicyById(int $id): InstallmentPolicy
    {
        return InstallmentPolicy::with('items')->findOrFail($id);
    }

    public function createPolicy(array $data): InstallmentPolicy
    {
        return DB::transaction(function () use ($data) {
            $policy = InstallmentPolicy::create([
                'name'               => $data['name'],
                'installments_count' => count($data['items']), 
            ]);

            $itemsData = [];
            foreach ($data['items'] as $index => $item) {
                $itemsData[] = [
                    'installment_number' => $index + 1,
                    'title'              => $item['title'],
                    'percentage'         => $item['percentage'],
                    'due_month'          => $item['dueMonth'],
                    'due_day'            => $item['dueDay'],
                ];
            }
            $policy->items()->createMany($itemsData);

            return $policy->load('items');
        });
    }

    public function updatePolicy(int $id, array $data): InstallmentPolicy
    {
        return DB::transaction(function () use ($id, $data) {
            $policy = InstallmentPolicy::findOrFail($id);

            $isUsed = \App\Models\FeePlan::where('installment_policy_id', $id)->exists() ||
                      \App\Models\FinancialAccount::where('installment_policy_id', $id)->exists();

            if ($isUsed) {
                throw new Exception('لا يمكن تعديل سياسة التقسيط لأنها مستخدمة بالفعل في خطط مالية أو حسابات طلاب.', 409);
            }

            $policy->update([
                'name'               => $data['name'],
                'installments_count' => count($data['items']),
            ]);

            $policy->items()->delete(); 
            
            $itemsData = [];
            foreach ($data['items'] as $index => $item) {
                $itemsData[] = [
                    'installment_number' => $index + 1,
                    'title'              => $item['title'],
                    'percentage'         => $item['percentage'],
                    'due_month'          => $item['dueMonth'],
                    'due_day'            => $item['dueDay'],
                ];
            }
            $policy->items()->createMany($itemsData);

            return $policy->fresh('items');
        });
    }

    public function deletePolicy(int $id): void
    {
        $policy = InstallmentPolicy::findOrFail($id);

        $isUsed = \App\Models\FeePlan::where('installment_policy_id', $id)->exists() ||
                  \App\Models\FinancialAccount::where('installment_policy_id', $id)->exists();

        if ($isUsed) {
            throw new Exception('لا يمكن حذف سياسة التقسيط لارتباطها بخطط مالية أو حسابات طلاب.', 409);
        }

        $policy->delete();
    }

    public function updatePolicyItem(int $id, array $data): \App\Models\InstallmentPolicyItem
    {
        $item = \App\Models\InstallmentPolicyItem::findOrFail($id);
        
        $item->update([
            'title'     => $data['title'] ?? $item->title,
            'due_month' => $data['dueMonth'] ?? $item->due_month,
            'due_day'   => $data['dueDay'] ?? $item->due_day,
        ]);

        return $item->fresh();
    }

    public function deletePolicyItem(int $id): void
    {
        throw new Exception('لا يمكن حذف دفعة واحدة بشكل مستقل لأن ذلك سيكسر مجموع الـ 100%. لحذف دفعة، يرجى تعديل سياسة التقسيط بالكامل من الواجهة الرئيسية.', 422);
    }

    // =========================================================
    // 2. خطط الرسوم الموحدة (Fee Plans)
    // =========================================================

    public function getFeePlans(): Collection
    {
        return FeePlan::with(['academicYear', 'gradeLevel', 'extraServices'])->get();
    }

    // 👈 إضافة دالة جلب خطة مالية واحدة
    public function getFeePlanById(int $id): FeePlan
    {
        return FeePlan::with(['academicYear', 'gradeLevel', 'extraServices'])->findOrFail($id);
    }

    public function createFeePlan(array $data): FeePlan
    {
        return DB::transaction(function () use ($data) {
            $feePlan = FeePlan::create([
                'academic_year_id'      => $data['academicYearId'],
                'grade_level_id'        => $data['gradeLevelId'],
                'name'                  => $data['name'],
                'base_amount'           => $data['baseAmount'],
            ]);

            if (!empty($data['extraServices'])) {
                $feePlan->extraServices()->createMany($data['extraServices']);
            }

            return $feePlan->load(['academicYear', 'gradeLevel', 'extraServices']);
        });
    }

    public function updateFeePlan(int $id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $feePlan = FeePlan::findOrFail($id);

            $isUsed = FinancialAccount::where('fee_plan_id', $id)->exists();
            if ($isUsed) {
                 throw new Exception('لا يمكن تعديل الخطة المالية لأن هناك طلاباً متعاقدين عليها بالفعل.', 409);
            }

            $feePlan->update([
                'academic_year_id'      => $data['academicYearId'],
                'grade_level_id'        => $data['gradeLevelId'],
                'name'                  => $data['name'],
                'base_amount'           => $data['baseAmount'],
            ]);

            $feePlan->extraServices()->delete();
            if (!empty($data['extraServices'])) {
                $feePlan->extraServices()->createMany($data['extraServices']);
            }

            return $feePlan->fresh(['academicYear', 'gradeLevel', 'extraServices']);
        });
    }

    public function deleteFeePlan(int $id): void
    {
        $feePlan = FeePlan::findOrFail($id);
        
        $isUsed = FinancialAccount::where('fee_plan_id', $id)->exists();
        if ($isUsed) {
            throw new Exception('لا يمكن حذف الخطة المالية لارتباطها بحسابات مالية لطلاب حاليين.', 409);
        }

        $feePlan->delete();
    }

    public function updateExtraService(int $id, array $data)
    {
        $service = FeePlanExtraService::findOrFail($id);
        
        $isUsed = FinancialAccount::where('fee_plan_id', $service->fee_plan_id)->exists();
        if ($isUsed) {
            throw new Exception('لا يمكن تعديل الخدمة الإضافية لأن هناك طلاباً متعاقدين على هذه الخطة المالية بالفعل.', 409);
        }

        $service->update([
            'type'   => $data['type'] ?? $service->type,
            'name'   => $data['name'] ?? $service->name,
            'amount' => $data['amount'] ?? $service->amount,
        ]);

        return $service->fresh();
    }

    public function deleteExtraService(int $id): void
    {
        $service = FeePlanExtraService::findOrFail($id);

        $isUsed = FinancialAccount::where('fee_plan_id', $service->fee_plan_id)->exists();
        if ($isUsed) {
            throw new Exception('لا يمكن حذف الخدمة الإضافية لأن هناك طلاباً متعاقدين على هذه الخطة المالية بالفعل.', 409);
        }

        $service->delete();
    }
    // اريد انشاء دالة لعرض ExtraService محددة
    public function getExtraService(int $id)
    {
        return FeePlanExtraService::findOrFail($id);
    }
    //اريد دالة لعرض item محددد من الinsallmentpolicy
    public function getInstallmentPolicyItem(int $id)
    {
        return InstallmentPolicyItem::findOrFail($id);
    }

}
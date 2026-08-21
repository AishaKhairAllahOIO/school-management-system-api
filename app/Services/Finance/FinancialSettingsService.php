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

    public function getPolicies(): Collection
    {
        return InstallmentPolicy::with('items')->get();
    }
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

            $isUsed = 
                      \App\Models\FinancialAccount::where('installment_policy_id', $id)->exists();

            if ($isUsed) {
throw new Exception(
    'The installment policy cannot be modified because it is already assigned to financial plans or student accounts.',
    409
);            }

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

        $isUsed = 
                  \App\Models\FinancialAccount::where('installment_policy_id', $id)->exists();

        if ($isUsed) {
throw new Exception(
    'The installment policy cannot be deleted because it is associated with financial plans or student accounts.',
    409
);        }

        $policy->delete();
    }
    public function updatePolicyItem(int $id, array $data): InstallmentPolicyItem
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
throw new Exception(
    'An installment cannot be deleted individually because the policy must total 100%. To remove an installment, please edit the entire installment policy.',
    422
);    }
    public function getFeePlans(): Collection
    {
        return FeePlan::with(['academicYear', 'gradeLevel', 'extraServices'])->get();
    }
    public function getFeePlanById(int $id): FeePlan
    {
        return FeePlan::with(['academicYear', 'gradeLevel', 'extraServices'])->findOrFail($id);
    }
    public function createFeePlan(array $data): FeePlan
    {
        $exists = FeePlan::where('academic_year_id', $data['academicYearId'])
            ->where('grade_level_id', $data['gradeLevelId'])
            ->exists();

        if ($exists) {
            throw new Exception('A fee plan already exists for this grade level in the selected academic year.', 422);
        }
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
        $feePlan = FeePlan::findOrFail($id);

        $isUsed = FinancialAccount::where('fee_plan_id', $id)->exists();
        if ($isUsed) {
            throw new Exception(
                'The financial plan cannot be modified because it is already assigned to students.',
                409
            );            
        }

        // 💡 درع الحماية: التأكد من عدم التصادم مع خطة أخرى موجودة
        $exists = FeePlan::where('academic_year_id', $data['academicYearId'])
            ->where('grade_level_id', $data['gradeLevelId'])
            ->where('id', '!=', $id) // استثناء الخطة التي نقوم بتعديلها حالياً
            ->exists();

        if ($exists) {
            throw new Exception('A fee plan already exists for this grade level in the selected academic year.', 422);
        }

        return DB::transaction(function () use ($feePlan, $data) {
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
throw new Exception(
    'The financial plan cannot be deleted because it is associated with active student financial accounts.',
    409
);        }

        $feePlan->delete();
    }
    public function updateExtraService(int $id, array $data)
    {
        $service = FeePlanExtraService::findOrFail($id);
        
        $isUsed = FinancialAccount::where('fee_plan_id', $service->fee_plan_id)->exists();
        if ($isUsed) {
throw new Exception(
    'The additional service cannot be modified because students are already assigned to this financial plan.',
    409
);        }

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
throw new Exception(
    'The additional service cannot be deleted because students are already assigned to this financial plan.',
    409
);        }

        $service->delete();
    }
    public function getExtraService(int $id)
    {
        return FeePlanExtraService::findOrFail($id);
    }
    public function getInstallmentPolicyItem(int $id)
    {
        return InstallmentPolicyItem::findOrFail($id);
    }

}
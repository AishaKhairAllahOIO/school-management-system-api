<?php

namespace App\Services\Staff;

use App\Models\StaffFinancialContract;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;
use Exception;

class StaffFinancialContractService
{
    /**
     * جلب عقود الموظفين مع الفلترة أو العرض العام
     */
    public function getAllContracts(array $filters = []): Collection
    {
        $query = StaffFinancialContract::with(['staff.user', 'academicYear']);

        if (!empty($filters['academic_year_id'])) {
            $query->where('academic_year_id', $filters['academic_year_id']);
        }

        if (!empty($filters['staff_id'])) {
            $query->where('staff_id', $filters['staff_id']);
        }

        return $query->latest()->get();
    }

    /**
     * إنشاء عقد مالي جديد لموظف/معلم
     */
    public function createContract(array $data): StaffFinancialContract
    {
        return DB::transaction(function () use ($data) {
            // التحقق المسبق من عدم وجود عقد مكرر لنفس الموظف في نفس السنة
            $exists = StaffFinancialContract::where('staff_id', $data['staff_id'])
                ->where('academic_year_id', $data['academic_year_id'])
                ->exists();

            if ($exists) {
                throw new Exception('يوجد عقد مالي مسجل مسبقاً لهذا الموظف في هذه السنة الدراسية.', 422);
            }

            return StaffFinancialContract::create($data);
        });
    }

    /**
     * جلب عقد محدد بالمعرف
     */
    public function getContractById(int $id): StaffFinancialContract
    {
        return StaffFinancialContract::with(['staff.user', 'academicYear'])->findOrFail($id);
    }

    /**
     * تعديل عقد مالي
     */
    public function updateContract(int $id, array $data): StaffFinancialContract
    {
        return DB::transaction(function () use ($id, $data) {
            $contract = StaffFinancialContract::findOrFail($id);

            // إذا تم تغيير الموظف أو السنة، نتحقق من عدم حدوث تداخل
            if (
                (isset($data['staff_id']) && $data['staff_id'] !== $contract->staff_id) ||
                (isset($data['academic_year_id']) && $data['academic_year_id'] !== $contract->academic_year_id)
            ) {
                $staffId = $data['staff_id'] ?? $contract->staff_id;
                $yearId = $data['academic_year_id'] ?? $contract->academic_year_id;

                $exists = StaffFinancialContract::where('staff_id', $staffId)
                    ->where('academic_year_id', $yearId)
                    ->where('id', '!=', $id)
                    ->exists();

                if ($exists) {
                    throw new Exception('لا يمكن التعديل: يوجد عقد آخر مسجل لهذا الموظف في نفس السنة.', 422);
                }
            }

            $contract->update($data);

            return $contract->refresh()->load(['staff.user', 'academicYear']);
        });
    }

    /**
     * حذف عقد مالي
     */
    public function deleteContract(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $contract = StaffFinancialContract::findOrFail($id);
            
            // تحقق إن كان العقد مرتبطاً برواتب مسجلة مسبقاً لمنع كسر البيانات
            if ($contract->payrolls()->exists()) {
                throw new Exception('لا يمكن حذف هذا العقد لأنه مرتبطة بسجلات رواتب سابقة مسجلة في النظام.', 422);
            }

            $contract->delete();
            return true;
        });
    }
}
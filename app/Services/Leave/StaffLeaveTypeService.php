<?php
namespace App\Services\Leave;
use App\Models\StaffLeaveType;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Exception;

class StaffLeaveTypeService
{
    public function getAllLeaveTypes(): Collection
    {
        return StaffLeaveType::latest()->get();
    }
    public function getLeaveById(int $id)
    {
        return StaffLeaveType::findOrFail($id);
    }

    public function createLeaveType(array $data): StaffLeaveType
    {
        return StaffLeaveType::create($data);
    }

    public function updateLeaveType(int $id, array $data)
    {
        $leaveType = StaffLeaveType::findOrFail($id);
        if(DB::table('staff_leaves')->where('leave_type_id', $id)->exists())
            throw new Exception('لا يمكن تعديل نوع الإجازة هذا لأنه مرتبط بسجلات إجازات سابقة للموظفين.');
        $leaveType->update($data);
        return $leaveType;
    }

    public function deleteLeaveType(int $id): void
    {
        $leaveType = StaffLeaveType::findOrFail($id);

        if (DB::table('staff_leaves')->where('leave_type_id', $id)->exists()) {
            throw new Exception('لا يمكن حذف نوع الإجازة هذا لأنه مرتبط بسجلات إجازات سابقة للموظفين.');
        }

        $leaveType->delete();
    }
}
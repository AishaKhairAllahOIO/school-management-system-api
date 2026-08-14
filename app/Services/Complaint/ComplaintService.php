<?php

namespace App\Services\Complaint;

use App\Models\Complaint;
use App\Models\ComplaintCategory;
use App\Models\AcademicSetting;
use Illuminate\Database\Eloquent\Collection;

class ComplaintService
{

    public function getComplaintOptions(): Collection
    {
        return ComplaintCategory::where('is_active', true)
            ->with(['types' => function ($query) {
                $query->where('is_active', true)
                      ->select('id', 'complaint_category_id', 'title', 'severity');
            }])
            ->select('id', 'name')
            ->get();
    }

    /**
     * حفظ الشكوى الجديدة المُقدمة من ولي الأمر
     */
    public function submitComplaint(array $data, int $guardianId): Complaint
    {
        // جلب إعدادات السنة الدراسية الحالية لربط الشكوى بها
        $setting = AcademicSetting::firstOrFail();

        $complaint = Complaint::create([
            'guardian_id'       => $guardianId,
            'student_id'        => $data['student_id'],
            'complaint_type_id' => $data['complaint_type_id'],
            'academic_year_id'  => $setting->current_academic_year_id,
            'semester_id'       => $setting->current_semester_id,
            'status'            => 'pending', // الحالة الافتراضية
        ]);

        return $complaint->load('type.category', 'student.user');
    }

    /**
     * جلب سجل الشكاوى السابقة التي قدمها ولي الأمر
     */
    public function getGuardianComplaints(int $guardianId): Collection
    {
        // جلب الإعدادات لعرض شكاوى السنة الحالية فقط (أو يمكنك إزالة الشرط لعرض كل السجل التاريخي)
        $setting = AcademicSetting::firstOrFail();

        return Complaint::with(['type.category', 'student.user'])
            ->where('guardian_id', $guardianId)
            ->where('academic_year_id', $setting->current_academic_year_id)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}

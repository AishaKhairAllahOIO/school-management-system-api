<?php

namespace App\Http\Resources\Report;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StaffAttendanceManagementReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'total_staff'                => $this['total_staff'],
            'overall_attendance_rate'    => $this['overall_attendance_rate'] . '%',
            'overall_absence_rate'       => $this['overall_absence_rate'] . '%',
            'total_unexcused_days'       => $this['total_unexcused_days'],
            'total_excused_days'         => $this['total_excused_days'],
            'total_leave_days'           => $this['total_leave_days'],
            
            // تم تعديل المسمى ليكون أكثر وضوحاً
            'total_missed_periods_count' => $this['total_missed_periods_count'], 
            
            // إضافة المصفوفة الجديدة التي تفصل الحصص الفائتة حسب المادة
            'missed_periods_by_subject'  => $this['missed_periods_by_subject'], 
        ];
    }
}
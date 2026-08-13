<?php

namespace App\Http\Resources\Staff;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StaffAttendanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'staff_id'        => $this->staff_id,
            'attendance_date' => $this->attendance_date->format('Y-m-d'),
            'status'          => $this->status,
            'absence_type'    => $this->absence_type,
            // تفاصيل الحصص التي غاب عنها إن كان غيابه جزئياً
            'missing_periods' => $this->whenLoaded('periodAttendances', function() {
                return $this->periodAttendances->map(function($p) {
                    return [
                        'schedule_entry_id' => $p->schedule_entry_id,
                        // عرض رقم الحصة (مثلاً: 1 يعني الحصة الأولى)
                        'period_index'      => $p->scheduleEntry?->period_index,
                        // عرض اليوم
                        'day'               => $p->scheduleEntry?->day,
                        // اسم الصف (استبدلي name بـ اسم العمود الفعلي لديك في جدول class_rooms)
                        'class_room'        => $p->scheduleEntry?->classRoom?->name, 
                        // المادة (افترضت وجود علاقة لجلب اسم المادة، عدليها حسب هيكل GradeSubject لديك)
                        'grade_subject_id'  => $p->scheduleEntry?->grade_subject_id,
                    ];
                });
            }),
        ];
    }
}

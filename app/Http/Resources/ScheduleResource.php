<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\ScheduleValidator; // استيراد المُدقق

class ScheduleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // 1. تجميع الحصص حسب الشعبة (كما فعلنا سابقاً)
        $formattedClasses = $this->entries
            ->groupBy(fn($entry) => $entry->classRoom->name)
            ->map(function ($classEntries, $className) {
                $days = $classEntries
                    ->groupBy('day')
                    ->map(function ($dayEntries) {
                        return $dayEntries->sortBy('period_index')->values()->map(function ($entry) {
                            return [
                                'period' => $entry->period_index,
                                'subject' => $entry->gradeSubject->subject->name ?? 'N/A',
                                'teacher' => $entry->teacher->name ?? 'N/A',
                                'is_heavy' => $entry->gradeSubject->difficulty === 'heavy',
                            ];
                        });
                    });

                return [
                    'class_name' => $className,
                    'schedule' => $days,
                ];
            })->values();

        // 2. تشغيل المُدقق (Validator) على هذا الجدول
        $validator = app(ScheduleValidator::class);
        $validationReport = $validator->validate($this->resource);

        // 3. إعادة البيانات مع تقرير الجودة
        return [
            'id' => $this->id,
            'academic_year_id' => $this->academic_year_id,
            'semester_id' => $this->semester_id,

            // حالة الجدول والتجاوزات
            'is_perfect' => $validationReport['valid'],
            'quality_report' => [
                'statistics' => $validationReport['statistics'],
                'violations' => $validationReport['errors'] // هنا ستظهر التجاوزات
            ],

            'classes' => $formattedClasses,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}

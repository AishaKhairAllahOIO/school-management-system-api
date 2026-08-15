<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportCardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
       return [
            'report_card_id' => $this->id,
            'student_id'     => $this->enrollment->student_id,
            'student_name'   => $this->enrollment->student->user->first_name . ' ' . $this->enrollment->student->user->last_name, // حسب علاقاتك
            
            // الخلاصة الكلية
            'summary' => [
                'total_marks'       => $this->total_marks,
                'max_total_marks'   => $this->max_total_marks,
                'attendance_status' => $this->attendance_status,
                'final_result'      => $this->final_result,
                'failure_reasons'   => $this->failure_reasons ?? [],
            ],

            // 🎯 تفاصيل المواد (كل مادة بعلاماتها و تقييماتها)
            'subjects' => $this->details->map(function ($detail) {
                return [
                    'subject_name'       => $detail->gradeSubject->subject->subject_name ?? 'مادة',
                    'is_failing_subject' => $detail->is_failing_subject,
                    'subject_total'      => $detail->subject_total,
                    'max_mark'           => $detail->max_mark,
                    'passing_mark'       => $detail->passing_mark,
                    'status'             => $detail->status,
                    
                    // السحر: هذا الحقل سيقرأ مصفوفة JSON التي خزنّا فيها (الشفهي، المذاكرة، الخ)
                    'evaluations'        => is_string($detail->evaluations_summary) 
                                            ? json_decode($detail->evaluations_summary, true) 
                                            : $detail->evaluations_summary,
                ];
            }),
        ];
    }
}

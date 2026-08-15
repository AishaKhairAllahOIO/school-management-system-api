<?php

namespace App\Http\Resources\Report;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentAttendanceManagementReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'total_students'           => $this['total_students'],
            'overall_attendance_rate'  => $this['overall_attendance_rate'] . '%',
            'overall_absence_rate'     => $this['overall_absence_rate'] . '%',
            'total_unexcused_absences' => $this['total_unexcused_absences'],
            'total_excused_absences'   => $this['total_excused_absences'],
            'classrooms_summary'       => collect($this['classrooms_summary'])->map(function ($item) {
                return [
                    'class_room_id'      => $item['class_room_id'],
                    'class_room_name'    => $item['class_room_name'],
                    'student_count'      => $item['student_count'],
                    'attendance_rate'    => $item['attendance_rate'] . '%',
                    'absence_rate'       => $item['absence_rate'] . '%',
                    'unexcused_absences' => $item['unexcused_absences'],
                ];
            }),
        ];
    }
}

<?php

namespace App\Http\Resources\Counselor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CounselorStudentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $enrollment = $this->enrollments->first();

        return [
            'id' => $this->id,

            'student' => [
                'id' => $this->user->student->id,
                'name' => $this->user->first_name . ' ' . $this->user->last_name,
                'father_name' => $this->user->father_name,
                'gender' => $this->user->gender,
                'birth_date' => $this->user->birth_date,
                'photoUrl' => $this->user->photo_url
                    ? url('/api/documents/photos/' . ltrim(preg_replace('/^.*?(users\/|defaults\/)/', '$1', $this->user->photo_url), '/'))
                    : null,
            ],


            'academic_info' => [
                'grade' => $enrollment?->classRoom?->gradeLevel?->name,
                'class_room' => $enrollment?->classRoom?->name,
            ],


            'sessions_count' => $this->counselor_appointments_count ?? 0,


            'appointments' => $this->counselorAppointments->map(function ($appointment) {

                return [
                    'id' => $appointment->id,

                    'date' => $appointment->appointment_date,

                    'time' => [
                        'start' => $appointment->start_time,
                        'end' => $appointment->end_time,
                    ],

                    'status' => $appointment->booking_status,


                    'session' => $appointment->counselingSession ? [
                        'attendance_status' => $appointment->counselingSession->attendance_status,
                        'assessment' => $appointment->counselingSession->assessment,
                        'notes' => $appointment->counselingSession->notes,
                    ] : null
                ];
            }),

            'created_at' => $this->created_at,
        ];
    }
}
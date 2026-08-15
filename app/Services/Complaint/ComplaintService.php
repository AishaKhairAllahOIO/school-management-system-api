<?php

namespace App\Services\Complaint;

use App\Models\Complaint;
use App\Models\ComplaintCategory;
use App\Models\AcademicSetting;
use App\Models\Alert;
use App\Models\Guardian;
use App\Models\Staff;
use App\Models\Student;
use App\Services\User\AlertService;
use Exception;
use Illuminate\Database\Eloquent\Collection;

class ComplaintService
{

    public function __construct(private AlertService $alertService)
    {
    }

    public function getComplaintOptions(): Collection
    {
        return ComplaintCategory::where('is_active', true)
            ->with([
                'types' => function ($query) {
                    $query->where('is_active', true)
                        ->select('id', 'complaint_category_id', 'title', 'severity');
                }
            ])
            ->select('id', 'name')
            ->get();
    }


    public function submitComplaint(array $data, int $guardianId): Complaint
    {
        $setting = AcademicSetting::firstOrFail();

        $guardian = Guardian::findOrFail($guardianId);
        $student = Student::findOrFail($data['student_id']);

         $exists = Complaint::query()
        ->where('guardian_id', $guardianId)
        ->where('student_id', $data['student_id'])
        ->where('complaint_type_id', $data['complaint_type_id'])
        ->whereDate('created_at', now()->toDateString())
        ->exists();

    if ($exists) {
        throw new Exception(
            'You have already submitted this complaint for this student today.'
        );
    }

        $guardianName = $guardian->user->first_name . ' ' . $guardian->user->last_name;
        $studentName = $student->user->first_name . ' ' . $student->user->last_name;

        $complaint = Complaint::create([
            'guardian_id' => $guardianId,
            'student_id' => $data['student_id'],
            'complaint_type_id' => $data['complaint_type_id'],
            'academic_year_id' => $setting->current_academic_year_id,
            'semester_id' => $setting->current_semester_id,
        ]);

        $complaint->load('type.category', 'student.user');

        $adminStaffIds = Staff::whereHas('user', function ($query) {
            $query->role(['super_admin', 'adviser', 'secretary']);
        })->pluck('id')->toArray();

        if (!empty($adminStaffIds)) {
            $this->alertService->createBatchStaffAlerts(
                $adminStaffIds,
                Alert::TYPE_COMPLAIN,
                [
                    'severity' => $complaint->type->severity ?? 'medium',
                ],
                "شكوى جديدة من ولي الأمر: {$guardianName}",
                "تتعلق بالطالب {$studentName} حول: {$complaint->type->title}"
            );
        }

        return $complaint;
    }

    public function getGuardianComplaints(int $guardianId, int $studentId)
    {
        $student = Student::query()
            ->where('id', $studentId)
            ->where('guardian_id', $guardianId)
            ->first();

        if (!$student) {
            throw new Exception('Student does not belong to this guardian.', 403);
        }

        return Complaint::query()
            ->where('guardian_id', $guardianId)
            ->where('student_id', $studentId)
            ->with([
                'type.category',
                'student.user',
            ])
            ->get();
    }
    public function updateComplaint(int $complaintId, int $guardianId, array $data): Complaint
    {
        $complaint = Complaint::where('id', $complaintId)->where('guardian_id', $guardianId)->firstOrFail();


        $complaint->update([
            'student_id' => $data['student_id'] ?? $complaint->student_id,
            'complaint_type_id' => $data['complaint_type_id'] ?? $complaint->complaint_type_id,
        ]);

        return $complaint->load('type.category', 'student.user');
    }

    public function deleteComplaint(int $complaintId, int $guardianId): void
    {
        $complaint = Complaint::where('id', $complaintId)->where('guardian_id', $guardianId)->firstOrFail();

        $complaint->delete();
    }
}

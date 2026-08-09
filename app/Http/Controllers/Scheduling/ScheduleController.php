<?php

namespace App\Http\Controllers\Scheduling;

use App\Http\Controllers\Controller;
use App\Http\Requests\Scheduale\UpdateScheduleEntryRequest;
use App\Http\Resources\Scheduale\AdminScheduleResource;
use App\Services\Schedule\ScheduleService;
use App\Services\User\AlertService;
use App\Jobs\GenerateScheduleJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\ApiResource;
use App\Models\Student;
use Exception;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ScheduleController extends Controller
{
    use ApiResource;

    public function __construct(
        private ScheduleService $scheduleService,
        private AlertService $alertService
    ) {}


    private function authorizeStudentAccess($user, int $studentId): void
    {
        if ($user->hasRole('student') && $user->student) {
            if ($user->student->id !== $studentId) {
                throw new AccessDeniedHttpException('Access denied you can not view others schedule', null, 403);
            }
        }

        if ($user->hasRole('guardian') && $user->guardian) {
            $isMyChild = $user->guardian->students()->where('students.id', $studentId)->exists();
            
            if (!$isMyChild) {
                throw new AccessDeniedHttpException('Access denied this child is not one of your children ', null, 403);
            }
        }
        
    }
    public function generate(Request $request): JsonResponse
    {
        $request->validate([
            'academic_year_id' => 'required|integer',
            'semester_id'      => 'required|integer',
        ]);

        $staff = Auth::user()->staff;

        try {
            $this->scheduleService->checkBeforeGeneration(
                $request->academic_year_id,
                $request->semester_id
            );

            GenerateScheduleJob::dispatch($request->academic_year_id, $request->semester_id, $staff->id);

            $this->alertService->createSystemNotice(
                $staff,
                'Generation Started ⚙️',
                'The system is now generating the schedule in the background. You will be notified when it is ready.'
            );

            return $this->successResponse(
                null,
                'Schedule generation queued successfully. You will be notified upon completion.',
                201
            );

        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    public function regenerate(Request $request): JsonResponse
    {
        $request->validate([
            'academic_year_id' => 'required|integer',
            'academic_term_id'      => 'required|integer',
        ]);

        $staff = Auth::user()->staff;

        try {
            $this->scheduleService->deleteExistingSchedule(
                $request->academic_year_id,
                $request->academic_term_id
            );

            GenerateScheduleJob::dispatch($request->academic_year_id, $request->academic_term_id, $staff->id);

            $this->alertService->createSystemNotice(
                $staff,
                'Regeneration Started ♻️',
                'The old schedule was deleted and a new one is being generated. Please wait.'
            );

            return $this->successResponse(
                null,
                'Old schedule deleted. New schedule generation queued successfully.',
                201
            );

        } catch (Exception $e) {
            return $this->errorResponse('Failed to regenerate schedule: ' . $e->getMessage(), 500);
        }
    }

    public function updateEntry(UpdateScheduleEntryRequest $request, int $entryId): JsonResponse
    {
        $entry = $this->scheduleService->updateEntry($entryId, $request->validated());

        return $this->successResponse(
            $entry,
            'Entry updated successfully (Manual Override)',
            200
        );
    }

    public function adminView(int $scheduleId): JsonResponse
    {
        $data = $this->scheduleService->getAdminSchedule($scheduleId);

        return $this->successResponse(
            new AdminScheduleResource((object) $data),
            'Admin schedule retrieved successfully.',
            200
        );
    }

    public function studentWeekly(int $studentId): JsonResponse
    {
        $user = Auth::user();
        
        $this->authorizeStudentAccess($user, $studentId);

        $student = Student::findOrFail($studentId);

        $enrollment = $student->enrollments()->latest()->first();

        if (!$enrollment || !$enrollment->class_room_id) {
             return $this->errorResponse('This student does not enrolled yet', 404);
        }

        $schedule = $this->scheduleService->getStudentWeeklySchedule($enrollment->class_room_id);
        
        return $this->successResponse(
            $schedule, 
            'Student weekly schedule retrieved successfully.',
            200
        );
    }
    public function studentTomorrow(int $studentId): JsonResponse
    {
        $user = Auth::user();
        
        $this->authorizeStudentAccess($user, $studentId);

        $student = Student::findOrFail($studentId);

        $enrollment = $student->enrollments()->latest()->first();

        if (!$enrollment || !$enrollment->class_room_id) {
             return $this->errorResponse('This child does not enrolled yet.', 404);
        }

        $schedule = $this->scheduleService->getStudentTomorrowSchedule($enrollment->class_room_id);
        
        return $this->successResponse(
            $schedule, 
             'Student tomorrow schedule retrieved successfully.',
             200
        );
    }
    public function teacherWeekly(int $teacherId): JsonResponse
    {
        $schedule = $this->scheduleService->getTeacherWeeklySchedule($teacherId);

        return $this->successResponse(
            $schedule,
            'Teacher weekly schedule retrieved successfully.',200
        );
    }

    public function teacherTomorrow(int $teacherId): JsonResponse
    {
        $schedule = $this->scheduleService->getTeacherTomorrowSchedule($teacherId);

        return $this->successResponse(
            $schedule,
            'Teacher tomorrow schedule retrieved successfully.',
            200
        );
    }

    public function allTeachersWeekly(int $scheduleId): JsonResponse
    {
        $schedules = $this->scheduleService->getAllTeachersSchedule($scheduleId);

        return $this->successResponse(
            $schedules,
            'All teachers schedules retrieved successfully.'
        );
    }
}

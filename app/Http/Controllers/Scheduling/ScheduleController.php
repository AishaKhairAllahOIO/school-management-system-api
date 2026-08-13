<?php

namespace App\Http\Controllers\Scheduling;

use App\Http\Controllers\Controller;
use App\Http\Requests\Scheduale\UpdateScheduleEntryRequest;
use App\Http\Resources\Scheduale\AdminScheduleResource;
use App\Services\Schedule\ScheduleService;
use App\Services\User\AlertService;
use App\Jobs\GenerateScheduleJob;
use App\Models\Enrollment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\ApiResource;
use App\Http\Requests\Scheduale\StoreScheduleEntryRequest;
use App\Models\ScheduleEntry;
use InvalidArgumentException;
use Exception;

class ScheduleController extends Controller
{
    use ApiResource;

    public function __construct(
        private ScheduleService $scheduleService,
        private AlertService $alertService
    ) {
    }


    public function generate(Request $request): JsonResponse
    {
        $request->validate([
            'academic_year_id' => 'required|integer',
            'semester_id' => 'required|integer',
        ]);

        $staff = $this->getAuthStaff($request);

        try {
            $this->scheduleService->checkBeforeGeneration($request->academic_year_id, $request->semester_id);
            GenerateScheduleJob::dispatch($request->academic_year_id, $request->semester_id, $staff->id);

            $this->alertService->createSystemNotice(
                $staff,
                'Generation Started ⚙️',
                'The system is now generating the schedule in the background. You will be notified when it is ready.'
            );

            return $this->successResponse(null, 'Schedule generation queued successfully.', 202);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    public function regenerate(Request $request): JsonResponse
    {
        $request->validate([
            'academic_year_id' => 'required|integer',
            'semester_id' => 'required|integer',
        ]);

        $staff = $this->getAuthStaff($request);

        try {
            $this->scheduleService->deleteExistingSchedule($request->academic_year_id, $request->semester_id);
            GenerateScheduleJob::dispatch($request->academic_year_id, $request->semester_id, $staff->id);

            $this->alertService->createSystemNotice(
                $staff,
                'Regeneration Started ♻️',
                'The old schedule was deleted and a new one is being generated. Please wait.'
            );

            return $this->successResponse(null, 'Old schedule deleted. New schedule generation queued successfully.', 202);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to regenerate schedule: ' . $e->getMessage(), 500);
        }
    }

    public function addEntry(StoreScheduleEntryRequest $request): JsonResponse
    {
        $entry = $this->scheduleService->addEntry($request->validated());

        return $this->successResponse(
            $entry,
            'New entry added successfully (Manual Override)',
            201
        );
    }

    public function updateEntry(UpdateScheduleEntryRequest $request, int $entryId): JsonResponse
    {
        $entry = ScheduleEntry::find($entryId);

        if (!$entry) {
            return $this->errorResponse('The selected session dose not found.', 404);
        }

        $entry = $this->scheduleService->updateEntry($entryId, $request->validated());

        return $this->successResponse(
            $entry,
            'Entry updated successfully (Manual Override)'
        );
    }

    public function adminView(Request $request): JsonResponse
    {
        $request->validate([
            'academic_year_id' => 'required|integer',
            'semester_id' => 'required|integer',
        ]);

        try {
            $data = $this->scheduleService->getAdminSchedule(
                $request->academic_year_id,
                $request->semester_id
            );

            return $this->successResponse(new AdminScheduleResource((object) $data), 'Admin schedule retrieved successfully.');
        } catch (Exception $e) {
            return $this->errorResponse('Schedule not found for the selected term.', 404);
        }
    }


    public function studentWeekly(Request $request): JsonResponse
    {
        try {
            $student = $this->getResolvedStudent($request);
            $enrollment = $this->getCurrentEnrollment($student);

            $schedule = $this->scheduleService->getStudentWeeklySchedule($enrollment->class_room_id);
            return $this->successResponse($schedule, 'Student weekly schedule retrieved successfully.');
        } catch (Exception $e) {
            $code = $e->getCode() ?: 400;
            return $this->errorResponse($e->getMessage(), $code);
        }
    }

    public function studentTomorrow(Request $request): JsonResponse
    {
        try {
            $student = $this->getResolvedStudent($request);
            $enrollment = $this->getCurrentEnrollment($student);

            $schedule = $this->scheduleService->getStudentTomorrowSchedule($enrollment->class_room_id);
            return $this->successResponse($schedule, 'Student tomorrow schedule retrieved successfully.');
        } catch (Exception $e) {
            $code = $e->getCode() ?: 400;
            return $this->errorResponse($e->getMessage(), $code);
        }
    }


    public function teacherWeekly(Request $request): JsonResponse
    {
        try {
            $teacher = $this->getAuthTeacher($request);

            $schedule = $this->scheduleService->getTeacherWeeklySchedule($teacher->id);
            return $this->successResponse($schedule, 'Teacher weekly schedule retrieved successfully.');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 403);
        }
    }

    public function teacherTomorrow(Request $request): JsonResponse
    {
        try {
            $teacher = $this->getAuthTeacher($request);

            $schedule = $this->scheduleService->getTeacherTomorrowSchedule($teacher->id);
            return $this->successResponse($schedule, 'Teacher tomorrow schedule retrieved successfully.');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 403);
        }
    }


public function allTeachersWeekly(Request $request): JsonResponse
    {
        try {
            $this->getAuthStaff($request);

            if (!$request->user()->hasAnyRole(['super_admin', 'adviser'])) {
                throw new InvalidArgumentException('Unauthorized access. Only admins and advisers can view the master timetable.', 403);
            }

            // إضافة التحقق من السنة والفصل
            $request->validate([
                'academic_year_id' => 'required|integer',
                'semester_id'      => 'required|integer',
            ]);

            // استدعاء الخدمة باستخدام السنة والفصل
            $schedules = $this->scheduleService->getAllTeachersSchedule(
                $request->academic_year_id, 
                $request->semester_id
            );

            return $this->successResponse(
                $schedules,
                'All teachers schedules retrieved successfully.'
            );
        } catch (Exception $e) {
            $code = $e->getCode();
            $code = ($code >= 400 && $code < 600) ? $code : 500; 

            return $this->errorResponse($e->getMessage(), $code);
        }
    }


    private function getResolvedStudent(Request $request)
    {
        $user = $request->user();

        // 1. إذا كان المستخدم طالباً
        if ($user->hasRole('student')) {
            $student = $user->student;
            if (!$student)
                throw new InvalidArgumentException('This account does not belong to a student.', 403);
            return $student;
        }

        if ($user->hasRole('guardian')) {
            $guardian = $user->guardian;
            if (!$guardian)
                throw new InvalidArgumentException('This account does not belong to a guardian.', 403);

            // يجب أن يرسل الـ ID في الـ Query Parameter (مثال: ?student_id=5)
            $studentId = $request->query('student_id');
            if (!$studentId)
                throw new InvalidArgumentException('Please provide a student_id in the request query parameters.', 422);

            $student = $guardian->students()->find($studentId);
            if (!$student)
                throw new InvalidArgumentException('This student does not belong to the current guardian.', 403);

            return $student;
        }

        throw new InvalidArgumentException('Unauthorized access. Only students and guardians can view this schedule.', 403);
    }

    private function getAuthTeacher(Request $request)
    {
        $user = $request->user();
        $staff = $user->staff;

        if (!$staff || !$user->hasRole('teacher')) {
            throw new InvalidArgumentException('This account does not belong to a registered teacher.', 403);
        }

        return $staff;
    }

    private function getAuthStaff(Request $request)
    {
        $user = $request->user();
        $staff = $user->staff;

        if (!$staff && !$user->hasAnyRole(['super_admin', 'adviser', 'teacher'])) {
            throw new InvalidArgumentException('This account does not belong to a registered staff member.', 403);
        }

        return $staff;
    }

    private function getCurrentEnrollment($student): Enrollment
    {
        $enrollment = Enrollment::query()
            ->where('student_id', $student->id)
            ->whereHas('academicYear', function ($query) {
                $query->where('is_current', true);
            })
            ->latest()
            ->first();

        if (!$enrollment || !$enrollment->class_room_id) {
            throw new InvalidArgumentException('This student does not have an active enrollment in any classroom.', 404);
        }

        return $enrollment;
    }
}

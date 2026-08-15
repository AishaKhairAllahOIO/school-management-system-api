<?php

namespace App\Http\Controllers\Scheduling;

use App\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\Scheduale\StoreExamScheduleRequest;
use App\Http\Requests\Scheduale\UpdateExamScheduleRequest;
use App\Http\Resources\Scheduale\ExamResource;
use App\Models\Enrollment;
use App\Services\Schedule\ExamService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class ExamScheduleController extends Controller
{
    use ApiResource;

    public function __construct(
        private ExamService $examService
    ) {
    }

    private function getResolvedStudent(Request $request)
    {
        $user = $request->user();

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

    public function getSetupData(int $gradeLevelId): JsonResponse
    {
        try {
            $data = $this->examService->getSetupDataForGrade($gradeLevelId);

            return $this->successResponse(
                $data,
                'Exam setup data retrieved successfully.'
            );
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function store(StoreExamScheduleRequest $request): JsonResponse
    {
        try {
            $exam = $this->examService->createExamSchedule($request->validated());

            return $this->successResponse(
                new ExamResource($exam),
                'Exam schedule created successfully.',
                201
            );
        } catch (Exception $e) {
            return $this->errorResponse('Failed to create exam schedule: ' . $e->getMessage(), 500);
        }
    }

    public function destroy(int $examId): JsonResponse
    {
        try {
            $this->examService->deleteExamSchedule($examId);
            return $this->successResponse(null, 'Exam schedule deleted successfully.');
        } catch (Exception $e) {
            return $this->errorResponse('Failed to delete exam schedule.', 500);
        }
    }

    public function destroySubject(int $examId, int $gradeSubjectId): JsonResponse
    {
        try {
            $this->examService->deleteExamSubject($examId, $gradeSubjectId);
            return $this->successResponse(null, 'Subject removed from exam schedule successfully.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('The selected exam or subject was not found.', 404);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to remove subject from exam schedule: ' . $e->getMessage(), 500);
        }
    }

    public function studentExams(Request $request): JsonResponse
    {
        try {
            $student = $this->getResolvedStudent($request);
            $enrollment = $this->getCurrentEnrollment($student);

            $gradeLevelId = $enrollment->classRoom->grade_level_id;

            $data = $this->examService->getStudentExams($gradeLevelId, $student->id, $request->user()->id);

            return $this->successResponse(
                $data,
                'Student exams retrieved successfully.'
            );
        } catch (Exception $e) {
            $code = $e->getCode() ?: 400;
            return $this->errorResponse($e->getMessage(), $code);
        }
    }

    public function teacherExams(Request $request): JsonResponse
    {

        try {
            $teacher = $this->getAuthTeacher($request);

            $data = $this->examService->getTeacherExams($teacher->id);

            return $this->successResponse(
                $data,
                'Teacher exams retrieved successfully.'
            );
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 403);
        }
    }
    public function adminExams(Request $request, int $academicYearId, int $semesterId): JsonResponse
    {

        try {
            $admin = $this->getAuthStaff($request);

            $data = $this->examService->getAllExamsForAdmin($academicYearId, $semesterId);

            return $this->successResponse(
                $data,
                'Admin exams retrieved successfully.'
            );
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 403);
        }
    }

    public function unreadCount(Request $request): JsonResponse
    {
        try {
            $student = $this->getResolvedStudent($request);
            $enrollment = $this->getCurrentEnrollment($student);
            $gradeLevelId = $enrollment->classRoom->grade_level_id;

            $counts = $this->examService->unreadCount($request->user(), $gradeLevelId, $student->id);

            return $this->successResponse(
                $counts,
                'Unread counts retrieved successfully.'
            );
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'nullable|string|in:exam,quiz'
        ]);

        try {
            $student = $this->getResolvedStudent($request);
            $enrollment = $this->getCurrentEnrollment($student);
            $gradeLevelId = $enrollment->classRoom->grade_level_id;

            $type = $request->input('type');

            $this->examService->markAllRead($request->user(), $gradeLevelId, $student->id, $type);

            $message = $type
                ? "All unread {$type}s marked as read successfully."
                : 'All exams and quizzes marked as read successfully.';

            return $this->successResponse(null, $message);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function update(UpdateExamScheduleRequest $request, int $examId): JsonResponse
    {
        try {
            $exam = $this->examService->updateExamSchedule($examId, $request->validated());

            return $this->successResponse(
                new ExamResource($exam),
                'Exam schedule updated successfully.',
                200
            );
        } catch (Exception $e) {
            return $this->errorResponse('Failed to update exam schedule: ' . $e->getMessage(), 500);
        }
    }


}

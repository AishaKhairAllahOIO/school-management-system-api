<?php

namespace App\Http\Controllers\Teacher;

use App\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StoreMarksRequest;
use App\Services\Teacher\MarkService;
use App\Traits\StaffAuthorizationTrait;
use Exception;
use Illuminate\Http\Request;

class MarkController extends Controller
{
    use ApiResource, StaffAuthorizationTrait;

    protected MarkService $markService;

    public function __construct(MarkService $markService)
    {
        $this->markService = $markService;
    }

    public function getGradebook(Request $request, $gradeSubjectId, $classRoomId)
    {
        try {
            if (!$this->checkTeacherMarkAccess($request->user(), (int) $gradeSubjectId, (int) $classRoomId)) {
                return $this->errorResponse("Access denied.", 403);
            }

            $matrix = $this->markService->getGradebookMatrix((int) $gradeSubjectId, (int) $classRoomId);
            return $this->successResponse($matrix, "Gradebook retrieved successfully.", 200);

        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function storeMarks(StoreMarksRequest $request)
    {
        try {
            $validated = $request->validated();
            $user = $request->user();

            if (!$this->checkTeacherMarkAccess($user, (int) $validated['grade_subject_id'], (int) $validated['class_room_id'])) {
                return $this->errorResponse("Access denied.", 403);
            }

            $staffId = $user->staff->id;
            $this->markService->saveMarksBulk($validated, $staffId);

            return $this->successResponse(null, "Marks saved successfully.", 200);

        } catch (Exception $e) {
            $code = $e->getCode() == 422 ? 422 : 500;
            return $this->errorResponse($e->getMessage(), $code);
        }
    }

    public function index(int $academicYearId)
    {

        try {
            $marks = $this->markService->getAllMarksForAdmin(
                $academicYearId
            );

            return $this->successResponse($marks, 'All student marks retrieved successfully.', 200);

        } catch (Exception $e) {
            $code = $e->getCode() == 422 ? 422 : 500;
            return $this->errorResponse($e->getMessage(), $code);
        }

    }



}

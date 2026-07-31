<?php

namespace App\Http\Controllers\Teacher;

use App\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StoreMarksRequest;
use App\Policies\MarkPolicy;
use App\Services\Teacher\MarkService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Exception;
use Illuminate\Http\Request;

class MarkController extends Controller
{
    use ApiResource, AuthorizesRequests;


    protected MarkService $markService;

    public function __construct(MarkService $markService)
    {
        $this->markService = $markService;
    }

    public function getGradebook(Request $request, $gradeSubjectId, $classRoomId)
    {
        try {
            $this->authorize('viewGradebook', [MarkPolicy::class, (int) $gradeSubjectId, (int) $classRoomId]);

            $matrix = $this->markService->getGradebookMatrix((int) $gradeSubjectId, (int) $classRoomId);
            return $this->successResponse($matrix, 'Grade book shown successfully.', 200);

        } catch (Exception $e) {
            $code = $e->getCode() == 403 ? 403 : 500;
            return $this->errorResponse($e->getMessage() ?: 'غير مصرح لك بالوصول لعلامات هذه الشعبة.', $code);
        }
    }

    public function storeMarks(StoreMarksRequest $request)
    {
        try {
            $validated = $request->validated();
            $user = $request->user();

            $this->authorize('updateMarks', [MarkPolicy::class, (int) $validated['grade_subject_id'], (int) $validated['class_room_id']]);

            $staffId = $user->staff->id;
            $this->markService->saveMarksBulk($validated, $staffId);

            return $this->successResponse(null, 'تم رصد وتحديث العلامات بنجاح.', 200);

        } catch (Exception $e) {
            $code = in_array($e->getCode(), [423, 422, 403]) ? $e->getCode() : 500;
            return $this->errorResponse($e->getMessage(), $code);
        }
    }
}

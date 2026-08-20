<?php

namespace App\Http\Controllers\Teacher;

use App\ApiResource;
use App\Http\Controllers\Controller;
use App\Models\ClassRoom;
use App\Services\Teacher\TeacherDropdownService;
use App\Traits\StaffAuthorizationTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Exception;

class TeacherDropdownController extends Controller
{
    use ApiResource, StaffAuthorizationTrait;

    public function __construct(protected TeacherDropdownService $dropdownService) {}


    public function subjectsTree(Request $request): JsonResponse
    {
        try {
            $tree = $this->dropdownService->getSubjectsTree($request->user());
            return $this->successResponse($tree, 'تم جلب شجرة المواد والصفوف والشعب بنجاح.');
        } catch (Exception $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }


    public function classroomStudents(Request $request, int $classRoomId): JsonResponse
    {
        try {
            $user = $request->user();
            $classroom = ClassRoom::findOrFail($classRoomId);

            $isAuthorized = $this->checkClassroomAccess($user, $classroom->grade_level_id, [$classRoomId]);

            if (!$isAuthorized) {
                throw new AccessDeniedHttpException('غير مصرح لك بعرض طلاب هذه الشعبة، فهي ليست ضمن نصابك التدريسي.');
            }

            $students = $this->dropdownService->getClassroomStudents($classRoomId);

            return $this->successResponse($students, 'تم جلب طلاب الشعبة بنجاح.');
        } catch (AccessDeniedHttpException $e) {
            return $this->errorResponse($e->getMessage(), 403);
        } catch (Exception $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }
}

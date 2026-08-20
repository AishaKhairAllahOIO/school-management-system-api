<?php

namespace App\Http\Controllers\Student;

use App\ApiResource;
use App\Http\Controllers\Controller;
use App\Services\Student\StudentMarkDisplayService;
use Illuminate\Http\Request;
use Exception;
use Symfony\Component\HttpKernel\Exception\HttpException;

class StudentMarkDisplayController extends Controller
{
    use ApiResource;

    protected StudentMarkDisplayService $markDisplayService;

    public function __construct(StudentMarkDisplayService $markDisplayService)
    {
        $this->markDisplayService = $markDisplayService;
    }

    public function index(Request $request)
    {
        try {
            $studentId = $request->input('student_id');
            $marks = $this->markDisplayService->getMarks($request->user(), $studentId ? (int) $studentId : null);



            return $this->paginatedResponse($marks, 'Marks retrieved successfully.', 200);
        } catch (Exception $e) {
            $code = $this->getExceptionCode($e);
            return $this->errorResponse($e->getMessage(), $code);
        }
    }

    public function unreadCount(Request $request)
    {
        try {
            $studentId = $request->input('student_id');
            $data = $this->markDisplayService->unreadCount($request->user(), $studentId ? (int) $studentId : null);

            return $this->successResponse($data, 'Unread marks count retrieved successfully.', 200);
        } catch (Exception $e) {
            $code = $this->getExceptionCode($e);
            return $this->errorResponse($e->getMessage(), $code);
        }
    }

    public function markAllAsRead(Request $request)
    {
        try {
            $studentId = $request->input('student_id');
            $data = $this->markDisplayService->markAllAsRead($request->user(), $studentId ? (int) $studentId : null);

            return $this->successResponse($data, 'All marks have been marked as read successfully.', 200);
        } catch (Exception $e) {
            $code = $this->getExceptionCode($e);
            return $this->errorResponse($e->getMessage(), $code);
        }
    }

}

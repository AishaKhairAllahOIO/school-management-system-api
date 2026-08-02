<?php

namespace App\Http\Controllers\Admin;

use App\ApiResource;
use App\Http\Controllers\Controller;
use App\Services\User\AlertService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SystemNoticeController extends Controller
{
    use ApiResource;

    public function __construct(protected AlertService $alertService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $staff = $request->user()->staff;

        if (!$staff) {
            return $this->errorResponse('Unauthorized access.', 403);
        }

        $notices = $this->alertService->showSystemNotices($staff);

        return $this->paginatedResponse(
            $notices,
            'System notices retrieved successfully.',
            200
        );
    }

    public function unreadCount(Request $request): JsonResponse
    {
        $user = $request->user();

        $count = $this->alertService->unreadSystemNoticesCount($user);

        return $this->successResponse(
            ['unread_count' => $count],
            'Unread system notices count retrieved successfully.',
            200
        );
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = $request->user();

        $this->alertService->markAllSystemNoticesAsRead($user);

        return $this->successResponse(
            ['unread_count' => 0],
            'All system notices have been marked as read successfully.',
            200
        );
    }
}

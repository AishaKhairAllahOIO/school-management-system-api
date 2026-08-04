<?php

namespace App\Http\Controllers\Admin;

use App\ApiResource;
use App\Http\Controllers\Controller;
use App\Services\User\AlertService;
use App\Services\User\AnnouncementService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BellController extends Controller
{
    use ApiResource;

    public function __construct(
        protected AlertService $alertService,
        protected AnnouncementService $announcementService
    ) {}


    public function getBellUnreadCount(Request $request): JsonResponse
    {
        $user = $request->user();

        $alertCounts = $this->alertService->unreadCountForUser($user);

        $announcementsCount = $this->announcementService->unreadCount($user);

        $totalBellCount = $alertCounts['alerts'] + $alertCounts['system_alerts'] + $announcementsCount;

        return $this->successResponse(
            [
                'total_unread' => $totalBellCount,
            ],
            'Bell unread counts retrieved successfully.',
            200
        );
    }


    public function markAllBellItemsAsRead(Request $request): JsonResponse
    {
        $user = $request->user();

        $this->alertService->markAllReadForUser($user, 'general');
        $this->alertService->markAllSystemNoticesAsRead($user);
        $this->alertService->markAllReadForUser($user, 'financial');

        $this->announcementService->markAllAsRead($user);

        return $this->successResponse(
            [
                'total_unread' => 0,
            ],
            'All bell notifications and announcements have been marked as read successfully.',
            200
        );
    }
}

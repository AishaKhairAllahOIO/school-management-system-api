<?php

namespace App\Http\Controllers\Admin;

use App\ApiResource;
use App\Http\Controllers\Controller;
use App\Services\User\AlertService;
use App\Services\User\AnnouncementService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Exception;

class BellController extends Controller
{
    use ApiResource;

    public function __construct(
        protected AlertService $alertService,
        protected AnnouncementService $announcementService
    ) {}

    public function getBellUnreadCount(Request $request): JsonResponse
    {
        try {
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
        } catch (Exception $e) {

            Log::error('Error fetching bell unread count: ' . $e->getMessage(), [
                'user_id' => $request->user()?->id
            ]);

            return $this->errorResponse(
                'An error occurred while fetching the unread notifications count.',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    public function markAllBellItemsAsRead(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

           $al= $this->alertService->markAllReadForUser($user, 'all');
           $an= $this->announcementService->markAllAsRead($user);

            return $this->successResponse(
                [
                    'total_unread' => 0,

                ],
                'All bell notifications and announcements have been marked as read successfully.',
                200
            );
        } catch (Exception $e) {

            Log::error('Error marking all bell items as read: ' . $e->getMessage(), [
                'user_id' => $request->user()?->id
            ]);

            return $this->errorResponse(
                'An error occurred while marking notifications as read.',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }
}

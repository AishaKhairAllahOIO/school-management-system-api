<?php

namespace App\Http\Controllers\User;

use App\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\AnnouncementRequest;
use App\Http\Requests\Web\UpdateAnnouncementRequest;
use App\Http\Resources\User\AnnouncementResource;
use App\Models\Announcement;
use App\Services\User\AnnouncementService;
use Illuminate\Http\Request;

class UserAnnouncementController extends Controller
{
    use ApiResource;
    public function __construct(
        private readonly AnnouncementService $service
    ) {
    }

    public function store(AnnouncementRequest $request)
    {
        $announcement = $this->service->create($request->validated());

        return $this->successResponse(
            new AnnouncementResource($announcement),
            'Announcement created successfully.',
            201
        );
    }

    public function update(UpdateAnnouncementRequest $request, int $id)
    {
        $announcement = $this->service->update($id, $request->validated());

        return $this->successResponse(
            new AnnouncementResource($announcement),
            'Announcement updated successfully.',
            200
        );
    }


    public function announcementsForStaff(Request $request)
    {
        $user = $request->user();

        if ($user->hasRole('guardian') || $user->hasRole('student')) {
            return $this->errorResponse('هذا الحساب ليس حساب موظف.', 403);
        }

        $announcements = $this->service->forStaff($user);
        $announcements->through(fn($announcement) => new AnnouncementResource($announcement));
        return $this->paginatedResponse(
            $announcements,
            'Announcements for staff retrieved successfully.',
        );
    }

    public function studentAnnouncements(Request $request)
    {
        $user = $request->user();

        if (!$user->hasRole('student')) {
            return $this->errorResponse('This account is not a student account.', 403);
        }
        $announcements = $this->service->forStudent($user);
        $announcements->through(fn($announcement) => new AnnouncementResource($announcement));
        return $this->paginatedResponse(
            $announcements,
            'Announcements for students retrieved successfully.',
            200
        );
    }

    public function guardianAnnouncements(Request $request)
    {
        $user = $request->user();

        if (!$user->guardian) {
            return $this->errorResponse('This account is not a guardian account.', 403);
        }

        $studentId = $request->student_id;

        if ($studentId) {
            $isHisChild = $user->guardian->students()->where('students.id', $studentId)->exists();
            if (!$isHisChild) {
                return $this->errorResponse('This student does not belong to the current guardian.', 404);
            }
        }

        $announcements = $this->service->forGuardian($user, $studentId);
$announcements->through(fn($announcement) => new AnnouncementResource($announcement));
        return $this->paginatedResponse(
            $announcements,
            'Announcements for guardian retrieved successfully.',
            200
        );
    }

    public function adminAnnouncements(Request $request)
    {
        if (!$request->user()->hasAnyRole(['super_admin', 'adviser'])) {
            return $this->errorResponse('You are not authorized to view admin announcements.', 403);
        }

        $announcements = $this->service->getAdminAnnouncements($request->user());
        $announcements->through(fn($announcement) => new AnnouncementResource($announcement));
        return $this->paginatedResponse(
            $announcements,
            'Your announcements retrieved successfully.'
        );
    }

    public function destroy(Request $request, int $id)
    {
        $announcement = Announcement::find($id);

        if (!$announcement) {
            return $this->errorResponse('The announcement does not exist.', 404);
        }

        if (!$request->user()->can('delete', $announcement)) {
            return $this->errorResponse('You are not authorized to delete this announcement.', 403);
        }

        $this->service->delete($id);

        return $this->successResponse(null, 'The announcement has been deleted successfully.');
    }

    public function getUnreadCount(Request $request)
    {
        $count = $this->service->unreadCount($request->user(), $request->student_id);
        return $this->successResponse(['count' => $count], 'Success', 200);
    }

    public function markAllAsRead(Request $request)
    {
        $this->service->markAllAsRead($request->user(), $request->student_id);
        return $this->successResponse(null, 'The unread announcements count has been reset.', 200);
    }


}

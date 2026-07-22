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
            'تم نشر الإعلان بنجاح.',
            201
        );
    }

    public function update(UpdateAnnouncementRequest $request, int $id)
    {
        $announcement = $this->service->update($id, $request->validated());

        return $this->successResponse(
            new AnnouncementResource($announcement),
            'تم تعديل الإعلان بنجاح.',
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

        return $this->paginatedResponse(
            AnnouncementResource::collection($announcements),
            'تم جلب الإعلانات بنجاح.'
        );
    }

    public function studentAnnouncements(Request $request)
    {
        $user = $request->user();

        if (!$user->hasRole('student')) {
            return $this->errorResponse('هذا الحساب ليس حساب طالب.', 403);
        }
        $announcements = $this->service->forStudent($user);

        return $this->paginatedResponse(
            AnnouncementResource::collection($announcements),
            'success',
            200
        );
    }

    public function guardianAnnouncements(Request $request)
    {
        $user = $request->user();

        if (!$user->guardian) {
            return $this->errorResponse('هذا الحساب ليس حساب ولي أمر.', 403);
        }

        $studentId = $request->student_id;

        if ($studentId) {
            $isHisChild = $user->guardian->students()->where('students.id', $studentId)->exists();
            if (!$isHisChild) {
                return $this->errorResponse('هذا الطالب غير موجود أو غير مرتبط بحسابك.', 404);
            }
        }

        $announcements = $this->service->forGuardian($user, $studentId);

        return $this->paginatedResponse(
            AnnouncementResource::collection($announcements),
            'تم جلب إعلانات الطالب بنجاح',
            200
        );
    }

    public function adminAnnouncements(Request $request)
    {
        if (!$request->user()->hasAnyRole(['super_admin', 'adviser'])) {
            return $this->errorResponse('غير مصرح لك بالوصول', 403);
        }

        $announcements = $this->service->getAdminAnnouncements($request->user());
        return $this->paginatedResponse(AnnouncementResource::collection($announcements), 'إعلانات الطلاب (لوحة الإدارة)');
    }

    public function destroy(Request $request, int $id)
    {
        $announcement = Announcement::find($id);

        if (!$announcement) {
            return $this->errorResponse('الإعلان غير موجود.', 404);
        }

        if (!$request->user()->can('delete', $announcement)) {
            return $this->errorResponse('غير مصرح لك بحذف هذا الإعلان.', 403);
        }

        $this->service->delete($id);

        return $this->successResponse(null, 'تم حذف الإعلان بنجاح.');
    }

    public function getUnreadCount(Request $request)
    {
        $count = $this->service->unreadCount($request->user(), $request->student_id);
        return $this->successResponse(['count' => $count], 'success', 200);
    }

    public function markAllAsRead(Request $request)
    {
        $this->service->markAllAsRead($request->user(), $request->student_id);
        return $this->successResponse(null, 'تم تصفير عداد الإعلانات', 200);
    }

    
}

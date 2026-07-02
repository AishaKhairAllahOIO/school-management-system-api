<?php

namespace App\Http\Controllers\User;

use App\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\AnnouncementRequset;
use App\Http\Resources\User\AnnouncementResource;
use App\Services\User\AnnouncementService;
use Illuminate\Http\Request;

class UserAnnouncementController extends Controller
{
use ApiResource;
    public function __construct(
        private readonly AnnouncementService $service
    ) {}

    // ---------- إنشاء (المدير) ----------
    public function store(AnnouncementRequset $request)
    {
        $announcement = $this->service->create($request->validated());

        return $this->successResponse(
            new AnnouncementResource($announcement),
            'تم نشر الإعلان بنجاح.',
            201
        );
    }


    public function announcementsForStaff()
    {
        $announcements = $this->service->forStaff();

        return $this->successResponse(
            AnnouncementResource::collection($announcements),
            'تم جلب الإعلانات بنجاح.'
        );
    }

    public function announcementsForStudent()
    {
        $announcements = $this->service->forStudent();

        return $this->successResponse(
            AnnouncementResource::collection($announcements),
            'تم جلب الإعلانات بنجاح.'
        );
    }


    public function destroy(int $id){
        $this->service->delete($id);
        return $this->successResponse(null, 'تم حذف الإعلان بنجاح.');
    }
}

<?php

namespace App\Http\Controllers\User;

use App\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\AnnouncementRequset;
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
}

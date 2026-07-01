<?php

namespace App\Http\Controllers\Notification;

use App\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\Notification\StoreDeviceTokenRequest;
use App\Models\DeviceToken;
use App\Services\Notification\DeviceTokenService;
use Illuminate\Http\Request;

// Controller
class DeviceTokenController extends Controller
{
    use ApiResource;
    private DeviceTokenService $deviceTokenService;

    public function __construct(DeviceTokenService $deviceTokenService)
    {
        $this->deviceTokenService=$deviceTokenService;
    }


    public function store(StoreDeviceTokenRequest $request)
{

$user =$request->user();
    $this->deviceTokenService->registerToken(
        $user,
        $request->fcm_token
    );

    return $this->successResponse(null, 'تم تسجيل الجهاز للإشعارات.', 200);
}


    public function destroy(Request $request)
    {

    $this->deviceTokenService->deleteToken($request->fcm_token);
        return $this->successResponse(null, 'تم إلغاء تسجيل الجهاز.', 200);
    }
}

<?php

namespace App\Http\Controllers\User;

use App\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Resources\Auth\ProfileResource;
use App\Models\User;
use App\Services\User\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\User\UpdateStaffRequest;
use App\Http\Resources\Auth\AcademicProfileResource;

class UserController extends Controller
{
    use ApiResource;
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function getUserInfo(Request $request)
    {

        $user = $request->user();

        if (!$user) {
            return $this->errorResponse('User not found.', 404);
        }

        if ($user->hasRole('student'))
            $result = $this->userService->getStudent($user);

        else if ($user->hasRole('guardian'))
            $result = $this->userService->getGuardian($user);

        return $this->successResponse($result, 'تم جلب بيانات لوحةالتحكم بنجاح', 200);
    }
    public function myProfile()
    {

        $user = $this->userService->getAuthenticatedProfile(Auth::user());

        return $this->successResponse(new AcademicProfileResource($user), 'تم استعراض الملف الشخصي بنجاح', 200);
    }
    public function updateMyAdminProfile(UpdateStaffRequest $request)
    {
    ;
        $updatedAdmin = $this->userService->updateStaffRecord(
            Auth::user(),
            $request->validated()
        );

        return $this->successResponse(
             new AcademicProfileResource($updatedAdmin), 
            'تم تحديث بيانات الملف الشخصي للمدير العام بنجاح', 200
        );
    }
}

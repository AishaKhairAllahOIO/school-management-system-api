<?php

namespace App\Http\Controllers\User;

use App\ApiResource;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\User\UserService;

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
}

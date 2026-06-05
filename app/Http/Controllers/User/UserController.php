<?php

namespace App\Http\Controllers\User;

use App\ApiResource;
use App\Http\Controllers\Controller;
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

    public function UserDashbourd(Request $request){

        $user=$request->user()->load();

        $result = $this->userService->getStudentDashboard($user);

        return $this->successResponse($result, 'تم جلب بيانات لوحة التحكم بنجاح', 200);


    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\SystemAccessLoginRequest;
use App\Http\Requests\Auth\SystemAccessVerifyRequest;
use App\Http\Resources\Auth\SystemAccessResource;
use App\Services\Auth\SystemAccessService;

class SystemAccessController extends Controller
{
    use ApiResource;

    public function login(SystemAccessLoginRequest $request, SystemAccessService $service)
    {
        $service->login($request->validated());

        return $this->successResponse('', 'wait a code to complete logging in', 200);
    }
    public function verifyOtp(SystemAccessVerifyRequest $request,SystemAccessService $service) {
      $access= $service->verifyOtp($request->validated());
      $responseData = [
            'user'  => new SystemAccessResource($access['data']),
            'token' => $access['token']];
      return $this->successResponse($responseData,'login successful',200);
      
    }

    public function logout(SystemAccessService $service)
    {
        $service->logout();

        return $this->successResponse('', 'Logout successful', 200);
    }
}


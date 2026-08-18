<?php

namespace App\Http\Controllers\Admin\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\DashboardService;
use App\ApiResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Throwable;

class DashboardController extends Controller
{
    use ApiResource;

    protected DashboardService $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * 👑 لوحة تحكم المدير العام
     */
    public function superAdminDashboard(): JsonResponse
    {
        try {
            $data = $this->dashboardService->getSuperAdminDashboard();
            return $this->successResponse($data,'Super admin dashboard statistics retrieved successfully.');
        } catch (Throwable $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * 🧑‍🏫 لوحة تحكم الموجه
     */
    public function adviserDashboard(): JsonResponse
    {
        try {
            $data = $this->dashboardService->getAdviserDashboard();
            return $this->successResponse($data, 'Adviser dashboard statistics retrieved successfully.');
        } catch (Throwable $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * 💼 لوحة تحكم السكرتير
     */
    public function secretaryDashboard(): JsonResponse
    {
        try {
            $data = $this->dashboardService->getSecretaryDashboard();
            return $this->successResponse($data, 'Secretary dashboard statistics retrieved successfully.');
        } catch (Throwable $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }
    public function index(): JsonResponse
    {
        try {
            $user = Auth::user();

            $data = $this->dashboardService->getDashboardForAuthUser($user);

            return $this->successResponse(
                $data['dashboard_data'],
                "Dashboard statistics retrieved successfully for role ({$data['role']})."
            );
        } catch (Throwable $e) {
            $statusCode = $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500;
            return $this->errorResponse('Error:Server', $statusCode,[$e->getMessage()]);
        }
    }
    
}
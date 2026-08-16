<?php

namespace App\Http\Controllers\RoleAndPermission;

use App\Http\Controllers\Controller;
use App\Http\Requests\RoleAndPermission\SyncRolePermissionsRequest;
use App\Services\RoleAndPermission\RoleManagementService;
use App\Http\Resources\RoleAndPermission\RoleResource;
use App\ApiResource;
use Exception;
use Illuminate\Http\JsonResponse;

class RoleController extends Controller
{
    use ApiResource;

    protected $roleService;

    public function __construct(RoleManagementService $roleService)
    {
        $this->roleService = $roleService;
    }

    public function index(): JsonResponse
    {
        $roles = $this->roleService->getRolesWithStatistics();
        return $this->successResponse(
            RoleResource::collection($roles),
            'Roles and statistics retrieved successfully.'
        );
    }

    public function getSystemModules(): JsonResponse
    {
        $modules = $this->roleService->getSystemModules();
        return $this->successResponse(
            $modules,
            'System modules and permissions retrieved successfully.'
        );
    }

    public function sync(SyncRolePermissionsRequest $request, $id): JsonResponse
    {
        try {
            $permission = $this->roleService->syncPermissions($id, $request->validated('permissions'));
            return $this->successResponse($permission, 'Role permissions synchronized successfully.');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode() ?: 400);
        }
    }
}
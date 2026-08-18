<?php

namespace App\Http\Controllers\Admin\Leave;

use App\Http\Controllers\Controller;

use App\ApiResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use Throwable;
use App\Services\Leave\StaffLeaveTypeService;
use App\Http\Resources\Leave\StaffLeaveTypeResource;
use App\Http\Requests\Admin\Leave\StoreStaffLeaveTypeRequest;
use App\Http\Requests\Admin\Leave\UpdateStaffLeaveTypeRequest;


class StaffLeaveTypeController extends Controller
{
    use ApiResource;

    protected StaffLeaveTypeService $service;

    public function __construct(StaffLeaveTypeService $service)
    {
        $this->service = $service;
    }

    public function index(): JsonResponse
    {
        try {
            $types = $this->service->getAllLeaveTypes();
            return $this->successResponse(StaffLeaveTypeResource::collection($types), 'Staff leave types retrieved successfully.');
        } catch (Throwable $e) {
            return $this->errorResponse('Error:Server', 500,[$e->getMessage()]);
        }
    }
    public function show(int $id)
    {
        try{
            
            $leaveType = $this->service->getLeaveById($id);
            return $this->successResponse(new StaffLeaveTypeResource($leaveType), 'Staff leave type retrieved successfully.');
         }catch (ModelNotFoundException $e) {
            return $this->errorResponse('Staff leave type not found.', 404);
         }
    }

    public function store(StoreStaffLeaveTypeRequest $request): JsonResponse
    {
        try {
            $type = $this->service->createLeaveType($request->validated());
            return $this->successResponse(new StaffLeaveTypeResource($type), 'Staff leave type created successfully.', 201);
        } catch (Throwable $e) {
            return $this->errorResponse('Error:Server', 500,[$e->getMessage()]);
        }
    }

    public function update(UpdateStaffLeaveTypeRequest $request, int $id): JsonResponse
    {
        try {
            $type = $this->service->updateLeaveType($id, $request->validated());
            return $this->successResponse(new StaffLeaveTypeResource($type), 'Staff leave type updated successfully.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Staff leave type not found.', 404);
        } catch (Throwable $e) {
            return $this->errorResponse('Error:Server', 500,[$e->getMessage()]);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->service->deleteLeaveType($id);
            return $this->successResponse(null, 'Staff leave type deleted successfully.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Staff leave type not found.', 404);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        } catch (Throwable $e) {
            return $this->errorResponse('Error:Server', 500,[$e->getMessage()]);
        }
    }
}
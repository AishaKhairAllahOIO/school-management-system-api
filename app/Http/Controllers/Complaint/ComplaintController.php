<?php

namespace App\Http\Controllers\Complaint;

use App\Http\Controllers\Controller;
use App\Http\Resources\Complaint\ComplaintResource;
use App\Services\Complaint\ComplaintService;
use App\ApiResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ComplaintController extends Controller
{
    use ApiResource;

    public function __construct(private ComplaintService $complaintService)
    {
    }


    public function options(): JsonResponse
    {
        try {
            $options = $this->complaintService->getComplaintOptions();

            return $this->successResponse(
                $options,
                'Complaint options retrieved successfully.'
            );
        } catch (Exception $e) {
            return $this->errorResponse('Failed to retrieve complaint options.', 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'student_id' => 'required|integer|exists:students,id',
            'complaint_type_id' => 'required|integer|exists:complaint_types,id',
        ]);

        try {
            $guardianId = $request->user()->guardian->id;

            $complaint = $this->complaintService->submitComplaint($validatedData, $guardianId);

            return $this->successResponse(
                new ComplaintResource($complaint),
                'Complaint submitted successfully. Administration has been notified.',
                201
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Student or Guardian not found.', 404);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to submit complaint: ' . $e->getMessage(), 500);
        } catch (Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                422
            );
        }
    }


    public function index(Request $request, int $studentId): JsonResponse
    {
        try {
            $guardianId = $request->user()->guardian->id;

            $complaints = $this->complaintService
                ->getGuardianComplaints($guardianId, $studentId);

            return $this->successResponse(
                ComplaintResource::collection($complaints),
                'Guardian complaints retrieved successfully.',
                200
            );

        } catch (Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                403
            );
        }
    }

    public function update(Request $request, int $complaintId): JsonResponse
    {
        $validatedData = $request->validate([
            'student_id' => 'sometimes|integer|exists:students,id',
            'complaint_type_id' => 'sometimes|integer|exists:complaint_types,id',
        ]);

        try {
            $guardianId = $request->user()->guardian->id;

            $complaint = $this->complaintService->updateComplaint($complaintId, $guardianId, $validatedData);

            return $this->successResponse(
                new ComplaintResource($complaint),
                'Complaint updated successfully.',
                200
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Complaint not found or you do not have permission to edit it.', 404);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to update complaint: ' . $e->getMessage(), 500);
        }
    }


    public function destroy(Request $request, int $complaintId): JsonResponse
    {
        try {
            $guardianId = $request->user()->guardian->id;

            $this->complaintService->deleteComplaint($complaintId, $guardianId);

            return $this->successResponse(
                null,
                'Complaint deleted successfully.',
                200
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Complaint not found or you do not have permission to delete it.', 404);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to delete complaint: ' . $e->getMessage(), 500);
        }
    }
}
<?php

namespace App\Http\Controllers\Setting;
use App\Http\Controllers\Controller;

use App\Http\Requests\Setting\UpdateGeneralSettingsRequest;
use App\Http\Resources\Setting\GeneralSettingsResource;
use App\Services\Setting\SchoolSettingsService;
use App\ApiResource; 
use Illuminate\Validation\ValidationException;
use Exception;

class SchoolSettingsController extends Controller
{
    use ApiResource;

    public function show(SchoolSettingsService $service)
    {
        try {
            $settings = $service->getSettings();
            
            if (!$settings) {
                return $this->errorResponse('School settings have not been initialized yet.', 404);
            }
            
            return $this->successResponse(
                new GeneralSettingsResource($settings), 
                'Settings retrieved successfully.', 
                200
            );
            
        } catch (Exception $e) {
            return $this->errorResponse('An error occurred while fetching settings.', 500, ['exception_message' => $e->getMessage()]);
        }
    }

    public function update(UpdateGeneralSettingsRequest $request, SchoolSettingsService $service)
    {
        try {
            $updatedSettings = $service->updateSettings($request->validated());
            
            return $this->successResponse(
                new GeneralSettingsResource($updatedSettings), 
                'General settings updated successfully.', 
                200
            );
            
        } catch (ValidationException $e) {
            return $this->errorResponse($e->getMessage(), 422, $e->errors());
        } catch (Exception $e) {
            return $this->errorResponse('Failed to update settings.', 500, ['exception_message' => $e->getMessage()]);
        }
    }
}

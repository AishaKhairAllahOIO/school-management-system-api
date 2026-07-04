<?php

namespace App\Http\Controllers\Setting;
use App\Http\Controllers\Controller;

use App\Http\Requests\Setting\UpdateGeneralSettingsRequest;
use App\Http\Resources\Setting\GeneralSettingsResource;
use App\Services\Setting\SchoolSettingsService;
use App\ApiResource; 
use Illuminate\Validation\ValidationException;
use Exception;
use App\Http\Requests\Setting\AddSchoolImageRequest;
use App\Http\Resources\Setting\SchoolImageResource;
use App\Models\SchoolImage;
use App\Http\Requests\Setting\UpdateSchoolImageRequest;

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
    
    public function storeImages(AddSchoolImageRequest $request,SchoolSettingsService $service)
    {
        $images = $service->addSchoolImages($request->validated());

        return $this->successResponse(
            SchoolImageResource::collection($images),
            'تم إضافة رابط الصورة إلى المعرض بنجاح.',
            201
        );
    }
        public function updateImage(UpdateSchoolImageRequest $request, SchoolImage $image, SchoolSettingsService $service)
    {
        $updatedImage = $service->updateSchoolImage($image, $request->validated());

        return $this->successResponse(
            new SchoolImageResource($updatedImage),
            'تم تحديث بيانات الصورة بنجاح.',
            200
        );
    }

    public function destroyImage(SchoolImage $image, SchoolSettingsService $service)
    {
        $service->deleteSchoolImage($image);
        return $this->successResponse(null, 'تم حذف الصورة بنجاح.');
    }

    
}

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
use Illuminate\Database\Eloquent\ModelNotFoundException;


class SchoolSettingsController extends Controller
{
    use ApiResource;

    public function show(SchoolSettingsService $service)
    {
        try {
            $settings = $service->getSettings();

            return $this->successResponse(
                new GeneralSettingsResource($settings),
                'Settings retrieved successfully.',
                200
            );

        } catch (ModelNotFoundException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        } catch (Exception $e) {
            return $this->errorResponse('Error:Server', 500, ['exception_message' => $e->getMessage()]);
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
            return $this->errorResponse('Error:Server', 500, ['exception_message' => $e->getMessage()]);
        }
    }
    public function indexImages(SchoolSettingsService $service)
    {
        $images = $service->getAllImages();

        return $this->successResponse(
            SchoolImageResource::collection($images),
            'All school images retrieved successfully.',
            200
        );
    }


    public function showImage(int $id, SchoolSettingsService $service)
    {
        try {
            $image = $service->getImageById($id);
            return $this->successResponse(
                new SchoolImageResource($image),
                'School image data retrieved successfully.'
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('The requested school image does not exist.', 404);
        } catch (Exception $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }
    public function storeImages(AddSchoolImageRequest $request, SchoolSettingsService $service)
    {
        try {
            $images = $service->addSchoolImages($request->validated());

            return $this->successResponse(
                SchoolImageResource::collection($images),
                'School image link added to the gallery successfully.',
                201
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('School settings not found.', 404);
        } catch (Exception $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }
    public function updateImage(UpdateSchoolImageRequest $request, int $image, SchoolSettingsService $service)
    {
        try {
            $updatedImage = $service->updateSchoolImage($image, $request->validated());

            return $this->successResponse(
                new SchoolImageResource($updatedImage),
                'School image updated successfully.',
                200
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('The requested school image does not exist.', 404);
        }
    }

    public function destroyImage(int $image, SchoolSettingsService $service)
    {
        try {
            $service->deleteSchoolImage($image);
            return $this->successResponse(null, 'School image deleted successfully.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('The requested school image does not exist.', 404);
        } catch (Exception $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }
    public function destroy(SchoolSettingsService $service)
    {
        try {
            $service->deleteSettings();
            return $this->successResponse(null, 'School settings deleted successfully.');
        } catch (Exception $e) {
            return $this->errorResponse('Error:Server', 500, ['error' => $e->getMessage()]);
        }
    }
    public function index(SchoolSettingsService $service)
    {
        return $this->successResponse(
            $service->index(),
            'Data retrieved successfully.',
            200
        );
    }


}

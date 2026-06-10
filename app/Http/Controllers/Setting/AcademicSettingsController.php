<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Models\AcademicSetting;
use App\Http\Requests\Setting\UpdateAcademicSettingsRequest;
use App\Services\Setting\AcademicSettingsService;
use App\Http\Resources\Setting\AcademicSettingsResource;
use App\ApiResource;
use Illuminate\Validation\ValidationException;
use Exception;

class AcademicSettingsController extends Controller
{
    use ApiResource;
    public function show()
    {
        $settings = AcademicSetting::with(['gradeScales'])->where('school_id', 1)->firstOrFail();
        
        return $this->successResponse(
            new AcademicSettingsResource($settings),
             'Academic settings retrieved successfully.'
        );
    }

    public function update(UpdateAcademicSettingsRequest $request, AcademicSettingsService $service)
    {
     //   dd($request->all());
        try{
        $updatedSettings = $service->syncSettings($request->validated());

        return $this->successResponse(
            new AcademicSettingsResource($updatedSettings),
            'Academic settings updated successfully.'
        );
           }  catch (Exception $e) {
            return $this->errorResponse('Failed to update settings.', 500, ['exception_message' => $e->getMessage()]);
        }
        }
    }


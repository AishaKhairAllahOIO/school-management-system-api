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
use Illuminate\Database\Eloquent\ModelNotFoundException; // 👈 هذا الكلاس الذي يصطاد عدم وجود الداتا


class SchoolSettingsController extends Controller
{
    use ApiResource;

    public function show(SchoolSettingsService $service)
    {
        try {
            $settings = $service->getSettings();
            
            // if (!$settings) {
            //     return $this->errorResponse('School settings have not been initialized yet.', 404);
            // }
            
            return $this->successResponse(
                new GeneralSettingsResource($settings), 
                'Settings retrieved successfully.', 
                200
            );
            
        }catch(ModelNotFoundException $e)
        {
            return $this->errorResponse($e->getMessage(),404);
        }
         catch (Exception $e) {
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
    public function indexImages(SchoolSettingsService $service)
    {
        $images = $service->getAllImages();

        return $this->successResponse(
            SchoolImageResource::collection($images),
            'تم جلب جميع الصور بنجاح.',
            200
        );
    }

    // 👈 دالة عرض صورة محددة
public function showImage(int $id, SchoolSettingsService $service)
    {
        try {
            $image = $service->getImageById($id);
            return $this->successResponse(
                new SchoolImageResource($image),
                'تم جلب بيانات الصورة بنجاح.'
            );
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('الصورة المطلوبة غير موجودة في المعرض.', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('حدث خطأ غير متوقع.', 500, ['error' => $e->getMessage()]);
        }
    }
    public function storeImages(AddSchoolImageRequest $request,SchoolSettingsService $service)
    {
        try{
        $images = $service->addSchoolImages($request->validated());

        return $this->successResponse(
            SchoolImageResource::collection($images),
            'تم إضافة رابط الصورة إلى المعرض بنجاح.',
            201
        );
        }catch(ModelNotFoundException $e)
        {
            return $this->errorResponse('إعدادات المدرسة غير موجودة.', 404);
        }catch (\Exception $e) {
            return $this->errorResponse('حدث خطأ غير متوقع.', 500, ['error' => $e->getMessage()]);
        }
    }
        public function updateImage(UpdateSchoolImageRequest $request, int $image, SchoolSettingsService $service)
    {
        try{
        $updatedImage = $service->updateSchoolImage($image, $request->validated());

        return $this->successResponse(
            new SchoolImageResource($updatedImage),
            'تم تحديث بيانات الصورة بنجاح.',
            200
        );
        }catch(ModelNotFoundException $e)
        {
            return $this->errorResponse('الصورة المحددة غير موجودة في المعرض.', 404);
        }
    }

    public function destroyImage(int $image, SchoolSettingsService $service)
    {
        try{
        $service->deleteSchoolImage($image);
        return $this->successResponse(null, 'تم حذف الصورة بنجاح.');
        }catch(ModelNotFoundException $e)
        {
            return $this->errorResponse('الصورة المحددة غير موجودة في المعرض.', 404);
        }catch (\Exception $e) {
            return $this->errorResponse('حدث خطأ غير متوقع.', 500, ['error' => $e->getMessage()]);
        }
    }
    public function destroy(SchoolSettingsService $service)
    {
        try {
            $service->deleteSettings();
            return $this->successResponse(null, 'تم حذف الإعدادات العامة للمدرسة وصور المعرض بنجاح.');
        } catch (\Throwable $e) {
            return $this->errorResponse('حدث خطأ أثناء الحذف.', 500, ['error' => $e->getMessage()]);
        }
    }
    public function index(SchoolSettingsService $service)
    {
        return $this->successResponse($service->index(),'تم جلب البيانات ',200);
    }

    
}

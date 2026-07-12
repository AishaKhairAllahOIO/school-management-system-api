<?php 
namespace App\Services\Setting;
use App\Models\School;
use Exception;
use App\Models\SchoolImage;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Storage; // 👈 لا تنسي إضافة هذه
use Illuminate\Support\Facades\DB;


class SchoolSettingsService
{
    public function getSettings()
    {
        //اريد ان تعيد لا شي اذا لم يوجد هذا الاعداد:

        $setting= School::with('images')->first();
        if (!$setting) {
            return new School(); 
        }
        return $setting;
    }

    public function updateSettings(array $validatedData)
    {
        return DB::transaction(function () use ($validatedData) {
            
            $settings = School::find(1);

            $logoPath = $settings ? $settings->logo_url : null;

            if (isset($validatedData['logo']) && $validatedData['logo'] instanceof \Illuminate\Http\UploadedFile) {
                
                // حذف الشعار القديم من السيرفر إذا كان موجوداً
                if ($logoPath && !str_starts_with($logoPath, 'http') && Storage::disk('public')->exists($logoPath)) {
                    Storage::disk('public')->delete($logoPath);
                }
                
                // رفع الشعار الجديد وتحديث مساره
                $logoPath = $validatedData['logo']->store('school_logos', 'public');
            }

            // 4. تجهيز البيانات للإنشاء أو التعديل
            $mappedData = [
                'school_name'            => $validatedData['schoolName'],
                'short_name'             => $validatedData['shortName'],
                'description'            => $validatedData['description'] ?? null,
                'phone_number'           => $validatedData['phoneNumber'],
                'emergency_phone_number' => $validatedData['emergencyPhoneNumber'] ?? null,
                'email'                  => $validatedData['email'],
                'website'                => $validatedData['website'] ?? null,
                'address'                => $validatedData['address'],
                'city'                   => $validatedData['city'],
                'country'                => $validatedData['country'],
                'latitude'               => $validatedData['location']['latitude'] ?? null,
                'longitude'              => $validatedData['location']['longitude'] ?? null,
                'logo_url'               => $logoPath, // مسار الصورة الجديد أو القديم
            ];

            // 5. ✨ السحر الخاص بكِ: إنشاء إذا لم يوجد، وتحديث إذا وجد!
            $updatedSettings = School::updateOrCreate(
                ['id' => 1], 
                $mappedData  
            );

            // إرجاع الإعدادات مع صور المعرض
            return $updatedSettings->load('images');
        });}
    // --- إضافة صورة كـ URL لمعرض المدرسة ---
    public function getAllImages()
    {
        $settings = School::firstOrCreate(['id' => 1]);
        return $settings->images;
    }
   
public function getImageById(int $id)
    {
        // findOrFail ترمي ModelNotFoundException إذا لم تجد الصورة
        return \App\Models\SchoolImage::findOrFail($id);
    } 


    public function addSchoolImages(array $data)
    {
        $settings = \App\Models\School::first(); 
        if(!$settings)
            throw new ModelNotFoundException("إعدادات المدرسة غير موجودة.");
        $imagesData = [];

        foreach ($data['images'] as $imageData) {
            $path = $imageData['file']->store('school_images', 'public');
            
            $imagesData[] = [
                'url'  => $path, // نحفظ المسار الجديد
                'name' => $imageData['name'],
            ];
        }

        return $settings->images()->createMany($imagesData);
    }

    // --- تعديل بيانات صورة موجودة (الاسم أو الملف) ---
    public function updateSchoolImage(int $id, array $data): SchoolImage
    {
        $image = SchoolImage::findOrFail($id);
        if(!$image)
            throw new ModelNotFoundException("الصورة المحددة غير موجودة في المعرض.");
        if (isset($data['file'])) {
            // حذف الصورة القديمة من السيرفر إذا كانت موجودة (ولا تبدأ بـ http)
            if ($image->url && !str_starts_with($image->url, 'http') && Storage::disk('public')->exists($image->url)) {
                Storage::disk('public')->delete($image->url);
            }
            // رفع الصورة الجديدة
            $data['url'] = $data['file']->store('school_images', 'public');
        }

        $image->update([
            'url'  => $data['url'] ?? $image->url,
            'name' => $data['name'] ?? $image->name,
        ]);

        return $image->fresh();
    }

    public function deleteSchoolImage(int $id): void
    {
        $image = SchoolImage::findOrFail($id);
        if(!$image)
            throw new ModelNotFoundException("الصورة المحددة غير موجودة في المعرض.");
        // حذف الملف الفعلي من السيرفر
        if ($image->url && !str_starts_with($image->url, 'http') && Storage::disk('public')->exists($image->url)) {
            Storage::disk('public')->delete($image->url);
        }
        
        $image->delete();
    }
        public function deleteSettings(): void
    {
        $school = School::findOrFail(1);
        if ($school) {
             if ($school->logo && !str_starts_with($school->logo, 'http') && Storage::disk('public')->exists($school->logo)) {
                Storage::disk('public')->delete($school->logo);
            }
            foreach ($school->images as $image) {
                if ($image->url && !str_starts_with($image->url, 'http') && Storage::disk('public')->exists($image->url)) {
                    Storage::disk('public')->delete($image->url);
                }
            }
            $school->delete();
        }
    }
}
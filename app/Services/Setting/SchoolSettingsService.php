<?php 
namespace App\Services\Setting;
use App\Models\School;
use Exception;
use App\Models\SchoolImage;

class SchoolSettingsService
{
    public function getSettings()
    {
        return School::with('images')->first();
    }

    public function updateSettings(array $validatedData)
    {
        $mappedData = [
            'school_name' => $validatedData['schoolName'],
            'short_name' => $validatedData['shortName'],
            'description' => $validatedData['description'] ?? null,
            'phone_number' => $validatedData['phoneNumber'],
            'emergency_phone_number' => $validatedData['emergencyPhoneNumber'] ?? null,
            'email' => $validatedData['email'],
            'website' => $validatedData['website'] ?? null,
            'address' => $validatedData['address'],
            'city' => $validatedData['city'],
            'country' => $validatedData['country'],
            'latitude' => $validatedData['location']['latitude'] ?? null,
            'longitude' => $validatedData['location']['longitude'] ?? null,
            'logo_url' => $validatedData['logo'] ?? null,

        ];

        
        $school = School::updateOrCreate(
            ['id' => 1], 
            $mappedData  
        );

        return $school->load('images');
    }
    // --- إضافة صورة كـ URL لمعرض المدرسة ---
   public function addSchoolImages(array $data)
    {
        $settings = School::firstOrCreate(['id' => 1]);

        // createMany تأخذ مصفوفة (Array) وتضيفها كلها دفعة واحدة
        return $settings->images()->createMany($data['images']);
    }
    public function updateSchoolImage(SchoolImage $image, array $data)
    {
        $image->update($data);
        return $image;
    }

    // --- حذف صورة (حذف السجل فقط من الداتابيز) ---
    public function deleteSchoolImage(SchoolImage $image): void
    {
        $image->delete();
    }
}
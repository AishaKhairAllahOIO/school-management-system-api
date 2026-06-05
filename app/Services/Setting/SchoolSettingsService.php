<?php 
namespace App\Services\Setting;
use App\Models\School;
use Exception;

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
            'default_language' => $validatedData['defaultLanguage'],
            'timezone' => $validatedData['timezone'],
            'date_format' => $validatedData['dateFormat'],
            'currency' => $validatedData['currency'],
            'working_days' => $validatedData['workingDays'],
            'opening_time' => $validatedData['openingTime'],
            'closing_time' => $validatedData['closingTime'],
            'academic_year' => $validatedData['academicYear'],
        ];

        
        $school = School::updateOrCreate(
            ['id' => 1], 
            $mappedData  
        );

        return $school->load('images');
    }
}
<?php
namespace App\Services\Setting;
use App\Models\School;
use Exception;
use App\Models\SchoolImage;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;


class SchoolSettingsService
{
    public function getSettings()
    {

        $setting = School::with('images')->first();
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

                if ($logoPath && !str_starts_with($logoPath, 'http') && Storage::disk('public')->exists($logoPath)) {
                    Storage::disk('public')->delete($logoPath);
                }

                $logoPath = $validatedData['logo']->store('school_logos', 'public');
            }

            $mappedData = [
                'school_name' => $validatedData['schoolName'] ?? null,
                'short_name' => $validatedData['shortName'] ?? null,
                'description' => $validatedData['description'] ?? null,
                'phone_number' => $validatedData['phoneNumber'] ?? null,
                'emergency_phone_number' => $validatedData['emergencyPhoneNumber'] ?? null,
                'email' => $validatedData['email'] ?? null,
                'website' => $validatedData['website'] ?? null,
                'address' => $validatedData['address'] ?? null,
                'city' => $validatedData['city'] ?? null,
                'country' => $validatedData['country'] ?? null,
                'latitude' => $validatedData['location']['latitude'] ?? null,
                'longitude' => $validatedData['location']['longitude'] ?? null,
                'logo_url' => $logoPath,
            ];

            $updatedSettings = School::updateOrCreate(
                ['id' => 1],
                $mappedData
            );

            return $updatedSettings->load('images');
        });
    }
    public function getAllImages()
    {
        $settings = School::firstOrCreate(['id' => 1]);
        return $settings->images;
    }

    public function getImageById(int $id)
    {
        return SchoolImage::findOrFail($id);
    }


    public function addSchoolImages(array $data)
    {
        $settings = School::first();
        if (!$settings)
            throw new ModelNotFoundException("School settings not found.", 404);
        $imagesData = [];

        foreach ($data['images'] as $imageData) {
            $path = $imageData['file']->store('school_images', 'public');

            $imagesData[] = [
                'url' => $path,
                'name' => $imageData['name'],
            ];
        }

        return $settings->images()->createMany($imagesData);
    }

    public function updateSchoolImage(int $id, array $data): SchoolImage
    {
        $image = SchoolImage::findOrFail($id);
        if (!$image)
            throw new ModelNotFoundException("The specified image does not exist in the gallery.", 404);
        if (isset($data['file'])) {
            if ($image->url && !str_starts_with($image->url, 'http') && Storage::disk('public')->exists($image->url)) {
                Storage::disk('public')->delete($image->url);
            }
            $data['url'] = $data['file']->store('school_images', 'public');
        }

        $image->update([
            'url' => $data['url'] ?? $image->url,
            'name' => $data['name'] ?? $image->name,
        ]);

        return $image->fresh();
    }

    public function deleteSchoolImage(int $id): void
    {
        $image = SchoolImage::findOrFail($id);
        if (!$image)
            throw new ModelNotFoundException("The specified image does not exist in the gallery.", 404);
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
    public function index()
    {
        $school = School::find(1);
        return [
            'schoolName' => $school->school_name ?: "school",
            'shortName' => $school->short_name ?: "sch",
            'website' => $school->website ?: null,
            'logo' => $school->logo_url ? (str_starts_with($school->logo_url, 'http') ? $school->logo_url : asset('storage/' . $school->logo_url)) : null
        ];
    }
}

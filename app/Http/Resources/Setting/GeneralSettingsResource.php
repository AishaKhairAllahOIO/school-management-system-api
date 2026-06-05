<?php

namespace App\Http\Resources\Setting;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GeneralSettingsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
return [
            'id' =>  $this->id,
            'schoolName' => $this->school_name,
            'shortName' => $this->short_name,
            'description' => $this->description ?? '',
            'phoneNumber' => $this->phone_number,
            'emergencyPhoneNumber' => $this->emergency_phone_number ?? '',
            'email' => $this->email,
            'website' => $this->website ?? '',
            'address' => $this->address,
            'city' => $this->city,
            'country' => $this->country,
            
            // دمج الإحداثيات في كائن Location
            'location' => [
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
            ],
            
            'logoUrl' => $this->logo_url,
            'images' => $this->relationLoaded('images') ? $this->images : [],
            
            'defaultLanguage' => $this->default_language,
            'timezone' => $this->timezone,
            'dateFormat' => $this->date_format,
            'currency' => $this->currency,
            
            'workingDays' => $this->working_days,
            'openingTime' => substr($this->opening_time, 0, 5), // يرجع HH:MM فقط
            'closingTime' => substr($this->closing_time, 0, 5),
            'academicYear' => $this->academic_year,
            
            'createdAt' => $this->created_at->toISOString(),
            'updatedAt' => $this->updated_at->toISOString(),
        ];    }
}

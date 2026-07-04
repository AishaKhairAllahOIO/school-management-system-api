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
            'id'                   => (string) $this->id,
            
            'schoolName'           => $this->school_name,
            'shortName'            => $this->short_name,
            'description'          => $this->description,
            
            'phoneNumber'          => $this->phone_number,
            'emergencyPhoneNumber' => $this->emergency_phone_number,
            'email'                => $this->email,
            'website'              => $this->website,
            
            'address'              => $this->address,
            'city'                 => $this->city,
            'country'              => $this->country,
            
            'location' => [
                'latitude'  => $this->latitude ? (float) $this->latitude : null,
                'longitude' => $this->longitude ? (float) $this->longitude : null,
            ],
            
            'logoUrl'              => $this->logo_url,
            'images'               => SchoolImageResource::collection($this->whenLoaded('images')),
            
            'createdAt'            => $this->created_at->toIso8601String(),
            'updatedAt'            => $this->updated_at->toIso8601String(),
        ];
    }
}

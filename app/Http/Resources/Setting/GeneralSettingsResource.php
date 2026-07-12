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
            // 🛡️ حماية الـ id لكي يعود null إذا كان الكائن جديداً
            'id'                   => $this->id ? (string) $this->id : null,
            
            // استخدام ?? أسهل وأنظف بكثير من العمليات الشرطية
            'schoolName'           => $this->school_name ?? null,
            'shortName'            => $this->short_name ?? null,
            'description'          => $this->description ?? null,
            
            'phoneNumber'          => $this->phone_number ?? null,
            'emergencyPhoneNumber' => $this->emergency_phone_number ?? null,
            'email'                => $this->email ?? null,
            'website'              => $this->website ?? null,
            
            'address'              => $this->address ?? null,
            'city'                 => $this->city ?? null,
            'country'              => $this->country ?? null,
            
            'location' => [
                'latitude'  => $this->latitude ? (float) $this->latitude : null,
                'longitude' => $this->longitude ? (float) $this->longitude : null,
            ],
            
            'logoUrl'              => $this->logo_url ? (str_starts_with($this->logo_url, 'http') ? $this->logo_url : asset('storage/' . $this->logo_url)) : null,
            
            'images'               => SchoolImageResource::collection($this->whenLoaded('images', [])),
            
            'createdAt'            => $this->created_at ? $this->created_at->toIso8601String() : null,
            'updatedAt'            => $this->updated_at ? $this->updated_at->toIso8601String() : null,
        ];
    }
}

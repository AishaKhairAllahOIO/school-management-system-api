<?php

namespace App\Http\Resources\Setting;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class GeneralSettingsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk(
            config('filesystems.public_disk', 'public')
        );

        return [
            'id' => $this->id ? (string) $this->id : null,

            'schoolName' => $this->school_name,
            'shortName' => $this->short_name,
            'description' => $this->description,

            'phoneNumber' => $this->phone_number,
            'emergencyPhoneNumber' => $this->emergency_phone_number,
            'email' => $this->email,
            'website' => $this->website,

            'address' => $this->address,
            'city' => $this->city,
            'country' => $this->country,

            'location' => [
                'latitude' => $this->latitude !== null
                    ? (float) $this->latitude
                    : null,

                'longitude' => $this->longitude !== null
                    ? (float) $this->longitude
                    : null,
            ],


            'logoUrl' => $this->logo_url
                ? (
                    str_starts_with(trim($this->logo_url), 'http')
                    ? trim($this->logo_url)
                    : $disk->url(trim($this->logo_url))
                )
                : null,


            'images' => SchoolImageResource::collection(
                $this->whenLoaded('images')
            ),


            'createdAt' => $this->created_at?->toIso8601String(),

            'updatedAt' => $this->updated_at?->toIso8601String(),
        ];
    }
}
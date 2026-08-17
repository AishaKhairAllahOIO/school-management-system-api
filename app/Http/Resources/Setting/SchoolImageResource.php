<?php

namespace App\Http\Resources\Setting;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class SchoolImageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk(
            config('filesystems.public_disk', 'public')
        );
        return [
            'id' => (string) $this->id,

            'url' => $this->url
                ? (
                    str_starts_with($this->url, 'http')
                    ? $this->url
                    : $disk->url($this->url)
                )
                : null,

            'name' => $this->name,
        ];
    }
}
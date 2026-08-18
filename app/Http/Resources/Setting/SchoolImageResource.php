<?php

namespace App\Http\Resources\Setting;

use Illuminate\Http\Request;
use App\Support\FileUrl;
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

            'url' => FileUrl::make(
                $this->url,
                config('filesystems.public_disk')
            ),

            'name' => $this->name,
        ];
    }
}

<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class FileUrl
{
    public static function make(?string $path, ?string $disk = null): ?string
    {
        if (!$path) {
            return null;
        }

        $path = trim($path);

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        $disk ??= config('filesystems.public_disk');

        $storage = Storage::disk($disk);

        if ($disk === 's3') {
            return $storage->temporaryUrl(
                $path,
                now()->addMinutes(30)
            );
        }

        return $storage->url($path);
    }

    public static function endpoint(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        $path = trim($path);

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        $path = preg_replace(
            '/^.*?(users\/|defaults\/|documents\/|guardians\/|staff\/|students\/|imports\/|temp_imports\/)/',
            '$1',
            $path
        );

        return Storage::disk(config('filesystems.public_disk'))->url(
            ltrim($path, '/')
        );
    }
}

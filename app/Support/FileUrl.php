<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class FileUrl
{
    public static function make(
        ?string $path,
        ?string $disk = null
    ): ?string {
        if (!$path) {
            return null;
        }

        $path = trim($path);

        // إذا كانت القيمة رابطاً كاملاً، نعيدها كما هي
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        // إذا لم يحدد الـ disk، استخدم الـ default
        $disk ??= config('filesystems.default');

        $storage = Storage::disk($disk);

        // Railway / Tigris / S3
        if ($disk === 's3') {
            return $storage->temporaryUrl(
                $path,
                now()->addMinutes(30)
            );
        }

        // Local public disk
        return $storage->url($path);
    }
}

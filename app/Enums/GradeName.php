<?php

namespace App\Enums;

enum GradeName: string
{
    case SEVENTH = 'seventh';
    case EIGHTH = 'eighth';
    case NINTH = 'ninth';

    // هذه الدالة تربط كل صف بمرحلته الأكاديمية بشكل صارم ومحمي
    public function stage(): AcademicStageType
    {
        return match($this) {
            self::SEVENTH, self::EIGHTH, self::NINTH => AcademicStageType::Middle,
        };
    }

    // دالة مساعدة لترجمة الاسم للفرونت إند
    public function labelAr(): string
    {
        return match($this) {
            self::SEVENTH => 'الصف السابع',
            self::EIGHTH => 'الصف الثامن',
            self::NINTH => 'الصف التاسع',
        };
    }
}

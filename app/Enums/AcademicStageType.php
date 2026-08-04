<?php

namespace App\Enums;

enum AcademicStageType: string
{
    case Primary   = 'primary';
    case Middle    = 'middle';
    case Secondary = 'secondary';
    public function labelAr(): string
    {
        return match($this) {
            self::Primary      => 'المرحلة الابتدائية',
            self::Middle       => 'المرحلة الإعدادية',
            self::Secondary    => 'المرحلة الثانوية',
        };
    }
}
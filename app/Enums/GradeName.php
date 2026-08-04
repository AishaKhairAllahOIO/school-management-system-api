<?php

namespace App\Enums;

enum GradeName: string
{
  
    case FIRST  = 'first';
    case SECOND = 'second';
    case THIRD  = 'third';
    case FOURTH = 'fourth';
    case FIFTH  = 'fifth';
    case SIXTH  = 'sixth';

    // --- الإعدادية ---
    case SEVENTH = 'seventh';
    case EIGHTH  = 'eighth';
    case NINTH   = 'ninth';

    // --- الثانوية ---
    case TENTH    = 'tenth';
    case ELEVENTH = 'eleventh';
    case TWELFTH  = 'twelfth';

    // هذه الدالة تربط كل صف بمرحلته الأكاديمية بشكل صارم ومحمي
    public function stage(): AcademicStageType
    {
        return match($this) {
            self::FIRST, self::SECOND, self::THIRD,
            self::FOURTH, self::FIFTH, self::SIXTH => AcademicStageType::Primary,

            self::SEVENTH, self::EIGHTH, self::NINTH => AcademicStageType::Middle,

            self::TENTH, self::ELEVENTH, self::TWELFTH => AcademicStageType::Secondary,            
        };
    }

    // دالة مساعدة لترجمة الاسم للفرونت إند
    public function labelAr(): string
    {
        return match($this) {
            self::FIRST  => 'الصف الأول',
            self::SECOND => 'الصف الثاني',
            self::THIRD  => 'الصف الثالث',
            self::FOURTH => 'الصف الرابع',
            self::FIFTH  => 'الصف الخامس',
            self::SIXTH  => 'الصف السادس',

            self::SEVENTH => 'الصف السابع',
            self::EIGHTH  => 'الصف الثامن',
            self::NINTH   => 'الصف التاسع',

            self::TENTH    => 'الصف العاشر',
            self::ELEVENTH => 'الصف الحادي عشر',
            self::TWELFTH  => 'الصف الثاني عشر',
        };
    }
    public static function getGradesByStage(AcademicStageType $stage): array
    {
        return array_values(
            array_filter(
                self::cases(),
                fn (self $grade) => $grade->stage() === $stage
            )
        );
    }
}

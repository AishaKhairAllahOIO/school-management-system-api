<?php

namespace App\Domain\Scheduling\Builders;

use App\Models\AcademicSetting;
use App\Models\TeacherAssignment;
use App\Domain\Scheduling\DTO\GenerationContext;
use App\Domain\Scheduling\Generators\TimeSlotGenerator;
use App\Domain\Scheduling\Services\LessonExpander;
use Exception;

class GenerationContextBuilder
{
    public function __construct(
        private TimeSlotGenerator $timeSlotGenerator,
        private LessonRequirementBuilder $lessonBuilder,
        private LessonExpander $lessonExpander
    ) {
    }

    public function build(int $academicYearId, int $academicTermId): GenerationContext
    {
        $setting = AcademicSetting::first();

        if (!$setting || !$setting->schedule_settings) {
            throw new Exception("Academic settings or schedule settings are not configured. Please configure them before generating a schedule.");
        }

        $slots = $this->timeSlotGenerator->generate($setting->schedule_settings);

        $assignments = TeacherAssignment::with(['gradeSubject'])
            ->where('academic_year_id', $academicYearId)
            ->where('semester_id', $academicTermId)
            ->get();

        if ($assignments->isEmpty()) {
            throw new Exception("No teacher assignments found for the selected year and term.");
        }

        // تعريف classRoomIds مرة واحدة فقط وتحويلها لمصفوفة
        $classRoomIds = $assignments
            ->pluck('class_room_id')
            ->unique()
            ->values()
            ->toArray();

        $requirements = $this->lessonBuilder->build($assignments);

        $lessons = $this->lessonExpander->expand($requirements);

        return new GenerationContext(
            timeSlots: $slots,
            lessons: $lessons,
            classRoomIds: $classRoomIds
        );
    }
}

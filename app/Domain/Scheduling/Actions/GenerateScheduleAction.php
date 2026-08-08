<?php

namespace App\Domain\Scheduling\Actions;

use App\Domain\Scheduling\Builders\GenerationContextBuilder;
use App\Domain\Scheduling\Algorithms\BacktrackingSolver;
use App\Domain\Scheduling\Algorithms\SearchState;
use App\Domain\Scheduling\Persistence\SchedulePersistenceService;
use App\Domain\Scheduling\Constraints\ConstraintFactory;
use Exception;

class GenerateScheduleAction
{
    public function __construct(
        private GenerationContextBuilder $builder,
        private BacktrackingSolver $solver,
        private SchedulePersistenceService $persistence,
        private ConstraintFactory $constraintFactory
    ) {
    }

    public function execute(int $yearId, int $termId)
    {
        $hardConstraints = $this->constraintFactory->makeHard();
        $softConstraints = $this->constraintFactory->makeSoft();
        $context = $this->builder->build($yearId, $termId);
        $state = new SearchState($hardConstraints, $softConstraints);
        
        $timeSlotsCount = count($context->timeSlots);
        $totalAvailableSlots = $timeSlotsCount * count($context->classRoomIds);
        $totalLessons = count($context->lessons);

        // 1. فحص السعة الإجمالية للمدرسة
        if ($totalLessons > $totalAvailableSlots) {
            throw new Exception("Impossible schedule size: Required lessons ($totalLessons) exceed total available slots ($totalAvailableSlots).");
        }

        // 2. فحص السعة الخاصة بكل شعبة (ClassRoom)
        foreach ($context->classRoomIds as $classId) {
            // ملاحظة: تأكد أن المفتاح هو classRoomId وليس class_room_id حسب ما يُرجعه الـ LessonExpander
            $neededByClass = count(array_filter($context->lessons, fn($lesson) => $lesson['classRoomId'] == $classId));

            if ($neededByClass > $timeSlotsCount) {
                throw new Exception("Class $classId requires $neededByClass lessons, but weekly capacity is only $timeSlotsCount.");
            }
        }

        // 3. فحص السعة الخاصة بكل أستاذ (Teacher) - [إضافة ضرورية جداً للـ CSP]
        // يجب التأكد أن مجموع حصص الأستاذ في كل الشعب لا يتجاوز عدد مقاعد الأسبوع
        $teacherIds = array_unique(array_column($context->lessons, 'teacherId')); // تأكد من اسم المفتاح teacherId
        foreach ($teacherIds as $teacherId) {
            if (!$teacherId)
                continue; // تخطي في حال كانت بعض الحصص بدون أستاذ (إن وجد)

            $neededByTeacher = count(array_filter($context->lessons, fn($lesson) => $lesson['teacherId'] == $teacherId));

            if ($neededByTeacher > $timeSlotsCount) {
                throw new Exception("Teacher $teacherId is assigned to $neededByTeacher lessons across all classes, which exceeds the maximum weekly capacity of $timeSlotsCount.");
            }
        }

        // بدء التوليد
        $result = $this->solver->solve(
            $context->lessons,
            $state,
            $context->timeSlots
        );

        if (!$result) {
            throw new Exception("Schedule generation failed: The solver could not find a valid schedule with the current constraints.");
        }

        return $this->persistence->save(
            $result->result(),
            $yearId,
            $termId
        );
    }
}

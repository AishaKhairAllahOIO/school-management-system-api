<?php

namespace App\Domain\Scheduling\Algorithms;

class SearchState
{
    private array $assignments = [];
    private array $teacherBusy = [];
    private array $classBusy = [];
    private array $subjectDailyCount = [];

    // 1. مصفوفة جديدة لتتبع المواد الثقيلة بسرعة (O(1) Complexity)
    private array $heavySubjectDailyCount = [];

    // 2. فصل القيود
    private array $hardConstraints = [];
    private array $softConstraints = [];

    public function __construct(array $hardConstraints = [], array $softConstraints = [])
    {
        $this->hardConstraints = $hardConstraints;
        $this->softConstraints = $softConstraints;
    }

    public function canPlace(array $lesson, array $slot): bool
    {
        foreach($this->hardConstraints as $constraint) {
            if(!$constraint->passes($lesson, $slot, $this)) {
                return false;
            }
        }
        return true;
    }

    // 3. دالة جديدة لحساب نقاط العقوبة (Soft Constraints)
    public function evaluatePenalty(array $lesson, array $slot): int
    {
        $penalty = 0;
        foreach($this->softConstraints as $constraint) {
            $penalty += $constraint->penalty($lesson, $slot, $this);
        }
        return $penalty;
    }

    public function place(array $lesson, array $slot): void
    {
        $key = $slot['day'] . '_' . $slot['periodIndex'];

        $this->teacherBusy[$lesson['teacherId']][$key] = true;
        $this->classBusy[$lesson['classRoomId']][$key] = true;

        $subjectKey = $lesson['classRoomId'] . '_' . $lesson['subjectId'] . '_' . $slot['day'];
        $this->subjectDailyCount[$subjectKey] = ($this->subjectDailyCount[$subjectKey] ?? 0) + 1;

        // 4. تتبع المواد الثقيلة عند إضافتها
        if (isset($lesson['difficulty']) && $lesson['difficulty'] === 'heavy') {
            $heavyKey = $lesson['classRoomId'] . '_' . $slot['day'];
            $this->heavySubjectDailyCount[$heavyKey] = ($this->heavySubjectDailyCount[$heavyKey] ?? 0) + 1;
        }

        $this->assignments[] = [
            'lesson' => $lesson,
            'slot' => $slot
        ];
    }

    public function remove(array $lesson, array $slot): void
    {
        $key = $slot['day'] . '_' . $slot['periodIndex'];

        unset($this->teacherBusy[$lesson['teacherId']][$key]);
        unset($this->classBusy[$lesson['classRoomId']][$key]);

        $subjectKey = $lesson['classRoomId'] . '_' . $lesson['subjectId'] . '_' . $slot['day'];
        if(isset($this->subjectDailyCount[$subjectKey])) {
            $this->subjectDailyCount[$subjectKey]--;
        }

        // 5. إزالة المادة الثقيلة من التتبع عند التراجع (Backtrack)
        if (isset($lesson['difficulty']) && $lesson['difficulty'] === 'heavy') {
            $heavyKey = $lesson['classRoomId'] . '_' . $slot['day'];
            if(isset($this->heavySubjectDailyCount[$heavyKey])) {
                $this->heavySubjectDailyCount[$heavyKey]--;
            }
        }

        array_pop($this->assignments);
    }

    public function result(): array
    {
        return $this->assignments;
    }

    public function teacherBusy($teacher, $day, $period): bool
    {
        return isset($this->teacherBusy[$teacher][$day.'_'.$period]);
    }

    public function classBusy($class, $day, $period): bool
    {
        return isset($this->classBusy[$class][$day.'_'.$period]);
    }

    public function subjectCountInDay($classRoomId, $subjectId, $day): int
    {
        $key = $classRoomId . '_' . $subjectId . '_' . $day;
        return $this->subjectDailyCount[$key] ?? 0;
    }

    // 6. الدالة المطلوبة لقيد HeavySubjectLimitConstraint
    public function heavySubjectCountInDay($classRoomId, $day): int
    {
        $key = $classRoomId . '_' . $day;
        return $this->heavySubjectDailyCount[$key] ?? 0;
    }
}

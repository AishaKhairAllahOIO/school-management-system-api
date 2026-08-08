<?php

namespace App\Domain\Scheduling\Algorithms;

class BacktrackingSolver
{
    public function __construct(
        private VariableSelector $selector,
        private CandidateGenerator $generator,
        private ForwardChecker $checker
    ) {
    }

    public function solve(array $lessons, SearchState $state, array $slots)
    {
        echo "Remaining lessons: " . count($lessons) . PHP_EOL;

        if (empty($lessons)) {
            return $state;
        }

        $lesson = $this->selector->select(
            $lessons,
            $slots,
            $state,
            $this->generator
        );

        $remaining = array_filter(
            $lessons,
            fn($item) => $item['id'] != $lesson['id']
        );

        // هذه المقاعد اجتازت الـ Hard Constraints
        $candidates = $this->generator->generate(
            $lesson,
            $slots,
            $state
        );

        // 🌟 التعديل السحري: ترتيب المقاعد بناءً على الـ Soft Constraints
        // المقعد صاحب العقوبة الأقل سيكون في بداية المصفوفة لنجربه أولاً
        usort($candidates, function($slotA, $slotB) use ($lesson, $state) {
            $penaltyA = $state->evaluatePenalty($lesson, $slotA);
            $penaltyB = $state->evaluatePenalty($lesson, $slotB);

            return $penaltyA <=> $penaltyB;
        });

        foreach ($candidates as $candidate) {

            $state->place($lesson, $candidate);

            if (
                $this->checker->check(
                    $remaining,
                    $slots,
                    $state,
                    $this->generator
                )
            ) {
                $result = $this->solve($remaining, $state, $slots);

                if ($result) {
                    return $result;
                }
            }

            $state->remove($lesson, $candidate);
        }

        return null;
    }
}

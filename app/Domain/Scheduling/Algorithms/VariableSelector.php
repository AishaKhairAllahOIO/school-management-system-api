<?php

namespace App\Domain\Scheduling\Algorithms;

class VariableSelector
{
    public function select(
        array $lessons,
        array $slots,
        SearchState $state,
        CandidateGenerator $generator
    ): array {
        // 1. حساب العبء المتبقي لكل أستاذ لمعرفة "الأكثر انشغالاً" في الحصص المتبقية
        $teacherLoads = [];
        foreach ($lessons as $l) {
            $tId = $l['teacherId'];
            $teacherLoads[$tId] = ($teacherLoads[$tId] ?? 0) + 1;
        }

        $selected = null;
        $bestScore = PHP_INT_MAX;

        foreach ($lessons as $lesson) {
            // حساب المقاعد المتاحة (MRV)
            $options = count(
                $generator->generate($lesson, $slots, $state)
            );

            // Fail-First: إذا كانت الحصة مستحيلة الجدولة (لا يوجد مقاعد)، أعدها فوراً ليقوم المحرك بالتراجع!
            if ($options === 0) {
                return $lesson;
            }

            // 2. وزن الصعوبة
            $difficultyWeight = match($lesson['difficulty'] ?? 'light') {
                'heavy' => 20,
                'medium' => 10,
                default => 0
            };

            // 3. وزن عبء الأستاذ
            $teacherLoad = $teacherLoads[$lesson['teacherId']] ?? 0;

            // 4. المعادلة الدقيقة للتقييم:
            // - نضرب الخيارات بـ 1000 لتكون هي العامل الحاسم دائماً (أقل خيارات = أولوية قصوى)
            // - نطرح عبء الأستاذ والصعوبة لكسر التعادل (لتفضيل الأستاذ المزدحم والمادة الصعبة)
            $score = ($options * 1000) - ($teacherLoad * 10) - $difficultyWeight;

            if ($score < $bestScore) {
                $bestScore = $score;
                $selected = $lesson;
            }
        }

        return $selected;
    }
}

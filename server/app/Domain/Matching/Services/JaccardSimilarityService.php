<?php

namespace App\Domain\Matching\Services;

class JaccardSimilarityService
{
    public function calculate(array $setA, array $setB): float
    {
        if (empty($setA) || empty($setB)) {
            return 0.0;
        }

        $intersection = count(array_intersect($setA, $setB));
        $union = count(array_unique(array_merge($setA, $setB)));

        return $union > 0 ? ($intersection / $union) * 100.0 : 0.0;
    }
}

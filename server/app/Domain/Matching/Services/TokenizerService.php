<?php

declare(strict_types=1);

namespace App\Domain\Matching\Services;

class TokenizerService
{
    /**
     * Pure function to extract alphanumeric/multilingual tokens from text.
     *
     * @param string $text
     * @return array<int, string>
     */
    public function tokenize(string $text): array
    {
        $clean = mb_strtolower(preg_replace('/[^\p{L}\p{N}\s]/u', '', $text) ?? '', 'UTF-8');
        $tokens = preg_split('/\s+/u', $clean, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $stopWords = ['the', 'a', 'an', 'in', 'on', 'at', 'for', 'with', 'of', 'and', 'or', 'is', 'my'];
        return array_values(array_diff($tokens, $stopWords));
    }
}


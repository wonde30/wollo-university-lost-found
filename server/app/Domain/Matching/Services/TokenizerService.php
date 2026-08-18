<?php

namespace App\Domain\Matching\Services;

class TokenizerService
{
    public function tokenize(string $text): array
    {
        $clean = strtolower(preg_replace('/[^a-zA-Z0-9\s]/', '', $text));
        $tokens = preg_split('/\s+/', $clean, -1, PREG_SPLIT_NO_EMPTY);
        $stopWords = ['the', 'a', 'an', 'in', 'on', 'at', 'for', 'with', 'of', 'and', 'or', 'is', 'my'];
        return array_values(array_diff($tokens, $stopWords));
    }
}

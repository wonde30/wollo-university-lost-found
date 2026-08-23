<?php

namespace App\Jobs;

use App\Models\Item;
use App\Models\MatchSuggestion;
use App\Support\Enums\ItemType;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Str;

/**
 * Generate match suggestions for a newly reported item.
 *
 * Strategy (lightweight text + category matching):
 *   - If the new item is LOST  → search FOUND items in same category
 *   - If the new item is FOUND → search LOST  items in same category
 *   Score = category_match(0.4) + text_similarity(0.6)
 *   Only suggestions with score >= 0.3 are persisted & notified.
 */
class GenerateMatchSuggestions implements ShouldQueue
{
    use Queueable;

    private const SCORE_THRESHOLD = 0.30;
    private const ALGO_VERSION    = 'v1-text-category';

    public function __construct(public readonly Item $item)
    {}

    public function handle(): void
    {
        $item = $this->item;

        // Determine which pool to search
        $counterType   = $item->type === ItemType::LOST ? ItemType::FOUND : ItemType::LOST;
        $counterStatus = $item->type === ItemType::LOST ? 'found_unclaimed' : 'lost';

        $candidates = Item::where('type', $counterType)
            ->where('status', $counterStatus)
            ->where('is_deleted', false)
            ->where('id', '!=', $item->id)
            ->where('category_id', $item->category_id) // pre-filter for speed
            ->get();

        foreach ($candidates as $candidate) {
            $score = $this->computeScore($item, $candidate);

            if ($score < self::SCORE_THRESHOLD) {
                continue;
            }

            // Determine found/lost ordering
            [$foundId, $lostId] = $item->type === ItemType::FOUND
                ? [$item->id, $candidate->id]
                : [$candidate->id, $item->id];

            // Skip if this pair already has a pending/notified suggestion
            $exists = MatchSuggestion::where('found_item_id', $foundId)
                ->where('lost_item_id', $lostId)
                ->whereIn('status', ['pending', 'notified'])
                ->exists();

            if ($exists) {
                continue;
            }

            $categoryScore = 1.0; // same category guaranteed by the query
            $textScore     = $this->textSimilarity(
                ($item->title ?? '') . ' ' . ($item->description ?? ''),
                ($candidate->title ?? '') . ' ' . ($candidate->description ?? '')
            );

            $match = MatchSuggestion::create([
                'found_item_id'    => $foundId,
                'lost_item_id'     => $lostId,
                'score'            => $score,
                'category_score'   => $categoryScore,
                'text_score'       => $textScore,
                'location_score'   => 0,
                'algorithm_version'=> self::ALGO_VERSION,
                'status'           => 'pending',
            ]);

            // Dispatch notification asynchronously
            SendMatchNotification::dispatch($match);
        }
    }

    private function computeScore(Item $a, Item $b): float
    {
        $categoryScore = ($a->category_id === $b->category_id) ? 1.0 : 0.0;
        $textScore     = $this->textSimilarity(
            ($a->title ?? '') . ' ' . ($a->description ?? ''),
            ($b->title ?? '') . ' ' . ($b->description ?? '')
        );

        return round(($categoryScore * 0.4) + ($textScore * 0.6), 4);
    }

    /**
     * Simple bigram Dice coefficient for text similarity (0.0 – 1.0).
     */
    private function textSimilarity(string $a, string $b): float
    {
        $a = Str::lower(trim($a));
        $b = Str::lower(trim($b));

        if ($a === '' || $b === '') {
            return 0.0;
        }

        if ($a === $b) {
            return 1.0;
        }

        $bigramsA = $this->bigrams($a);
        $bigramsB = $this->bigrams($b);

        if (empty($bigramsA) || empty($bigramsB)) {
            return 0.0;
        }

        $intersection = array_intersect($bigramsA, $bigramsB);

        return (2 * count($intersection)) / (count($bigramsA) + count($bigramsB));
    }

    private function bigrams(string $str): array
    {
        $bigrams = [];
        $words   = preg_split('/\s+/', $str, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($words as $word) {
            $len = mb_strlen($word);
            for ($i = 0; $i < $len - 1; $i++) {
                $bigrams[] = mb_substr($word, $i, 2);
            }
        }
        return $bigrams;
    }
}

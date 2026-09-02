<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Item;
use App\Models\MatchSuggestion;
use App\Models\SystemSetting;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Str;

/**
 * FR-50: Generate match suggestions for a newly reported item.
 *
 * Scoring algorithm:
 *   - Category must match exactly (0 if not, 50 if yes)
 *   - Jaccard similarity of tokenised title+description × 50 (max 50 pts)
 *   - Location bonus: same campus +5 pts
 *   - Total score stored as DECIMAL(5,2), range 0–100
 *   - Score >= 35.00 triggers notification to lost-item reporter
 */
class GenerateMatchSuggestions implements ShouldQueue
{
    use Queueable;

    private const SCORE_THRESHOLD  = 35.00;
    private const ALGO_VERSION     = 'v1.0-fr50-jaccard';

    public function __construct(public readonly Item $item)
    {}

    public function handle(): void
    {
        $item = $this->item;

        // Determine which pool to search
        $counterType   = $item->type === 'lost' ? 'found' : 'lost';
        $counterStatus = $item->type === 'lost' ? 'found_unclaimed' : 'lost';

        // Pre-filter by category (category must match exactly per FR-50)
        $candidates = Item::where('type', $counterType)
            ->where('status', $counterStatus)
            ->where('is_deleted', false)
            ->where('id', '!=', $item->id)
            ->where('category_id', $item->category_id)
            ->get();

        foreach ($candidates as $candidate) {
            // Determine found/lost ordering
            [$foundItem, $lostItem] = $item->type === 'found'
                ? [$item, $candidate]
                : [$candidate, $item];

            // Skip if this pair already has a pending/notified suggestion
            $exists = MatchSuggestion::where('found_item_id', $foundItem->id)
                ->where('lost_item_id', $lostItem->id)
                ->whereIn('status', ['pending', 'notified'])
                ->exists();

            if ($exists) {
                continue;
            }

            // FR-50: Category score — 50 pts (guaranteed by query pre-filter)
            $categoryScore = 50.00;

            // FR-50: Jaccard similarity of tokenised title+description × 50 (max 50 pts)
            $tokensA   = $this->tokenize(($item->title ?? '') . ' ' . ($item->description ?? ''));
            $tokensB   = $this->tokenize(($candidate->title ?? '') . ' ' . ($candidate->description ?? ''));
            $jaccardSim = $this->jaccardSimilarity($tokensA, $tokensB);
            $textScore  = round($jaccardSim * 50.00, 2);

            // FR-50: Location bonus — same campus +5 pts
            $locationScore = ($item->campus_id === $candidate->campus_id) ? 5.00 : 0.00;

            // Total score, capped at 100
            $totalScore = min(100.00, $categoryScore + $textScore + $locationScore);

            // FR-50: Score >= threshold triggers notification
            $threshold = (float) SystemSetting::get('match_score_threshold', self::SCORE_THRESHOLD);
            if ($totalScore < $threshold) {
                continue;
            }

            $match = MatchSuggestion::create([
                'found_item_id'     => $foundItem->id,
                'lost_item_id'      => $lostItem->id,
                'score'             => $totalScore,
                'category_score'    => $categoryScore,
                'text_score'        => $textScore,
                'location_score'    => $locationScore,
                'algorithm_version' => self::ALGO_VERSION,
                'status'            => 'pending',
            ]);

            // Dispatch notification to the lost-item reporter
            SendMatchNotification::dispatch($match);
        }
    }

    /**
     * Tokenize text into lowercase word tokens.
     */
    private function tokenize(string $text): array
    {
        $text = Str::lower(trim($text));
        return array_unique(preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY));
    }

    /**
     * Jaccard similarity coefficient: |A ∩ B| / |A ∪ B|
     */
    private function jaccardSimilarity(array $a, array $b): float
    {
        if (empty($a) || empty($b)) {
            return 0.0;
        }

        $intersection = count(array_intersect($a, $b));
        $union        = count(array_unique(array_merge($a, $b)));

        return $union > 0 ? $intersection / $union : 0.0;
    }
}

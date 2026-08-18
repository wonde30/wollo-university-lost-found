<?php

namespace App\Domain\Matching\Services;

use App\Models\Item;
use App\Models\MatchSuggestion;
use App\Support\Enums\ItemType;
use App\Support\Enums\ItemStatus;

class ItemMatchingService
{
    public function __construct(
        protected TokenizerService $tokenizer,
        protected JaccardSimilarityService $jaccard
    ) {}

    public function findMatchesFor(Item $item): array
    {
        $targetType = $item->type === ItemType::FOUND ? ItemType::LOST : ItemType::FOUND;
        $activeStatus = $targetType === ItemType::FOUND ? ItemStatus::FOUND_UNCLAIMED : ItemStatus::LOST;

        $candidates = Item::where('type', $targetType)
            ->where('status', $activeStatus)
            ->where('id', '!=', $item->id)
            ->get();

        $suggestions = [];

        foreach ($candidates as $candidate) {
            $lostItem = $item->type === ItemType::LOST ? $item : $candidate;
            $foundItem = $item->type === ItemType::FOUND ? $item : $candidate;

            // Category match check (50 pts if exact match, 0 if not - FR-50)
            $categoryScore = ($lostItem->category_id === $foundItem->category_id) ? 50.00 : 0.00;
            if ($categoryScore === 0.00) {
                continue;
            }

            // Jaccard similarity of tokenised title + description (max 50 pts - FR-50)
            $tokensA = $this->tokenizer->tokenize("{$lostItem->title} {$lostItem->description}");
            $tokensB = $this->tokenizer->tokenize("{$foundItem->title} {$foundItem->description}");
            $textSim = $this->jaccard->calculate($tokensA, $tokensB);
            $textScore = round($textSim * 50.00, 2);

            // Location bonus (+5 pts if same campus - FR-50)
            $locationScore = ($lostItem->campus_id === $foundItem->campus_id) ? 5.00 : 0.00;

            $totalScore = min(100.00, $categoryScore + $textScore + $locationScore);

            // Threshold filter (Score >= 35.00 triggers suggestion - FR-50)
            if ($totalScore >= 35.00) {
                $suggestion = MatchSuggestion::updateOrCreate(
                    [
                        'found_item_id' => $foundItem->id,
                        'lost_item_id' => $lostItem->id,
                    ],
                    [
                        'score' => $totalScore,
                        'category_score' => $categoryScore,
                        'text_score' => $textScore,
                        'location_score' => $locationScore,
                        'algorithm_version' => 'v1.0',
                        'status' => 'pending',
                    ]
                );

                $suggestions[] = $suggestion;
            }
        }

        return $suggestions;
    }
}

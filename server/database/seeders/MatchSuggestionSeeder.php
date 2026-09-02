<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Item;
use App\Models\MatchSuggestion;
use App\Models\User;
use Illuminate\Database\Seeder;

class MatchSuggestionSeeder extends Seeder
{
    public function run(): void
    {
        $staff = User::where('email', 'security.dessie@wu.edu.et')->first() ?? User::first();

        $f01 = Item::where('reference_code', 'WU-F000001')->first(); // HP Laptop
        $l01 = Item::where('reference_code', 'WU-L000001')->first();

        $f03 = Item::where('reference_code', 'WU-F000003')->first(); // Samsung Phone
        $l03 = Item::where('reference_code', 'WU-L000003')->first();

        $f04 = Item::where('reference_code', 'WU-F000004')->first(); // Wallet
        $l04 = Item::where('reference_code', 'WU-L000004')->first();

        $f06 = Item::where('reference_code', 'WU-F000006')->first(); // iPad KIoT
        $l06 = Item::where('reference_code', 'WU-L000006')->first();

        $f13 = Item::where('reference_code', 'WU-F000013')->first(); // Umbrella
        $l08 = Item::where('reference_code', 'WU-L000008')->first(); // Jacket

        $f17 = Item::where('reference_code', 'WU-F000017')->first(); // ThinkPad
        $l16 = Item::where('reference_code', 'WU-L000016')->first();

        $f20 = Item::where('reference_code', 'WU-F000020')->first(); // Medical book
        $l19 = Item::where('reference_code', 'WU-L000019')->first();

        $matches = [
            // ThinkPad Laptop (High Match 94.5%)
            [
                'found' => $f17,
                'lost' => $l16,
                'score' => 94.50,
                'category_score' => 50.00,
                'text_score' => 34.50,
                'location_score' => 10.00,
                'algorithm_version' => 'v1.0',
                'status' => 'pending',
                'notified_at' => now()->subHours(2),
            ],
            // HP Laptop (High Match 91.0%)
            [
                'found' => $f01,
                'lost' => $l01,
                'score' => 91.00,
                'category_score' => 50.00,
                'text_score' => 31.00,
                'location_score' => 10.00,
                'algorithm_version' => 'v1.0',
                'status' => 'pending',
                'notified_at' => now()->subDays(2),
            ],
            // Samsung Phone (88.5%)
            [
                'found' => $f03,
                'lost' => $l03,
                'score' => 88.50,
                'category_score' => 50.00,
                'text_score' => 28.50,
                'location_score' => 10.00,
                'algorithm_version' => 'v1.0',
                'status' => 'reviewed',
                'reviewed_at' => now()->subHours(4),
                'reviewed_by' => $staff?->id,
                'notified_at' => now()->subDays(1),
            ],
            // Medical Microbiology Book (89.0%)
            [
                'found' => $f20,
                'lost' => $l19,
                'score' => 89.00,
                'category_score' => 50.00,
                'text_score' => 29.00,
                'location_score' => 10.00,
                'algorithm_version' => 'v1.0',
                'status' => 'pending',
                'notified_at' => now()->subDays(2),
            ],
            // Leather Wallet (78.0%)
            [
                'found' => $f04,
                'lost' => $l04,
                'score' => 78.00,
                'category_score' => 45.00,
                'text_score' => 23.00,
                'location_score' => 10.00,
                'algorithm_version' => 'v1.0',
                'status' => 'pending',
                'notified_at' => now()->subDays(3),
            ],
            // Apple iPad Air (96.0% - Confirmed)
            [
                'found' => $f06,
                'lost' => $l06,
                'score' => 96.00,
                'category_score' => 50.00,
                'text_score' => 36.00,
                'location_score' => 10.00,
                'algorithm_version' => 'v1.0',
                'status' => 'confirmed',
                'reviewed_at' => now()->subHours(2),
                'reviewed_by' => $staff?->id,
                'notified_at' => now()->subDays(1),
            ],
            // Umbrella vs Jacket (Low Score 45.0% - Dismissed)
            [
                'found' => $f13,
                'lost' => $l08,
                'score' => 45.00,
                'category_score' => 20.00,
                'text_score' => 15.00,
                'location_score' => 10.00,
                'algorithm_version' => 'v1.0',
                'status' => 'dismissed',
                'reviewed_at' => now()->subDays(5),
                'reviewed_by' => $staff?->id,
                'notified_at' => null,
            ],
        ];

        foreach ($matches as $m) {
            if ($m['found'] && $m['lost']) {
                MatchSuggestion::updateOrCreate(
                    [
                        'found_item_id' => $m['found']->id,
                        'lost_item_id' => $m['lost']->id,
                    ],
                    [
                        'score' => $m['score'],
                        'category_score' => $m['category_score'],
                        'text_score' => $m['text_score'],
                        'location_score' => $m['location_score'],
                        'algorithm_version' => $m['algorithm_version'],
                        'status' => $m['status'],
                        'notified_at' => $m['notified_at'] ?? null,
                        'reviewed_at' => $m['reviewed_at'] ?? null,
                        'reviewed_by' => $m['reviewed_by'] ?? null,
                        'created_at' => now()->subDays(2),
                    ]
                );
            }
        }
    }
}

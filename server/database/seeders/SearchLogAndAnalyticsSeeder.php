<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Item;
use App\Models\ItemView;
use App\Models\SearchLog;
use App\Models\User;
use Illuminate\Database\Seeder;

class SearchLogAndAnalyticsSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $items = Item::all();

        // 1. Search logs (Mix of successful queries and zero-result queries for FR-52 analytics)
        $searchQueries = [
            ['query' => 'hp laptop pavilion', 'results_count' => 2, 'ip' => '10.20.4.55'],
            ['query' => 'student id card', 'results_count' => 3, 'ip' => '10.20.4.56'],
            ['query' => 'calculus textbook', 'results_count' => 1, 'ip' => '10.20.4.57'],
            ['query' => 'samsung phone galaxy', 'results_count' => 2, 'ip' => '10.20.4.58'],
            ['query' => 'leather wallet', 'results_count' => 2, 'ip' => '10.20.4.59'],
            ['query' => 'casio calculator fx-991ex', 'results_count' => 1, 'ip' => '10.20.4.60'],
            ['query' => 'keys lanyard', 'results_count' => 1, 'ip' => '10.20.4.61'],
            ['query' => 'thinkpad lenovo', 'results_count' => 2, 'ip' => '10.20.4.62'],
            ['query' => 'sony headphones', 'results_count' => 1, 'ip' => '10.20.4.63'],
            ['query' => 'microbiology murray book', 'results_count' => 2, 'ip' => '10.20.4.64'],
            ['query' => 'ipad air apple', 'results_count' => 2, 'ip' => '10.20.4.65'],
            ['query' => 'ray-ban sunglasses', 'results_count' => 1, 'ip' => '10.20.4.66'],
            // Zero-result queries (Search fail rate metrics)
            ['query' => 'purple umbrella', 'results_count' => 0, 'ip' => '10.20.4.67'],
            ['query' => 'gold diamond necklace', 'results_count' => 0, 'ip' => '10.20.4.68'],
            ['query' => 'canon eos camera 80d', 'results_count' => 0, 'ip' => '10.20.4.69'],
            ['query' => 'dell alienware charger 240w', 'results_count' => 0, 'ip' => '10.20.4.70'],
        ];

        foreach ($searchQueries as $sq) {
            SearchLog::create([
                'query' => $sq['query'],
                'results_count' => $sq['results_count'],
                'ip_address' => $sq['ip'],
                'created_at' => now()->subHours(rand(1, 48)),
            ]);
        }

        // 2. Item Views for popular item analytics
        foreach ($items as $item) {
            $viewCount = rand(5, 25);
            for ($i = 0; $i < $viewCount; $i++) {
                $randomUser = $users->random();
                ItemView::create([
                    'item_id' => $item->id,
                    'user_id' => rand(0, 1) ? $randomUser->id : null,
                    'ip_address' => '10.20.4.' . rand(10, 99),
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'viewed_at' => now()->subHours(rand(1, 72)),
                    'created_at' => now()->subHours(rand(1, 72)),
                ]);
            }
        }
    }
}

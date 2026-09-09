<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Campus;
use App\Models\Category;
use App\Models\Item;
use App\Models\Location;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::pluck('id')->all();
        if (empty($users)) {
            return;
        }

        $campuses = Campus::where('is_active', true)->pluck('id')->all();
        $categories = Category::where('is_active', true)->pluck('id')->all();

        if (empty($campuses) || empty($categories)) {
            return;
        }

        $sampleItems = [
            'HP Pavilion 15 Core i7' => ['brand' => 'HP', 'color' => 'Silver', 'val' => 38000],
            'Samsung Galaxy S23 Ultra' => ['brand' => 'Samsung', 'color' => 'Phantom Black', 'val' => 45000],
            'iPhone 13 128GB' => ['brand' => 'Apple', 'color' => 'Midnight Blue', 'val' => 42000],
            'Dell Latitude 5420 Laptop' => ['brand' => 'Dell', 'color' => 'Gray', 'val' => 32000],
            'Student ID Card - Engineering' => ['brand' => null, 'color' => 'White/Blue', 'val' => 200],
            'Ethiopian National ID Card' => ['brand' => null, 'color' => 'Blue', 'val' => 500],
            'Scientific Calculator FX-991EX' => ['brand' => 'Casio', 'color' => 'Black', 'val' => 1800],
            'Leather Bi-fold Wallet' => ['brand' => 'Gucci replica', 'color' => 'Brown', 'val' => 950],
            'Wireless Noise Cancelling Earbuds' => ['brand' => 'Sony', 'color' => 'White', 'val' => 4500],
            'Mechanical Engineering Handbook' => ['brand' => null, 'color' => 'Green', 'val' => 600],
            'Waterproof Nylon Backpack' => ['brand' => 'Adidas', 'color' => 'Black', 'val' => 2200],
            'Set of 4 Dormitory Keys' => ['brand' => null, 'color' => 'Silver', 'val' => 350],
            'Digital Sports Watch' => ['brand' => 'G-Shock', 'color' => 'Black', 'val' => 3200],
            'Prescription Reading Glasses' => ['brand' => 'Ray-Ban', 'color' => 'Tortoise', 'val' => 2800],
            'Heavy Duty Golf Umbrella' => ['brand' => null, 'color' => 'Navy Blue', 'val' => 800],
            'External SSD 1TB' => ['brand' => 'SanDisk', 'color' => 'Orange/Black', 'val' => 5500],
            'Denim Winter Jacket' => ['brand' => 'Zara', 'color' => 'Dark Denim', 'val' => 2400],
            'Wireless Power Bank 20000mAh' => ['brand' => 'Anker', 'color' => 'Black', 'val' => 2100],
        ];

        $now = Carbon::now();

        for ($i = 1; $i <= 200; $i++) {
            // Distribute created_at evenly across the last 365 days
            $daysAgo = rand(1, 360);
            $hoursAgo = rand(0, 23);
            $minutesAgo = rand(0, 59);
            $createdAt = $now->copy()->subDays($daysAgo)->subHours($hoursAgo)->subMinutes($minutesAgo);

            $campusId = $campuses[array_rand($campuses)];
            $categoryId = $categories[array_rand($categories)];
            $reporterId = $users[array_rand($users)];

            // Match location to campus
            $locationId = Location::where('campus_id', $campusId)->inRandomOrder()->value('id');

            $type = (rand(1, 100) <= 45) ? 'lost' : 'found';
            $titleKey = array_rand($sampleItems);
            $itemInfo = $sampleItems[$titleKey];

            // Assign status based on item type and age
            if ($type === 'lost') {
                $statusRoll = rand(1, 100);
                if ($statusRoll <= 50) {
                    $status = 'reported';
                } elseif ($statusRoll <= 75) {
                    $status = 'claimed';
                } else {
                    $status = 'returned';
                }
                $heldAt = null;
            } else {
                $statusRoll = rand(1, 100);
                if ($statusRoll <= 45) {
                    $status = 'in_storage';
                    $heldAt = 'security_office';
                } elseif ($statusRoll <= 65) {
                    $status = 'reported';
                    $heldAt = 'finder';
                } elseif ($statusRoll <= 80) {
                    $status = 'claimed';
                    $heldAt = 'security_office';
                } else {
                    $status = 'returned';
                    $heldAt = 'security_office';
                }
            }

            $refPrefix = $type === 'lost' ? 'L' : 'F';
            $year = $createdAt->format('Y');
            $refCode = sprintf('WU-%s-%s%04d', $year, $refPrefix, $i + 1000);

            Item::create([
                'reference_code' => $refCode,
                'reporter_id'    => $reporterId,
                'campus_id'      => $campusId,
                'type'           => $type,
                'title'          => $titleKey,
                'description'    => "Discovered in or around campus grounds. {$titleKey} in good condition.",
                'category_id'    => $categoryId,
                'location_id'    => $locationId,
                'location_detail'=> 'Campus Central Area / Main Hallway',
                'brand'          => $itemInfo['brand'],
                'color'          => $itemInfo['color'],
                'serial_number'  => $itemInfo['brand'] ? 'SN-' . strtoupper(Str::random(8)) : null,
                'incident_date'  => $createdAt->copy()->subHours(rand(1, 48))->toDateString(),
                'incident_time'  => sprintf('%02d:%02d:00', rand(8, 18), rand(0, 59)),
                'status'         => $status,
                'held_at'        => $heldAt,
                'estimated_value'=> $itemInfo['val'],
                'is_high_value'  => $itemInfo['val'] >= 10000,
                'is_deleted'     => false,
                'created_at'     => $createdAt,
                'updated_at'     => $createdAt->copy()->addHours(rand(1, 12)),
            ]);
        }
    }
}

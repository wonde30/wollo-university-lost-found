<?php

namespace Database\Factories;

use App\Models\Campus;
use App\Models\Category;
use App\Models\Item;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ItemFactory extends Factory
{
    protected $model = Item::class;

    public function definition(): array
    {
        return [
            'reference_code' => 'WU-' . strtoupper(fake()->lexify('????')) . fake()->numerify('####'),
            'reporter_id' => User::factory(),
            'campus_id' => Campus::factory(),
            'category_id' => Category::factory(),
            'location_id' => Location::factory(),
            'type' => 'lost',
            'status' => 'lost',
            'title' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'incident_date' => now()->toDateString(),
            'is_deleted' => false,
        ];
    }
}

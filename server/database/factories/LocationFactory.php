<?php

namespace Database\Factories;

use App\Models\Campus;
use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

class LocationFactory extends Factory
{
    protected $model = Location::class;

    public function definition(): array
    {
        return [
            'campus_id' => Campus::factory(),
            'name' => fake()->unique()->words(2, true) . ' ' . fake()->unique()->numberBetween(100, 999),
            'code' => 'LOC-' . strtoupper(fake()->unique()->lexify('???')) . fake()->unique()->numerify('####'),
            'building' => 'Building ' . fake()->numberBetween(1, 20),
            'zone' => 'Zone ' . fake()->randomElement(['A', 'B', 'C']),
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word() . ' ' . fake()->unique()->numberBetween(100, 999),
            'icon_slug' => 'box',
            'sort_order' => 0,
            'is_active' => true,
        ];
    }
}

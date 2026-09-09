<?php

namespace Database\Factories;

use App\Models\Campus;
use Illuminate\Database\Eloquent\Factories\Factory;

class CampusFactory extends Factory
{
    protected $model = Campus::class;

    public function definition(): array
    {
        return [
            'name' => fake()->city() . ' Campus',
            'short_code' => strtoupper(fake()->unique()->lexify('???')),
            'city' => fake()->city(),
            'region' => 'Amhara',
            'is_active' => true,
        ];
    }
}

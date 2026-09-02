<?php

namespace Database\Factories;

use App\Models\Claim;
use App\Models\Item;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClaimFactory extends Factory
{
    protected $model = Claim::class;

    public function definition(): array
    {
        return [
            'item_id' => Item::factory(),
            'claimant_id' => User::factory(),
            'explanation' => fake()->paragraph(),
            'status' => 'pending',
            'ip_address' => fake()->ipv4(),
        ];
    }
}

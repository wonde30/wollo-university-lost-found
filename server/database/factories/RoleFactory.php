<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Role>
 */
class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition(): array
    {
        $name = fake()->unique()->slug(2);
        return [
            'name' => $name,
            'display_name' => ucwords(str_replace('-', ' ', $name)),
            'display_name_am' => null,
            'description' => fake()->sentence(),
            'description_am' => null,
            'is_system' => false,
            'is_active' => true,
        ];
    }
}

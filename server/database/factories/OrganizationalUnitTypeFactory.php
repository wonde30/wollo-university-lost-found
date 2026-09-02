<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\OrganizationalUnitType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrganizationalUnitType>
 */
class OrganizationalUnitTypeFactory extends Factory
{
    protected $model = OrganizationalUnitType::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);
        return [
            'code' => strtoupper(fake()->unique()->slug(2, '_')),
            'name' => ucwords($name),
            'name_am' => null,
            'description' => fake()->sentence(),
            'is_root' => false,
            'is_active' => true,
        ];
    }
}

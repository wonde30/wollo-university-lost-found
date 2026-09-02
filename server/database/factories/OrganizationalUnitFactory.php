<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Campus;
use App\Models\OrganizationalUnit;
use App\Models\OrganizationalUnitType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrganizationalUnit>
 */
class OrganizationalUnitFactory extends Factory
{
    protected $model = OrganizationalUnit::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);
        return [
            'campus_id' => Campus::first()?->id ?? Campus::factory(),
            'parent_id' => null,
            'type_id' => OrganizationalUnitType::first()?->id ?? OrganizationalUnitType::factory(),
            'name' => ucwords($name),
            'name_am' => null,
            'short_code' => strtoupper(fake()->unique()->bothify('OU-####')),
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}

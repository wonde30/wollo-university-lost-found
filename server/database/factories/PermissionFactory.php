<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Permission;
use App\Models\PermissionGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Permission>
 */
class PermissionFactory extends Factory
{
    protected $model = Permission::class;

    public function definition(): array
    {
        $name = strtoupper(fake()->unique()->slug(2, '_'));
        return [
            'name' => $name,
            'display_name' => ucwords(str_replace('_', ' ', $name)),
            'display_name_am' => null,
            'description' => fake()->sentence(),
            'description_am' => null,
            'category' => fake()->randomElement(['items', 'claims', 'custody', 'admin']),
            'permission_group_id' => PermissionGroup::first()?->id ?? PermissionGroup::factory(),
            'is_system' => false,
            'is_active' => true,
        ];
    }
}

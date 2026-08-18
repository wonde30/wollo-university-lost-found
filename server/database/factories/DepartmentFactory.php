<?php

namespace DatabaseFactories;

use AppModelsDepartment;
use IlluminateDatabaseEloquentFactoriesFactory;

class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'code' => strtoupper(fake()->unique()->bothify('DEP-###')),
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}

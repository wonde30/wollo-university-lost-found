<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuditLog>
 */
class AuditLogFactory extends Factory
{
    protected $model = AuditLog::class;

    public function definition(): array
    {
        return [
            'action' => fake()->randomElement(['item.created', 'item.updated', 'claim.approved', 'user.login']),
            'auditable_type' => null,
            'auditable_id' => null,
            'actor_id' => null,
            'actor_role' => fake()->randomElement(['admin', 'staff', 'student']),
            'old_values' => null,
            'new_values' => ['sample' => 'value'],
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'session_id' => null,
        ];
    }
}

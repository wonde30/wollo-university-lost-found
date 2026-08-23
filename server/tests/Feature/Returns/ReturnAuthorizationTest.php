<?php

declare(strict_types=1);

namespace Tests\Feature\Returns;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReturnAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_cannot_process_returns(): void
    {
        $student = User::factory()->student()->create();
        $this->actingAs($student);

        $response = $this->getJson('/api/v1/returns');

        $response->assertForbidden();
    }
}

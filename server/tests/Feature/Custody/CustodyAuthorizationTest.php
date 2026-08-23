<?php

declare(strict_types=1);

namespace Tests\Feature\Custody;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustodyAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_cannot_access_custody_management(): void
    {
        $student = User::factory()->student()->create();
        $this->actingAs($student);

        $response = $this->getJson('/api/v1/custody');

        $response->assertForbidden();
    }
}

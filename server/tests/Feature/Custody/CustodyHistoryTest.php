<?php

declare(strict_types=1);

namespace Tests\Feature\Custody;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustodyHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_list_custody_events(): void
    {
        $staff = User::factory()->staff()->create();
        $this->actingAs($staff);

        $response = $this->getJson('/api/v1/custody');

        $response->assertOk()
            ->assertJsonStructure(['data', 'meta']);
    }
}

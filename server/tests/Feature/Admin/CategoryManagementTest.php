<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_category(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $response = $this->postJson('/api/v1/admin/categories', [
            'name' => 'Bags & Backpacks',
            'slug' => 'bags-backpacks',
            'name_am' => 'ቦርሳ',
            'description' => 'Backpacks, handbags, purses',
            'icon' => 'backpack',
            'retention_days' => 90,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Bags & Backpacks');

        $this->assertDatabaseHas('categories', [
            'name' => 'Bags & Backpacks',
        ]);
    }

    public function test_admin_can_list_categories(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $response = $this->getJson('/api/v1/admin/categories');

        $response->assertOk()
            ->assertJsonStructure(['data']);
    }
}

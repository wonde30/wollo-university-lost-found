<?php

namespace Tests\Feature\Items;

use App\Models\Campus;
use App\Models\Category;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CreateLostItemTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_report_lost_item(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $campus = Campus::create(['name' => 'Main Campus', 'short_code' => 'MC', 'city' => 'Dessie', 'region' => 'Amhara']);
        $location = Location::create(['campus_id' => $campus->id, 'name' => 'Main Library', 'code' => 'LOC-LIB-01', 'zone' => 'library']);
        $category = Category::create(['name' => 'Laptops & Computers', 'name_am' => 'ላፕቶፕ']);

        $response = $this->postJson('/api/v1/items/lost', [
            'category_id' => $category->id,
            'campus_id' => $campus->id,
            'location_id' => $location->id,
            'title' => 'Lost Silver HP Laptop',
            'description' => 'HP Pavilion 15 inch silver laptop with university stickers on the back cover',
            'incident_date' => now()->format('Y-m-d'),
            'brand' => 'HP',
            'color' => 'Silver',
            'serial_number' => 'CNU1234567',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.type', 'lost')
            ->assertJsonPath('data.status', 'lost');

        $this->assertDatabaseHas('items', [
            'title' => 'Lost Silver HP Laptop',
            'type' => 'lost',
            'status' => 'lost',
            'reporter_id' => $user->id,
        ]);
    }
}

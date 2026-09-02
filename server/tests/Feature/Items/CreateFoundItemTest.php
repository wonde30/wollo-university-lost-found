<?php

namespace Tests\Feature\Items;

use App\Models\Campus;
use App\Models\Category;
use App\Models\Item;
use App\Models\Location;
use App\Models\StorageLocation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CreateFoundItemTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_register_found_item_into_storage(): void
    {
        $user = User::factory()->staff()->create();
        $this->actingAs($user);

        $campus = Campus::firstOrCreate(['short_code' => 'MC'], ['name' => 'Main Campus', 'city' => 'Dessie', 'region' => 'Amhara']);
        $location = Location::firstOrCreate(['code' => 'LOC-CAF-01'], ['campus_id' => $campus->id, 'name' => 'Cafeteria', 'zone' => 'cafeteria']);
        $category = Category::firstOrCreate(['name' => 'IDs & Cards'], ['name_am' => 'መታወቂያ']);
        $storage = StorageLocation::firstOrCreate(['code' => 'SEC-SAFE-A'], ['campus_id' => $campus->id, 'name' => 'Security Safe A']);

        $response = $this->postJson('/api/v1/items/found', [
            'category_id' => $category->id,
            'campus_id' => $campus->id,
            'location_id' => $location->id,
            'storage_location_id' => $storage->id,
            'title' => 'Found Student ID Card',
            'description' => 'Student ID found near counter with green lanyard and photo intact',
            'incident_date' => now()->format('Y-m-d'),
            'held_at' => 'security_office',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.type', 'found')
            ->assertJsonPath('data.status', 'found_unclaimed');

        $this->assertDatabaseHas('items', [
            'title' => 'Found Student ID Card',
            'type' => 'found',
            'status' => 'found_unclaimed',
            'held_at' => 'security_office',
        ]);

        $this->assertDatabaseHas('custody_events', [
            'storage_location_id' => $storage->id,
            'event_type' => 'deposited',
        ]);
    }

    public function test_student_can_report_found_item_kept_with_finder(): void
    {
        $student = User::factory()->student()->create();
        $this->actingAs($student);

        $campus = Campus::firstOrCreate(['short_code' => 'MC'], ['name' => 'Main Campus', 'city' => 'Dessie', 'region' => 'Amhara']);
        $location = Location::firstOrCreate(['code' => 'LOC-LIB-02'], ['campus_id' => $campus->id, 'name' => 'Main Library Study Area', 'zone' => 'library']);
        $category = Category::firstOrCreate(['name' => 'Electronics'], ['name_am' => 'ኤሌክትሮኒክስ']);

        $response = $this->postJson('/api/v1/items/found', [
            'category_id' => $category->id,
            'campus_id' => $campus->id,
            'location_id' => $location->id,
            'title' => 'Found Silver Casio Scientific Calculator',
            'description' => 'Discovered on 2nd floor desk next to window after afternoon study period',
            'incident_date' => now()->format('Y-m-d'),
            'brand' => 'Casio',
            'color' => 'Silver / Black',
            'serial_number' => 'FX-991EX-00234',
            'held_at' => 'with_finder',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.type', 'found')
            ->assertJsonPath('data.status', 'found_unclaimed')
            ->assertJsonPath('data.held_at', 'with_finder');

        $this->assertDatabaseHas('items', [
            'title' => 'Found Silver Casio Scientific Calculator',
            'held_at' => 'with_finder',
            'serial_number' => 'FX-991EX-00234',
        ]);
    }

    public function test_found_item_validation_requires_minimum_length(): void
    {
        $student = User::factory()->student()->create();
        $this->actingAs($student);

        $response = $this->postJson('/api/v1/items/found', [
            'title' => 'Key',
            'description' => 'Found it',
            'category_id' => 99999,
            'incident_date' => now()->addDays(5)->format('Y-m-d'),
            'held_at' => 'invalid_location',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'description', 'category_id', 'incident_date', 'held_at']);
    }

    public function test_found_item_with_photos(): void
    {
        Storage::fake('public');
        $student = User::factory()->student()->create();
        $this->actingAs($student);

        $campus = Campus::firstOrCreate(['short_code' => 'MC'], ['name' => 'Main Campus', 'city' => 'Dessie', 'region' => 'Amhara']);
        $category = Category::firstOrCreate(['name' => 'Accessories'], ['name_am' => 'መለዋወጫ']);

        $photo = UploadedFile::fake()->create('found_watch.jpg', 100, 'image/jpeg');

        $response = $this->postJson('/api/v1/items/found', [
            'category_id' => $category->id,
            'campus_id' => $campus->id,
            'title' => 'Found Analog Wrist Watch',
            'description' => 'Metallic analog wrist watch found near the basketball court bleachers',
            'incident_date' => now()->format('Y-m-d'),
            'photos' => [$photo],
            'held_at' => 'unknown',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('item_photos', [
            'original_name' => 'found_watch.jpg',
        ]);
    }
}

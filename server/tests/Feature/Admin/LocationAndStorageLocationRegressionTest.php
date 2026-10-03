<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Campus;
use App\Models\Location;
use App\Models\OrganizationalUnit;
use App\Models\OrganizationalUnitType;
use App\Models\StorageLocation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationAndStorageLocationRegressionTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Campus $campus1;
    protected Campus $campus2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create([
            'email' => 'admin.audit@wu.edu.et',
        ]);

        $this->campus1 = Campus::factory()->create(['name' => 'Campus Alpha', 'short_code' => 'ALP']);
        $this->campus2 = Campus::factory()->create(['name' => 'Campus Beta', 'short_code' => 'BET']);
    }

    public function test_admin_locations_search_does_not_crash_on_room_number(): void
    {
        Location::create([
            'campus_id' => $this->campus1->id,
            'name' => 'Engineering Hall Room 101',
            'code' => 'LOC-ENG-101',
            'building' => 'Engineering Block',
            'zone' => 'academic',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->getJson('/api/v1/admin/locations?search=101');

        $response->assertOk()
            ->assertJsonPath('data.0.code', 'LOC-ENG-101')
            ->assertJsonPath('data.0.zone', 'academic');
    }

    public function test_storage_locations_search_does_not_crash_on_building_or_shelf_cabinet_code(): void
    {
        StorageLocation::create([
            'campus_id' => $this->campus1->id,
            'name' => 'Vault Safe Box',
            'code' => 'SL-SAFE-1',
            'description' => 'Heavy steel safe for valuables',
            'capacity' => 10,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->getJson('/api/v1/admin/storage-locations?search=safe');

        $response->assertOk()
            ->assertJsonPath('data.0.code', 'SL-SAFE-1');
    }

    public function test_custody_storage_locations_search_does_not_crash(): void
    {
        StorageLocation::create([
            'campus_id' => $this->campus1->id,
            'name' => 'Electronics Locker 4',
            'code' => 'SL-ELEC-4',
            'description' => 'Locker bay for electronics',
            'capacity' => 15,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->getJson('/api/v1/custody/storage-locations?search=electronics');

        $response->assertOk()
            ->assertJsonPath('data.0.code', 'SL-ELEC-4');
    }

    public function test_organizational_unit_rejects_cross_campus_parent(): void
    {
        $type = OrganizationalUnitType::firstOrCreate(
            ['code' => 'COLLEGE'],
            ['name' => 'College', 'is_root' => true, 'is_active' => true]
        );

        $parentUnit = OrganizationalUnit::create([
            'campus_id' => $this->campus1->id,
            'type_id' => $type->id,
            'name' => 'College in Campus Alpha',
            'short_code' => 'COL-ALP',
            'is_active' => true,
        ]);

        $deptType = OrganizationalUnitType::firstOrCreate(
            ['code' => 'DEPARTMENT'],
            ['name' => 'Department', 'is_root' => false, 'is_active' => true]
        );

        // Attempt to create a department on Campus Beta with a parent on Campus Alpha
        $response = $this->actingAs($this->admin)->postJson('/api/v1/admin/organizational-units', [
            'campus_id' => $this->campus2->id,
            'parent_id' => $parentUnit->id,
            'type_id' => $deptType->id,
            'name' => 'Department in Beta with Parent in Alpha',
            'short_code' => 'DEP-BET',
            'is_active' => true,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['parent_id']);
    }

    public function test_organizational_unit_update_rejects_self_as_parent(): void
    {
        $type = OrganizationalUnitType::firstOrCreate(
            ['code' => 'COLLEGE'],
            ['name' => 'College', 'is_root' => true, 'is_active' => true]
        );

        $unit = OrganizationalUnit::create([
            'campus_id' => $this->campus1->id,
            'type_id' => $type->id,
            'name' => 'Test Unit',
            'short_code' => 'TEST-U',
            'is_active' => true,
        ]);

        // Attempt to set unit's parent_id to itself
        $response = $this->actingAs($this->admin)->putJson("/api/v1/admin/organizational-units/{$unit->id}", [
            'parent_id' => $unit->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['parent_id']);
    }
}

<?php

namespace Tests\Feature\Auth;

use App\Models\Campus;
use App\Models\OrganizationalUnit;
use App\Models\OrganizationalUnitType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_with_valid_details(): void
    {
        $campus = Campus::create(['name' => 'Main Campus', 'short_code' => 'MC', 'city' => 'Dessie', 'region' => 'Amhara']);
        $type = OrganizationalUnitType::firstOrCreate(
            ['code' => 'dept'],
            ['name' => 'Department', 'is_root' => true, 'is_active' => true]
        );
        $unit = OrganizationalUnit::create([
            'campus_id'  => $campus->id,
            'type_id'    => $type->id,
            'name'       => 'Department of Computer Science',
            'short_code' => 'CS',
            'is_active'  => true,
        ]);

        $response = $this->postJson('/api/v1/auth/register', [
            'full_name' => 'Abebe Bikila',
            'university_id' => 'WU/12345/14',
            'email' => 'abebe.bikila@wollo.edu.et',
            'password' => 'WolloSecure123!@#',
            'phone' => '+251911223344',
            'organizational_unit_id' => $unit->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'user' => ['id', 'email', 'full_name', 'role'],
                ],
            ]);

        $user = User::where('email', 'abebe.bikila@wollo.edu.et')->first();
        $this->assertNotNull($user);
        $this->assertEquals('WU/12345/14', $user->university_id);
        $this->assertEquals('student', $user->getRoleName());
        $this->assertDatabaseHas('user_organizational_units', [
            'user_id' => $user->id,
            'organizational_unit_id' => $unit->id,
        ]);
    }
}


<?php

declare(strict_types=1);

namespace Tests\Unit\Security;

use App\Http\Resources\Api\V1\ItemResource;
use App\Models\Item;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Policies\ItemPolicy;
use App\Policies\PermissionGroupPolicy;
use App\Policies\PermissionPolicy;
use App\Policies\RolePolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class PolicyAuthorizationUnitTest extends TestCase
{
    use RefreshDatabase;

    public function test_item_policy_change_status_denies_reporter_without_permission(): void
    {
        $policy = new ItemPolicy();

        $studentReporter = new User();
        $studentReporter->id = 42;
        $studentReporter->role_id = 99; // student

        $item = new Item();
        $item->id = 101;
        $item->reporter_id = 42;

        // Reporter should NOT be able to change general item status (must use withdraw endpoint)
        $this->assertFalse($policy->changeStatus($studentReporter, $item));
    }

    public function test_role_policy_requires_admin_or_manage_permissions(): void
    {
        $policy = new RolePolicy();

        $student = new User();
        $student->id = 1;
        $student->role_id = 3;

        $role = new Role();

        $this->assertFalse($policy->viewAny($student));
        $this->assertFalse($policy->create($student));
        $this->assertFalse($policy->update($student, $role));
        $this->assertFalse($policy->delete($student, $role));
    }

    public function test_permission_policy_requires_admin_or_manage_permissions(): void
    {
        $policy = new PermissionPolicy();

        $student = new User();
        $student->id = 1;
        $student->role_id = 3;

        $permission = new Permission();

        $this->assertFalse($policy->viewAny($student));
        $this->assertFalse($policy->create($student));
        $this->assertFalse($policy->update($student, $permission));
        $this->assertFalse($policy->delete($student, $permission));
    }

    public function test_user_policy_forbids_self_role_change_and_self_permission_sync(): void
    {
        $policy = new UserPolicy();

        $user = new User();
        $user->id = 5;

        // Same user ID (target is self)
        $this->assertFalse($policy->updateRole($user, $user));
        $this->assertFalse($policy->syncPermissions($user, $user));
        $this->assertFalse($policy->toggleActive($user, $user));
    }

    public function test_item_resource_masks_reporter_pii_for_unprivileged_viewers(): void
    {
        $reporter = new User([
            'full_name' => 'Abebe Bikila',
            'university_id' => 'WU/12345/14',
            'email' => 'abebe@wollo.edu.et',
            'phone' => '+251911223344',
            'language' => 'en',
            'is_active' => true,
        ]);
        $reporter->id = 10;
        $reporter->role_id = 3;

        $item = new Item([
            'reference_code' => 'WU-TEST-123',
            'reporter_id' => 10,
            'title' => 'Lost Calculus Book',
            'description' => 'Blue cover textbook',
            'type' => 'lost',
            'status' => 'lost',
        ]);
        $item->id = 50;
        $item->setRelation('reporter', $reporter);

        // Viewer 1: Other student (unprivileged)
        $otherStudent = new User();
        $otherStudent->id = 99;
        $otherStudent->role_id = 3;

        $requestUnprivileged = Request::create('/api/v1/items', 'GET');
        $requestUnprivileged->setUserResolver(fn () => $otherStudent);

        $resource = new ItemResource($item);
        $arrayData = $resource->toArray($requestUnprivileged);

        // PII must NOT be leaked
        $this->assertEquals('Abebe Bikila', $arrayData['reporter']['name']);
        $this->assertArrayNotHasKey('email', $arrayData['reporter']);
        $this->assertArrayNotHasKey('phone', $arrayData['reporter']);
        $this->assertArrayNotHasKey('university_id', $arrayData['reporter']);
    }
}

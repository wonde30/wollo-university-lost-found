<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LocationManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_feature_execution(): void
    {
        $this->assertTrue(true);
    }
}

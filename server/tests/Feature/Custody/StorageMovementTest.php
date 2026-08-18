<?php

namespace Tests\Feature\Custody;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StorageMovementTest extends TestCase
{
    use RefreshDatabase;

    public function test_feature_execution(): void
    {
        $this->assertTrue(true);
    }
}

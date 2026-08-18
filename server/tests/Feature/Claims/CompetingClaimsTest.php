<?php

namespace Tests\Feature\Claims;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CompetingClaimsTest extends TestCase
{
    use RefreshDatabase;

    public function test_feature_execution(): void
    {
        $this->assertTrue(true);
    }
}

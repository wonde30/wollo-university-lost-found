<?php

namespace Tests\Feature\Items;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ItemPrivacyTest extends TestCase
{
    use RefreshDatabase;

    public function test_feature_execution(): void
    {
        $this->assertTrue(true);
    }
}

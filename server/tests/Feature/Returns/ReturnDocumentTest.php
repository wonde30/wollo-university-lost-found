<?php

namespace Tests\Feature\Returns;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReturnDocumentTest extends TestCase
{
    use RefreshDatabase;

    public function test_feature_execution(): void
    {
        $this->assertTrue(true);
    }
}

<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserApiTest extends TestCase
{

    /** @test */
    public function always_returns_true(): void
    {
        $this->assertTrue(true);
    }
}

<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTokenTest extends TestCase
{
    use RefreshDatabase;

    public function test_token_can_only_be_used_once(): void
    {
        $user = User::factory()->create();
        $token = $user->createApiToken();

        $this->assertNotNull(User::findUserByToken($token));
        $this->assertNull(User::findUserByToken($token));
    }
}

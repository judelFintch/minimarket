<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserSuspensionTest extends TestCase
{
    use RefreshDatabase;

    public function test_suspended_user_is_redirected_to_login(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'suspended_at' => now(),
        ]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertRedirect('/login')
            ->assertSessionHas('status', 'Compte suspendu.');
    }
}

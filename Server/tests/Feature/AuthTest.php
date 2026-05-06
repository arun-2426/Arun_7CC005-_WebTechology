<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Auth happy-path + rejection cases. Uses a fresh in-memory SQLite DB
 * per test (see phpunit.xml).
 */
class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_receives_token(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name'                  => 'Arun K',
            'email'                 => 'arun@example.com',
            'password'              => 'StrongPass1',
            'password_confirmation' => 'StrongPass1',
        ]);

        $response->assertCreated()
                 ->assertJsonStructure(['user' => ['id', 'name', 'email'], 'token']);

        $this->assertDatabaseHas('users', ['email' => 'arun@example.com']);
    }

    public function test_register_rejects_weak_password(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name'                  => 'Test',
            'email'                 => 'weak@example.com',
            'password'              => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('password');
    }

    public function test_login_returns_token_with_correct_credentials(): void
    {
        User::factory()->create([
            'email'    => 'arun@example.com',
            'password' => 'StrongPass1',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email'    => 'arun@example.com',
            'password' => 'StrongPass1',
        ]);

        $response->assertOk()->assertJsonStructure(['user', 'token']);
    }

    public function test_login_rejects_wrong_password(): void
    {
        User::factory()->create([
            'email'    => 'arun@example.com',
            'password' => 'StrongPass1',
        ]);

        $this->postJson('/api/auth/login', [
            'email'    => 'arun@example.com',
            'password' => 'WrongOne1',
        ])->assertStatus(422);
    }

    public function test_me_endpoint_requires_auth(): void
    {
        $this->getJson('/api/auth/me')->assertStatus(401);
    }

    public function test_me_endpoint_returns_user_when_authed(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum')
             ->getJson('/api/auth/me')
             ->assertOk()
             ->assertJsonPath('data.id', $user->id)
             ->assertJsonPath('data.email', $user->email);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Chat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    public function test_access_protected_resource(): void
    {
        $user = User::factory()->create();

        Chat::factory()
            ->private()
            ->count(3)
            ->hasAttached($user, ['is_admin' => true], 'participants')
            ->has(User::factory(), 'participants')
            ->create();

        $response = $this->getJson(route('chats.index'));

        $response->assertUnauthorized();
    }

    public function test_login_with_unknown_user(): void
    {
        $response = $this->postJson(route('login'), [
            'username' => 'unknown',
            'password' => 'secret',
        ]);

        $response->assertNotFound()->json(['error' => 'User not found']);
    }

    public function test_login_with_invalid_credentials(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson(route('login'), [
            'username' => $user->username,
            'password' => 'password',
        ]);

        $response->assertUnauthorized()->assertJson([
            'error' => 'Wrong authentication credentials'
        ]);
    }

    public function test_login_with_correct_credentials(): void
    {
        $user = User::factory(null, [
            'password' => 'secret'
        ])->create();

        $response = $this->postJson(route('login'), [
            'username' => $user->username,
            'password' => 'secret',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['data', 'access_token', 'refresh_token'])
            ->assertJson([
                'data' => [
                    'name' => $user->name,
                    'username' => $user->username,
                    'email' => $user->email,
                ]
            ]);
    }
}

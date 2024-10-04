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

    public function test_login_successfully_with_email(): void
    {
        $user = User::factory(null, [
            'password' => 'secret'
        ])->create();

        $response = $this->postJson(route('login'), [
            'username' => $user->email,
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

    public function test_login_successfully_with_username(): void
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

    public function test_login_successfully_with_phone_number(): void
    {
        $user = User::factory(null, [
            'phone_number' => '628565540000',
            'password' => 'secret'
        ])->create();

        $response = $this->postJson(route('login'), [
            'username' => $user->phone_number,
            'password' => 'secret',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['data', 'access_token', 'refresh_token'])
            ->assertJson([
                'data' => [
                    'name' => $user->name,
                    'username' => $user->username,
                    'email' => $user->email,
                    'phone_number' => $user->phone_number,
                ]
            ]);
    }

    public function test_logout()
    {
        $user = User::factory()->create();

        $token = $user->createToken('app-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson(route('logout'));

        $response->assertOk();
    }

    public function test_register_successfully(): void
    {
        $response = $this->postJson(route('register'), [
            'name' => $this->faker->name(),
            'email' => $email = $this->faker->email(),
            'username' => $this->faker->userName(),
            'phone_number' => $this->faker->phoneNumber(),
            'password' => $password = $this->faker->password(),
            'password_confirmation' => $password,
            'agreement' => 1,
        ]);

        $user = User::where('email', $email)->first();

        $response->assertCreated()
            ->assertJson([
                'data' => [
                    'name' => $user->name,
                    'username' => $user->username,
                    'email' => $user->email,
                ]
            ]);
    }
}

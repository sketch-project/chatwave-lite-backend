<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AccountTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    public function test_retrieve_logged_user_profile(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson(route('account'));

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'name' => $user->name,
                    'username' => $user->username,
                    'email' => $user->email,
                ]
            ]);
    }


    public function test_user_successfully_update_their_account(): void
    {
        $user = User::factory()->create();
        Storage::fake('avatar');

        $data = [
            'name' => $this->faker->name(),
            'email' => $this->faker->email(),
            'username' => $this->faker->userName(),
            'phone_number' => $this->faker->phoneNumber(),
            'avatar' => UploadedFile::fake()->image('image.jpg'),
        ];
        $response = $this->actingAs($user)->putJson(route('account.update'), $data);

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'name' => $data['name'],
                    'username' => $data['username'],
                    'email' => $data['email'],
                    'phone_number' => $data['phone_number'],
                ]
            ]);
    }

}

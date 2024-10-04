<?php

namespace Tests\Feature;

use App\Enums\MessageType;
use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MessageTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    public function test_user_can_retrieve_chat_messages(): void
    {
        $user = User::factory()->create();
        $partner = User::factory()->create();

        $chat = Chat::factory()
            ->private()
            ->has(Message::factory()->count(20)->state([
                'user_id' => $this->faker->randomElement([$user->id, $partner->id]),
            ]))
            ->hasAttached([$user, $partner], ['is_admin' => true], 'participants')
            ->create();

        $response = $this->actingAs($user)->getJson(route('chats.messages.index', $chat));

        $response->assertOk()->assertJsonCount(15, 'data');
    }

    public function test_user_cannot_retrieve_chat_messages_if_not_a_participant(): void
    {
        $user = User::factory()->create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $chat = Chat::factory()
            ->private()
            ->has(Message::factory()->count(20)->state([
                'user_id' => $this->faker->randomElement([$user1->id, $user2->id]),
            ]))
            ->hasAttached([$user1, $user2], ['is_admin' => true], 'participants')
            ->create();

        $response = $this->actingAs($user)->getJson(route('chats.messages.index', $chat));

        $response->assertForbidden();
    }

    public function test_user_can_successfully_send_text_message(): void
    {
        $user = User::factory()->create();
        $partner = User::factory()->create();

        $chat = Chat::factory()
            ->private()
            ->hasAttached([$user, $partner], ['is_admin' => true], 'participants')
            ->create();

        $data = [
            'message_type' => MessageType::TEXT->value,
            'content' => $this->faker->realText(),
        ];
        $response = $this->actingAs($user)->postJson(route('chats.messages.store', $chat), $data);

        $response->assertCreated()
            ->assertJson([
                'data' => [
                    'user' => [
                        'id' => $user->id
                    ],
                    'message_type' => $data['message_type'],
                    'content' => $data['content'],
                ]
            ]);

        $this->assertDatabaseHas(Message::class, [
            'chat_id' => $chat->id,
            'message_type' => $data['message_type'],
            'content' => $data['content'],
        ]);
    }

    public function test_user_can_successfully_send_image_message(): void
    {
        $user = User::factory()->create();
        $partner = User::factory()->create();

        $chat = Chat::factory()
            ->private()
            ->hasAttached([$user, $partner], ['is_admin' => true], 'participants')
            ->create();
        $message = $chat->messages()->create([
            'user_id' => $partner->id,
            'message_type' => MessageType::TEXT->value,
            'content' => 'Send me a message'
        ]);

        Storage::fake('media');
        $data = [
            'message_type' => MessageType::IMAGE->value,
            'content' => $this->faker->realText(),
            'media' => UploadedFile::fake()->image('image.jpg'),
            'reply_id' => $message->id,
        ];
        $response = $this->actingAs($user)->postJson(route('chats.messages.store', $chat), $data);

        $media = $chat->messages->where('user_id', $user->id)->first()->media;
        $response->assertCreated()
            ->assertJson([
                'data' => [
                    'user' => [
                        'id' => $user->id
                    ],
                    'message_type' => $data['message_type'],
                    'content' => $data['content'],
                    'media' => [
                        'file_name' => $media->file_name
                    ]
                ]
            ]);

        $this->assertDatabaseHas(Message::class, [
            'chat_id' => $chat->id,
            'message_type' => $data['message_type'],
            'content' => $data['content'],
            'media_id' => $media->id,
        ]);
    }

    public function test_user_can_successfully_send_image_base64_message(): void
    {
        $user = User::factory()->create();
        $partner = User::factory()->create();

        $chat = Chat::factory()
            ->private()
            ->hasAttached([$user, $partner], ['is_admin' => true], 'participants')
            ->create();
        $message = $chat->messages()->create([
            'user_id' => $partner->id,
            'message_type' => MessageType::TEXT->value,
            'content' => 'Send me a message'
        ]);

        Storage::fake('media');
        $data = [
            'message_type' => MessageType::IMAGE->value,
            'content' => $this->faker->realText(),
            'media_base64' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAgAAAAIAQMAAAD+wSzIAAAABlBMVEX///+/v7+jQ3Y5AAAADklEQVQI12P4AIX8EAgALgAD/aNpbtEAAAAASUVORK5CYII',
            'reply_id' => $message->id,
        ];
        $response = $this->actingAs($user)->postJson(route('chats.messages.store', $chat), $data);

        $media = $chat->messages->where('user_id', $user->id)->first()->media;
        $response->assertCreated()
            ->assertJson([
                'data' => [
                    'user' => [
                        'id' => $user->id
                    ],
                    'message_type' => $data['message_type'],
                    'content' => $data['content'],
                    'media' => [
                        'file_name' => $media->file_name
                    ]
                ]
            ]);

        $this->assertDatabaseHas(Message::class, [
            'chat_id' => $chat->id,
            'message_type' => $data['message_type'],
            'content' => $data['content'],
            'media_id' => $media->id,
        ]);
    }

    public function test_user_cannot_send_any_message_if_not_a_participant(): void
    {
        $user = User::factory()->create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $chat = Chat::factory()
            ->private()
            ->hasAttached([$user1, $user2], ['is_admin' => true], 'participants')
            ->create();

        $data = [
            'message_type' => MessageType::TEXT->value,
            'content' => $this->faker->realText(),
        ];
        $response = $this->actingAs($user)->postJson(route('chats.messages.store', $chat), $data);

        $response->assertForbidden();

        $this->assertDatabaseMissing(Message::class, [
            'chat_id' => $chat->id,
            'message_type' => $data['message_type'],
            'content' => $data['content'],
        ]);
    }

    public function test_user_can_successfully_edit_their_own_message(): void
    {
        $user = User::factory()->create();
        $partner = User::factory()->create();

        $chat = Chat::factory()
            ->private()
            ->hasAttached([$user, $partner], ['is_admin' => true], 'participants')
            ->create();
        $message = Message::factory()->create([
            'user_id' => $user->id,
            'chat_id' => $chat->id,
            'updated_at' => null,
        ]);

        $data = [
            'content' => $this->faker->realText(),
        ];
        $response = $this->actingAs($user)->putJson(route('chats.messages.update', $message), $data);

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'user' => [
                        'id' => $user->id
                    ],
                    'content' => $data['content'],
                ]
            ]);

        $this->assertDatabaseHas(Message::class, [
            'chat_id' => $chat->id,
            'content' => $data['content'],
        ]);
        $this->assertNotNull($message->refresh()->updated_at);
    }

    public function test_user_cannot_edit_any_message_if_not_own_the_message(): void
    {
        $user = User::factory()->create();
        $partner = User::factory()->create();

        $chat = Chat::factory()
            ->private()
            ->hasAttached([$user, $partner], ['is_admin' => true], 'participants')
            ->create();
        $message = Message::factory()->create([
            'user_id' => $partner->id,
            'chat_id' => $chat->id,
            'updated_at' => null,
        ]);

        $data = [
            'content' => $this->faker->realText(),
        ];
        $response = $this->actingAs($user)->putJson(route('chats.messages.update', $message), $data);

        $response->assertForbidden();

        $this->assertDatabaseMissing(Message::class, [
            'id' => $message->id,
            'content' => $data['content'],
        ]);
    }

    public function test_user_can_successfully_delete_their_own_message(): void
    {
        $user = User::factory()->create();
        $partner = User::factory()->create();

        $chat = Chat::factory()
            ->private()
            ->hasAttached([$user, $partner], ['is_admin' => true], 'participants')
            ->create();
        $message = Message::factory()->create([
            'user_id' => $user->id,
            'chat_id' => $chat->id,
        ]);

        $response = $this->actingAs($user)->deleteJson(route('chats.messages.destroy', $message));

        $response->assertNoContent();

        $this->assertDatabaseMissing(Message::class, [
            'id' => $message->id,
        ]);
    }

    public function test_user_cannot_delete_if_not_own_the_message(): void
    {
        $user = User::factory()->create();
        $partner = User::factory()->create();

        $chat = Chat::factory()
            ->private()
            ->hasAttached([$user, $partner], ['is_admin' => true], 'participants')
            ->create();
        $message = Message::factory()->create([
            'user_id' => $partner->id,
            'chat_id' => $chat->id,
        ]);

        $response = $this->actingAs($user)->deleteJson(route('chats.messages.destroy', $message));

        $response->assertForbidden();

        $this->assertDatabaseHas(Message::class, [
            'id' => $message->id,
        ]);
    }

}

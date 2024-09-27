<?php

namespace Tests\Feature;

use App\Enums\ChatType;
use App\Models\Chat;
use App\Models\ChatParticipant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ChatTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    public function test_get_user_chat_list()
    {
        $user = User::factory()->create();

        Chat::factory()
            ->private()
            ->count(20)
            ->hasAttached($user, ['is_admin' => true], 'participants')
            ->has(User::factory(), 'participants')
            ->create();

        $response = $this->actingAs($user)->getJson(route('chats.index'));

        $response->assertOk();
        $response->assertJsonCount(15, 'data');
    }

    public function test_create_private_chat_success(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $data = [
            'type' => ChatType::PRIVATE,
            'name' => $this->faker->name(),
            'description' => $this->faker->text(),
            'participants' => [$user2->id],
        ];

        $response = $this->actingAs($user1)->postJson(route('chats.store'), $data);

        $response->assertCreated()->assertJson([
            'data' => [
                'type' => $data['type']->value,
                'name' => $data['name'],
                'description' => $data['description'],
                'participants' => [
                    [
                        'id' => $user1->id,
                        'name' => $user1->name,
                    ],
                    [
                        'id' => $user2->id,
                        'name' => $user2->name,
                    ],
                ]
            ]
        ]);

        $this->assertDatabaseHas(Chat::class, [
            'type' => $data['type'],
            'name' => $data['name'],
            'description' => $data['description'],
        ]);
        $participants = Chat::where(['type' => $data['type'], 'name' => $data['name']])->first()->participants;
        $this->assertTrue($participants->contains($user1->id));
        $this->assertTrue($participants->contains($user2->id));
    }

    public function test_create_private_chat_cannot_add_multi_user(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();

        $data = [
            'type' => ChatType::PRIVATE,
            'name' => $this->faker->name(),
            'description' => $this->faker->text(),
            'participants' => [$user2->id, $user3->id],
        ];

        $response = $this->actingAs($user1)->postJson(route('chats.store'), $data);

        $response->assertUnprocessable()->assertJson([
            'errors' => [
                'participants' => ['The participants field must contain 1 items.'],
            ]
        ]);
    }

    public function test_create_chat_cannot_add_ourself(): void
    {
        $user1 = User::factory()->create();

        $data = [
            'type' => ChatType::PRIVATE,
            'name' => $this->faker->name(),
            'description' => $this->faker->text(),
            'participants' => [$user1->id],
        ];

        $response = $this->actingAs($user1)->postJson(route('chats.store'), $data);

        $response->assertUnprocessable()->assertJson([
            'errors' => [
                'participants' => ['Cannot select yourself as other participant.'],
            ]
        ]);
    }

    public function test_create_group_chat_success(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();

        $data = [
            'type' => ChatType::GROUP,
            'name' => $this->faker->name(),
            'description' => $this->faker->text(),
            'participants' => [$user2->id, $user3->id],
        ];

        $response = $this->actingAs($user1)->postJson(route('chats.store'), $data);

        $response->assertCreated()->assertJson([
            'data' => [
                'type' => $data['type']->value,
                'name' => $data['name'],
                'description' => $data['description'],
                'participants' => [
                    [
                        'id' => $user1->id,
                        'name' => $user1->name,
                    ],
                    [
                        'id' => $user2->id,
                        'name' => $user2->name,
                    ],
                    [
                        'id' => $user3->id,
                        'name' => $user3->name,
                    ],
                ]
            ]
        ]);

        $this->assertDatabaseHas(Chat::class, [
            'type' => $data['type'],
            'name' => $data['name'],
            'description' => $data['description'],
        ]);
        $participants = Chat::where(['type' => $data['type'], 'name' => $data['name']])->first()->participants;
        $this->assertTrue($participants->contains($user1->id));
        $this->assertTrue($participants->contains($user2->id));
        $this->assertTrue($participants->contains($user3->id));
    }

    public function test_create_group_chat_cannot_add_single_user(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $data = [
            'type' => ChatType::GROUP,
            'name' => $this->faker->name(),
            'description' => $this->faker->text(),
            'participants' => [$user2->id],
        ];

        $response = $this->actingAs($user1)->postJson(route('chats.store'), $data);

        $response->assertUnprocessable()->assertJson([
            'errors' => [
                'participants' => ['The participants field must have at least 2 items.'],
            ]
        ]);
    }

    public function test_update_chat_success(): void
    {
        $user = User::factory()->create();
        $chat = Chat::factory()
            ->hasAttached($user, [], 'participants')
            ->create();

        $data = [
            'name' => $this->faker->name(),
            'description' => $this->faker->text(),
        ];

        $response = $this->actingAs($user)->putJson(route('chats.update', $chat), $data);

        $response->assertOk()->assertJson([
            'data' => [
                'name' => $data['name'],
                'description' => $data['description'],
            ]
        ]);

        $this->assertDatabaseHas(Chat::class, [
            'name' => $data['name'],
            'description' => $data['description'],
        ]);
    }

    public function test_add_participant_on_group_chat(): void
    {
        $user = User::factory()->create();
        $chat = Chat::factory()
            ->group()
            ->hasAttached($user, [], 'participants')
            ->has(User::factory()->count(2), 'participants')
            ->create();
        $newParticipant = User::factory()->create();

        $response = $this->actingAs($user)->patchJson(route('chats.add-participant', ['chat' => $chat, 'user' => $newParticipant]));

        $response->assertOk()->assertJson([
            'data' => [
                'name' => $newParticipant['name'],
            ]
        ]);

        $this->assertDatabaseHas(ChatParticipant::class, [
            'chat_id' => $chat->id,
            'user_id' => $newParticipant->id,
        ]);
    }

    public function test_cannot_add_participant_on_private_chat(): void
    {
        $user = User::factory()->create();
        $chat = Chat::factory()
            ->private()
            ->hasAttached($user, [], 'participants')
            ->has(User::factory(), 'participants')
            ->create();
        $newParticipant = User::factory()->create();

        $response = $this->actingAs($user)->patchJson(route('chats.add-participant', ['chat' => $chat, 'user' => $newParticipant]));

        $response->assertUnprocessable()->assertJson([
            'errors' => [
                'chat' => ['Cannot add participant on private chat type.'],
            ]
        ]);
    }

    public function test_remove_participant_on_group_chat(): void
    {
        $user = User::factory()->create();
        $otherUsers = User::factory()->count(2)->create();
        $removedParticipant = User::factory()->create();
        $chat = Chat::factory()
            ->group()
            ->hasAttached([$user, ...$otherUsers, $removedParticipant], [], 'participants')
            ->create();

        $response = $this->actingAs($user)->patchJson(route('chats.remove-participant', ['chat' => $chat, 'user' => $removedParticipant]));

        $response->assertOk()->assertJson([
            'data' => [
                'name' => $removedParticipant['name'],
            ]
        ]);

        $this->assertDatabaseMissing(ChatParticipant::class, [
            'chat_id' => $chat->id,
            'user_id' => $removedParticipant->id,
        ]);
    }

    public function test_cannot_remove_participant_on_private_chat(): void
    {
        $user = User::factory()->create();
        $participant = User::factory()->create();
        $chat = Chat::factory()
            ->private()
            ->hasAttached([$user, $participant], [], 'participants')
            ->create();

        $response = $this->actingAs($user)->patchJson(route('chats.remove-participant', ['chat' => $chat, 'user' => $participant]));

        $response->assertUnprocessable()->assertJson([
            'errors' => [
                'chat' => ['Cannot remove participant on private chat type.'],
            ]
        ]);
    }

    public function test_promote_participant_as_admin_on_group_chat(): void
    {
        $user = User::factory()->create();
        $otherParticipant = User::factory()->create();
        $chat = Chat::factory()
            ->group()
            ->hasAttached($user, ['is_admin' => true], 'participants')
            ->hasAttached($otherParticipant, ['is_admin' => false], 'participants')
            ->create();

        $response = $this->actingAs($user)->patchJson(route('chats.make-admin', ['chat' => $chat, 'user' => $otherParticipant]));

        $response->assertOk()->assertJson([
            'data' => [
                'name' => $otherParticipant['name'],
            ]
        ]);

        $this->assertDatabaseHas(ChatParticipant::class, [
            'chat_id' => $chat->id,
            'user_id' => $otherParticipant->id,
            'is_admin' => 1,
        ]);
    }

    public function test_cannot_promote_participant_as_admin_by_non_admin_user(): void
    {
        $user = User::factory()->create();
        $otherParticipant = User::factory()->create();
        $chat = Chat::factory()
            ->group()
            ->hasAttached($user, ['is_admin' => false], 'participants')
            ->hasAttached($otherParticipant, ['is_admin' => false], 'participants')
            ->create();

        $response = $this->actingAs($user)->patchJson(route('chats.make-admin', ['chat' => $chat, 'user' => $otherParticipant]));

        $response->assertForbidden()->assertJson([
            'error' => 'You are not admin in the group.'
        ]);
    }

    public function test_cannot_promote_participant_as_admin_on_private_chat(): void
    {
        $user = User::factory()->create();
        $chat = Chat::factory()
            ->private()
            ->hasAttached($user, [], 'participants')
            ->has(User::factory(), 'participants')
            ->create();
        $newParticipant = User::factory()->create();

        $response = $this->actingAs($user)->patchJson(route('chats.make-admin', ['chat' => $chat, 'user' => $newParticipant]));

        $response->assertUnprocessable()->assertJson([
            'errors' => [
                'chat' => ['Cannot assign the user as admin in a private chat.'],
            ]
        ]);
    }

    public function test_cannot_promote_participant_as_admin_if_not_participant_in_the_group(): void
    {
        $user = User::factory()->create();
        $otherParticipant = User::factory()->create();
        $chat = Chat::factory()
            ->group()
            ->hasAttached($user, ['is_admin' => true], 'participants')
            ->create();

        $response = $this->actingAs($user)->patchJson(route('chats.make-admin', ['chat' => $chat, 'user' => $otherParticipant]));

        $response->assertUnprocessable()->assertJson([
            'errors' => [
                'user' => ['The user is not a participant in the group.'],
            ]
        ]);
    }

    public function test_dismiss_participant_as_admin_on_group_chat(): void
    {
        $user = User::factory()->create();
        $otherParticipant = User::factory()->create();
        $chat = Chat::factory()
            ->group()
            ->hasAttached($user, ['is_admin' => true], 'participants')
            ->hasAttached($otherParticipant, ['is_admin' => true], 'participants')
            ->create();

        $response = $this->actingAs($user)->patchJson(route('chats.dismiss-admin', ['chat' => $chat, 'user' => $otherParticipant]));

        $response->assertOk()->assertJson([
            'data' => [
                'name' => $otherParticipant['name'],
            ]
        ]);

        $this->assertDatabaseHas(ChatParticipant::class, [
            'chat_id' => $chat->id,
            'user_id' => $otherParticipant->id,
            'is_admin' => 0,
        ]);
    }

    public function test_cannot_dismiss_participant_as_admin_by_non_admin_user(): void
    {
        $user = User::factory()->create();
        $otherParticipant = User::factory()->create();
        $chat = Chat::factory()
            ->group()
            ->hasAttached($user, ['is_admin' => false], 'participants')
            ->hasAttached($otherParticipant, ['is_admin' => false], 'participants')
            ->create();

        $response = $this->actingAs($user)->patchJson(route('chats.dismiss-admin', ['chat' => $chat, 'user' => $otherParticipant]));

        $response->assertForbidden()->assertJson([
            'error' => 'You are not admin in the group.'
        ]);
    }

    public function test_cannot_dismiss_participant_as_admin_on_private_chat(): void
    {
        $user = User::factory()->create();
        $chat = Chat::factory()
            ->private()
            ->hasAttached($user, [], 'participants')
            ->has(User::factory(), 'participants')
            ->create();
        $newParticipant = User::factory()->create();

        $response = $this->actingAs($user)->patchJson(route('chats.dismiss-admin', ['chat' => $chat, 'user' => $newParticipant]));

        $response->assertUnprocessable()->assertJson([
            'errors' => [
                'chat' => ['Cannot dismiss the user as admin in a private chat.'],
            ]
        ]);
    }

    public function test_cannot_dismiss_participant_as_admin_if_not_participant_in_the_group(): void
    {
        $user = User::factory()->create();
        $otherParticipant = User::factory()->create();
        $chat = Chat::factory()
            ->group()
            ->hasAttached($user, ['is_admin' => true], 'participants')
            ->create();

        $response = $this->actingAs($user)->patchJson(route('chats.dismiss-admin', ['chat' => $chat, 'user' => $otherParticipant]));

        $response->assertUnprocessable()->assertJson([
            'errors' => [
                'user' => ['The user is not a participant in the group.'],
            ]
        ]);
    }

    public function test_delete_chat(): void
    {
        $user = User::factory()->create();
        $participant = User::factory()->create();
        $chat = Chat::factory()
            ->private()
            ->hasAttached([$user, $participant], [], 'participants')
            ->create();

        $response = $this->actingAs($user)->deleteJson(route('chats.destroy', $chat));

        $response->assertNoContent();
    }
}

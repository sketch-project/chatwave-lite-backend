<?php

namespace App\Services;

use App\Enums\ChatType;
use App\Events\ChatCreated;
use App\Events\ChatUpdated;
use App\Http\Requests\Chat\StoreChatRequest;
use App\Http\Requests\Chat\UpdateChatAvatarRequest;
use App\Http\Requests\Chat\UpdateChatRequest;
use App\Http\Requests\Message\StoreMessageRequest;
use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use App\Repositories\ChatRepository;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

readonly class ChatService
{
    public function __construct(
        private ChatRepository $chatRepository,
        private MessageService $messageService,
    ) {}

    public function getAllPaginated(Request $request)
    {
        return $this->chatRepository->getAllPaginated($request);
    }

    public function create(StoreChatRequest $request): Chat
    {
        $avatar = $request->file('avatar');
        if ($avatar) {
            $avatar = $avatar->store('avatars/' . date('Y/m')) ?: null;
        }

        $participants = $request->input('participants', []);

        if ($request->input('type') == ChatType::PRIVATE->value) {
            $existingChat = $this->chatRepository->getPrivateChatByUserIds([
                $request->user()->id, ...$participants,
            ]);
            if (!empty($existingChat)) {
                broadcast(new ChatCreated($existingChat))->toOthers();

                return $existingChat;
            }
        }

        return DB::transaction(function () use ($request, $avatar, $participants) {
            $chat = $this->chatRepository->create([
                'type' => $request->input('type'),
                'name' => $request->input('name'),
                'avatar' => $avatar,
                'description' => $request->input('description'),
            ]);

            $this->chatRepository->addParticipants($chat, collect($participants)
                ->mapWithKeys(fn ($id) => [$id => ['is_admin' => false]])
                ->put($request->user()->id, ['is_admin' => true])
                ->toArray()
            );

            if ($request->filled('message')) {
                $messageRequest = new StoreMessageRequest($request->input('message'));
                $messageRequest->setUserResolver(function () use ($request) {
                    return $request->user();
                });
                $this->messageService->create($chat, $messageRequest, shouldBroadcast: false);
            }

            broadcast(new ChatCreated($chat))->toOthers();

            return $chat;
        });
    }

    public function update(Chat $chat, UpdateChatRequest $request): bool
    {
        if ($avatar = $request->file('avatar')) {
            $avatarPath = $avatar->store('avatars/' . date('Y/m')) ?: $chat->avatar;
        } else {
            $avatarPath = $chat->avatar;
        }

        $result = $this->chatRepository->update($chat, [
            'name' => $request->input('name'),
            'avatar' => $avatarPath,
            'description' => $request->input('description'),
        ]);

        if ($result) {
            broadcast(new ChatUpdated($chat))->toOthers();
        }

        return $result;
    }

    public function updateLastMessage(Chat $chat, Message $message): bool
    {
        return $this->chatRepository->updateLastMessage($chat, $message);
    }

    /**
     * @throws Exception
     */
    public function updateAvatar(Chat $chat, UpdateChatAvatarRequest $request): bool
    {
        $avatar = $request->file('avatar');
        if ($avatar = $avatar->store('avatars/' . date('Y/m'))) {
            $lastAvatar = $chat->avatar;
            $result = $this->chatRepository->update($chat, [
                'avatar' => $avatar,
            ]);

            if ($lastAvatar) {
                Storage::delete($lastAvatar);
            }

            return $result;
        }
        throw new Exception(__('Cannot upload avatar'));
    }

    /**
     * @throws ValidationException
     * @throws Exception
     */
    public function addParticipant(Chat $chat, User $user): User
    {
        if ($chat->type == ChatType::PRIVATE) {
            throw ValidationException::withMessages([
                'chat' => __('Cannot add participant on private chat type.'),
            ]);
        }

        if ($chat->participants->contains($user)) {
            throw ValidationException::withMessages([
                'user' => __('The user is already a participant in the group.'),
            ]);
        }

        $this->chatRepository->addParticipant($chat, $user);

        if ($chat->refresh()->participants->contains($user)) {
            return $user;
        }
        throw new Exception(__('Cannot add participant.'));
    }

    /**
     * @throws ValidationException
     */
    public function removeParticipant(Chat $chat, User $user): User
    {
        if ($chat->type == ChatType::PRIVATE) {
            throw ValidationException::withMessages([
                'chat' => __('Cannot remove participant on private chat type.'),
            ]);
        }

        if ($this->chatRepository->removeParticipant($chat, $user) == 0) {
            throw new ModelNotFoundException(__('The user is not found in the group.'));
        }

        return $user;
    }

    /**
     * @throws ValidationException
     * @throws AuthorizationException
     */
    public function makeAsAdmin(Chat $chat, User $user): User
    {
        if ($chat->type == ChatType::PRIVATE) {
            throw ValidationException::withMessages([
                'chat' => __('Cannot assign the user as admin in a private chat.'),
            ]);
        }
        $chatParticipants = $chat->participants;
        if (!$chatParticipants->contains($user)) {
            throw ValidationException::withMessages([
                'user' => __('The user is not a participant in the group.'),
            ]);
        }

        return $this->chatRepository->makeAsAdmin($chat, $user);
    }

    /**
     * @throws ValidationException
     * @throws AuthorizationException
     */
    public function dismissAsAdmin(Chat $chat, User $user): User
    {
        if ($chat->type == ChatType::PRIVATE) {
            throw ValidationException::withMessages([
                'chat' => __('Cannot dismiss the user as admin in a private chat.'),
            ]);
        }
        $chatParticipants = $chat->participants;
        if (!$chatParticipants->contains($user)) {
            throw ValidationException::withMessages([
                'user' => __('The user is not a participant in the group.'),
            ]);
        }

        return $this->chatRepository->dismissAsAdmin($chat, $user);
    }

    public function delete(Chat $chat): bool
    {
        return $this->chatRepository->delete($chat);
    }
}

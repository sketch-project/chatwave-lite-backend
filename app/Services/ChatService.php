<?php

namespace App\Services;

use App\Enums\ChatType;
use App\Http\Requests\Chat\StoreChatRequest;
use App\Http\Requests\Chat\UpdateChatAvatarRequest;
use App\Http\Requests\Chat\UpdateChatRequest;
use App\Http\Resources\ChatResource;
use App\Models\Chat;
use App\Models\User;
use App\Repositories\ChatRepository;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

readonly class ChatService
{
    public function __construct(
        private ChatRepository $chatRepository
    ) {}

    public function getAllPaginated(Request $request)
    {
        $chats = $this->chatRepository->getAllPaginated($request);

        return ChatResource::collection($chats);
    }

    public function create(StoreChatRequest $request)
    {
        $avatar = $request->file('avatar');
        if ($avatar) {
            $avatar = $avatar->store('avatars/' . date('Y/m')) ?: null;
        }

        return DB::transaction(function () use ($request, $avatar) {
            $chat = $this->chatRepository->create([
                'type' => $request->input('type'),
                'name' => $request->input('name'),
                'avatar' => $avatar,
                'description' => $request->input('description'),
            ]);

            $this->chatRepository->addParticipants($chat, [
                ...$request->input('participants', []),
                auth()->user()->id,
            ]);

            return $chat;
        });
    }

    public function update(Chat $chat, UpdateChatRequest $request): bool
    {
        $avatar = $request->file('avatar');
        if ($avatar) {
            $avatar = $avatar->store('avatars/' . date('Y/m')) ?: $chat->avatar;
        } else {
            $avatar = $chat->avatar;
        }

        return $this->chatRepository->update($chat, [
            'name' => $request->input('name'),
            'avatar' => $avatar,
            'description' => $request->input('description'),
        ]);
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
     */
    public function addParticipant(Chat $chat, User $user): User
    {
        if ($chat->type == ChatType::PRIVATE) {
            throw ValidationException::withMessages([
                'chat' => __('Cannot add participant on private chat type.'),
            ]);
        }

        return $this->chatRepository->addParticipant($chat, $user);
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

        return $this->chatRepository->removeParticipant($chat, $user);
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

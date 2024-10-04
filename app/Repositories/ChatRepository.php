<?php

namespace App\Repositories;

use App\Enums\ChatType;
use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Http\Request;

readonly class ChatRepository
{
    public function __construct(private Chat $model) {}

    public function getAllPaginated(?Request $request = null): CursorPaginator
    {
        return $request->user()->chats()
            ->orderBy('updated_at', 'desc')
            ->cursorPaginate();
    }

    public function getPrivateChatByUserIds(array $userIds)
    {
        return $this->model->query()
            ->select('chats.*')
            ->join('chat_participants', 'chat_participants.chat_id', '=', 'chats.id')
            ->whereIn('chat_participants.user_id', $userIds)
            ->where('chats.type', ChatType::PRIVATE)
            ->groupBy('chats.id')
            ->havingRaw('COUNT(chats.id) = ?', [count($userIds)])
            ->first();
    }

    public function create($data)
    {
        return $this->model->newInstance()->create($data)->refresh();
    }

    public function update(Chat $chat, $data = null): bool
    {
        return $chat->fill($data)->save();
    }

    public function updateLastMessage(Chat $chat, Message $message): bool
    {
        $chat->last_message_id = $message->id;

        return $chat->save();
    }

    public function addParticipants(Chat $chat, array $participants)
    {
        $chat->participants()->attach($participants);

        return $chat->participants()->whereIn('user_id', $participants)->get();
    }

    public function addParticipant(Chat $chat, User $user): User
    {
        $chat->participants()->attach($user->id, ['is_admin' => false]);

        return $user;
    }

    public function removeParticipant(Chat $chat, User $user): int
    {
        return $chat->participants()->detach($user->id);
    }

    public function makeAsAdmin(Chat $chat, User $user): User
    {
        $chat->participants()->syncWithPivotValues($user, ['is_admin' => true], false);

        return $user;
    }

    public function dismissAsAdmin(Chat $chat, User $user): User
    {
        $chat->participants()->syncWithPivotValues($user, ['is_admin' => false], false);

        return $user;
    }

    public function delete(Chat $chat): ?bool
    {
        return $chat->delete();
    }
}

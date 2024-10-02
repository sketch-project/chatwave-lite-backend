<?php

namespace App\Repositories;

use App\Enums\ChatType;
use App\Models\Chat;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ChatRepository extends BaseRepository
{
    public function __construct(Chat $model)
    {
        parent::__construct($model);
    }

    public function getAllPaginated(?Request $request = null, $options = null)
    {
        return $this->model->query()
            ->whereHas('participants', function (Builder $query) use ($request) {
                $query->where('user_id', $request->user()->id);
            })
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
}

<?php

namespace App\Repositories;

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
            ->orderBy('id', 'desc')
            ->cursorPaginate();
    }

    public function addParticipants(Chat $chat, array $participants)
    {
        $chat->participants()->attach($participants);

        return $chat->participants()->whereIn('user_id', $participants)->get();
    }

    public function addParticipant(Chat $chat, User $user): User
    {
        $chat->participants()->attach($user->id);

        return $user;
    }

    public function removeParticipant(Chat $chat, User $user): User
    {
        $chat->participants()->detach($user->id);

        return $user;
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

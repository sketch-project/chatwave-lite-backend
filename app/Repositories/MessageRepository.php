<?php

namespace App\Repositories;

use App\Models\Chat;
use App\Models\Message;
use Illuminate\Contracts\Pagination\CursorPaginator;

class MessageRepository
{
    public function getAllPaginated(Chat $chat): CursorPaginator
    {
        return $chat->messages()
            ->orderBy('created_at', 'desc')
            ->cursorPaginate();
    }

    public function create(Chat $chat, $data): Message
    {
        return $chat->messages()->create($data);
    }

    public function update(Message $message, $data): bool
    {
        return $message->update($data);
    }

    public function delete(Message $message): ?bool
    {
        return $message->delete();
    }
}

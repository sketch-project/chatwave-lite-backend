<?php

namespace App\Policies;

use App\Models\Chat;
use App\Models\Message;
use App\Models\User;

class MessagePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user, Chat $chat): bool
    {
        return $chat->participants->contains($user);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Message $message): bool
    {
        return $user->id == $message->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, Chat $chat): bool
    {
        return $chat->participants->contains($user);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Message $message): bool
    {
        return $user->id == $message->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Message $message)
    {
        return $user->id == $message->user_id;
    }
}

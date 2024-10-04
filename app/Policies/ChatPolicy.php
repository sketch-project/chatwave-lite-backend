<?php

namespace App\Policies;

use App\Models\Chat;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ChatPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasVerifiedEmail();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasVerifiedEmail();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Chat $chat): Response
    {
        return $this->groupAdmin($user, $chat);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function groupAdmin(User $user, Chat $chat): Response
    {
        if (!$chat->participants->find($user->id)?->chatParticipants?->is_admin) {
            return Response::deny(__('You are not admin in the group.'));
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Chat $chat): Response
    {
        return $this->groupAdmin($user, $chat);
    }
}

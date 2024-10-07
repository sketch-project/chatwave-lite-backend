<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('users.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chats.{user}', function (\App\Models\User $user, \App\Models\User $me) {
    return $user->id == $me->id;
});

<?php

namespace App\Repositories;

use App\Models\Message;

class MessageRepository
{
    public function __construct(private Message $model) {}
}

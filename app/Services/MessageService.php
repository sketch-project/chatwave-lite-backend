<?php

namespace App\Services;

use App\Repositories\MessageRepository;

readonly class MessageService
{
    public function __construct(
        private MessageRepository $messageRepository
    ) {}
}

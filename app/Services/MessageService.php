<?php

namespace App\Services;

use App\Enums\MessageType;
use App\Events\MessageSent;
use App\Http\Requests\Message\StoreMessageRequest;
use App\Http\Requests\Message\UpdateMessageRequest;
use App\Models\Chat;
use App\Models\Message;
use App\Repositories\ChatRepository;
use App\Repositories\MessageRepository;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Support\Facades\DB;

readonly class MessageService
{
    public function __construct(
        private MessageRepository $messageRepository,
        private ChatRepository $chatRepository,
        private MediaService $mediaService,
    ) {}

    public function getAllPaginated(Chat $chat): CursorPaginator
    {
        return $this->messageRepository->getAllPaginated($chat);
    }

    public function create(Chat $chat, StoreMessageRequest $request, $shouldBroadcast = true)
    {
        $data = [
            'user_id' => $request->user()->id,
            'reply_id' => $request->input('reply_id'),
            'message_type' => $request->input('message_type'),
            'content' => $request->input('content'),
        ];

        return DB::transaction(function () use ($chat, $data, $request, $shouldBroadcast) {
            if ($data['message_type'] != MessageType::TEXT->value) {
                $file = $request->file('media') ?: $request->input('media_base64');
                $media = $this->mediaService->create($file);
                $data['media_id'] = $media->id;
            }

            $message = $this->messageRepository->create($chat, $data);

            $this->chatRepository->updateLastMessage($chat, $message);

            if ($shouldBroadcast) {
                broadcast(new MessageSent($message))->toOthers();
            }

            return $message;
        });
    }

    public function update(Message $message, UpdateMessageRequest $request): bool
    {
        return $this->messageRepository->update($message, [
            'content' => $request->input('content'),
        ]);
    }

    public function delete(Message $message): ?bool
    {
        return $this->messageRepository->delete($message);
    }
}

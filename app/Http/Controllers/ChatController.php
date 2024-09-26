<?php

namespace App\Http\Controllers;

use App\Http\Requests\Chat\StoreChatRequest;
use App\Http\Requests\Chat\UpdateChatRequest;
use App\Http\Resources\ChatResource;
use App\Http\Resources\UserResource;
use App\Models\Chat;
use App\Models\User;
use App\Services\ChatService;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class ChatController extends Controller
{
    use ValidatesRequests;

    public function __construct(private readonly ChatService $chatService) {}

    public function index(Request $request)
    {
        return $this->chatService->getAllPaginated($request);
    }

    public function store(StoreChatRequest $request): ChatResource
    {
        $chat = $this->chatService->create($request);

        return ChatResource::make($chat);
    }

    public function update(Chat $chat, UpdateChatRequest $request): JsonResponse
    {
        $chat = $this->chatService->update($chat, $request);

        return ChatResource::make($chat)->response()->setStatusCode(200);
    }

    /**
     * @throws ValidationException
     */
    public function addParticipant(Chat $chat, User $user): UserResource
    {
        $chat = $this->chatService->addParticipant($chat, $user);

        return UserResource::make($chat);
    }

    /**
     * @throws ValidationException
     */
    public function removeParticipant(Chat $chat, User $user): UserResource
    {
        $chat = $this->chatService->removeParticipant($chat, $user);

        return UserResource::make($chat);
    }

    public function destroy(Chat $chat): Response
    {
        $this->chatService->delete($chat);

        return response()->noContent();
    }
}

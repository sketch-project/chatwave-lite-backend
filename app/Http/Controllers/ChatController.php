<?php

namespace App\Http\Controllers;

use App\Http\Requests\Chat\StoreChatRequest;
use App\Http\Requests\Chat\UpdateChatRequest;
use App\Http\Resources\ChatResource;
use App\Http\Resources\UserResource;
use App\Models\Chat;
use App\Models\User;
use App\Services\ChatService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class ChatController extends Controller
{
    use AuthorizesRequests, ValidatesRequests;

    public function __construct(private readonly ChatService $chatService) {}

    /**
     * @throws AuthorizationException
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('view-any', Chat::class);

        return ChatResource::collection($this->chatService->getAllPaginated($request));
    }

    /**
     * @throws AuthorizationException
     */
    public function store(StoreChatRequest $request): ChatResource
    {
        $this->authorize('create', Chat::class);

        $chat = $this->chatService->create($request);

        return ChatResource::make($chat);
    }

    /**
     * @throws AuthorizationException
     */
    public function update(Chat $chat, UpdateChatRequest $request): JsonResponse
    {
        $this->authorize('update', $chat);

        $chat = $this->chatService->update($chat, $request);

        return ChatResource::make($chat)->response()->setStatusCode(200);
    }

    /**
     * @throws ValidationException
     * @throws AuthorizationException
     */
    public function addParticipant(Chat $chat, User $user): UserResource
    {
        $this->authorize('group-admin', $chat);

        $chat = $this->chatService->addParticipant($chat, $user);

        return UserResource::make($chat);
    }

    /**
     * @throws ValidationException
     * @throws AuthorizationException
     */
    public function removeParticipant(Chat $chat, User $user): UserResource
    {
        $this->authorize('group-admin', $chat);

        $chat = $this->chatService->removeParticipant($chat, $user);

        return UserResource::make($chat);
    }

    /**
     * @throws ValidationException
     * @throws AuthorizationException
     */
    public function makeAsAdmin(Chat $chat, User $user): UserResource
    {
        $this->authorize('group-admin', $chat);

        $chat = $this->chatService->makeAsAdmin($chat, $user);

        return UserResource::make($chat);
    }

    /**
     * @throws ValidationException
     * @throws AuthorizationException
     */
    public function dismissAsAdmin(Chat $chat, User $user): UserResource
    {
        $this->authorize('group-admin', $chat);

        $chat = $this->chatService->dismissAsAdmin($chat, $user);

        return UserResource::make($chat);
    }

    /**
     * @throws AuthorizationException
     */
    public function destroy(Chat $chat): Response
    {
        $this->authorize('delete', $chat);

        $this->chatService->delete($chat);

        return response()->noContent();
    }
}

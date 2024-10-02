<?php

namespace App\Http\Controllers;

use App\Http\Requests\Chat\StoreChatRequest;
use App\Http\Requests\Chat\UpdateChatAvatarRequest;
use App\Http\Requests\Chat\UpdateChatRequest;
use App\Http\Resources\ChatResource;
use App\Http\Resources\UserResource;
use App\Models\Chat;
use App\Models\User;
use App\Services\ChatService;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
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

        return ChatResource::make($chat->load('participants'));
    }

    /**
     * @throws AuthorizationException
     */
    public function update(Chat $chat, UpdateChatRequest $request): ChatResource
    {
        $this->authorize('update', $chat);

        $this->chatService->update($chat, $request);

        return ChatResource::make($chat->load('participants'));
    }

    /**
     * @throws ValidationException
     * @throws AuthorizationException
     */
    public function addParticipant(Chat $chat, User $user): UserResource
    {
        $this->authorize('group-admin', $chat);

        $user = $this->chatService->addParticipant($chat, $user);

        return UserResource::make($user);
    }

    /**
     * @throws ValidationException
     * @throws AuthorizationException
     */
    public function removeParticipant(Chat $chat, User $user): UserResource
    {
        $this->authorize('group-admin', $chat);

        $user = $this->chatService->removeParticipant($chat, $user);

        return UserResource::make($user);
    }

    /**
     * @throws ValidationException
     * @throws AuthorizationException
     */
    public function makeAsAdmin(Chat $chat, User $user): UserResource
    {
        $this->authorize('group-admin', $chat);

        $user = $this->chatService->makeAsAdmin($chat, $user);

        return UserResource::make($user);
    }

    /**
     * @throws ValidationException
     * @throws AuthorizationException
     */
    public function dismissAsAdmin(Chat $chat, User $user): UserResource
    {
        $this->authorize('group-admin', $chat);

        $user = $this->chatService->dismissAsAdmin($chat, $user);

        return UserResource::make($user);
    }

    /**
     * @throws AuthorizationException
     * @throws Exception
     */
    public function updateAvatar(Chat $chat, UpdateChatAvatarRequest $request): ChatResource
    {
        $this->authorize('update', $chat);

        $this->chatService->updateAvatar($chat, $request);

        return ChatResource::make($chat);
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

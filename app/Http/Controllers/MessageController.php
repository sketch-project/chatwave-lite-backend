<?php

namespace App\Http\Controllers;

use App\Http\Requests\Message\StoreMessageRequest;
use App\Http\Requests\Message\UpdateMessageRequest;
use App\Http\Resources\MessageResource;
use App\Models\Chat;
use App\Models\Message;
use App\Services\MessageService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class MessageController extends Controller
{
    use AuthorizesRequests, ValidatesRequests;

    public function __construct(private readonly MessageService $messageService) {}

    /**
     * Display a listing of the resource.
     *
     * @throws AuthorizationException
     */
    public function index(Chat $chat): AnonymousResourceCollection
    {
        $this->authorize('view-any', [Message::class, $chat]);

        return MessageResource::collection($this->messageService->getAllPaginated($chat));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @throws AuthorizationException
     */
    public function store(Chat $chat, StoreMessageRequest $request): MessageResource
    {
        $this->authorize('create', [Message::class, $chat]);

        return MessageResource::make($this->messageService->create($chat, $request));
    }

    /**
     * Update the specified resource in storage.
     *
     * @throws AuthorizationException
     */
    public function update(Message $message, UpdateMessageRequest $request): MessageResource
    {
        $this->authorize('update', $message);

        $this->messageService->update($message, $request);

        return MessageResource::make($message);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @throws AuthorizationException
     */
    public function destroy(Message $message): Response
    {
        $this->authorize('delete', $message);

        $this->messageService->delete($message);

        return response()->noContent();
    }
}

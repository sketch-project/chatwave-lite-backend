<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'chat_id' => $this->chat_id,
            'message_type' => $this->message_type,
            'user' => new UserResource($this->user),
            'content' => $this->content,
            'reply' => $this->when($this->reply_id, function () {
                return new ReplyMessageResource($this->reply);
            }),
            'chat' => ChatResource::make($this->chat),
            'media' => MediaResource::make($this->media),
            'is_forwarded' => $this->is_forwarded,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

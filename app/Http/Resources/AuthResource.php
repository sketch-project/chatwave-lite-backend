<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => UserResource::make($this['user']),
            'access_token' => $this['access_token'],
            'refresh_token' => $this['refresh_token'],
        ];
    }
}

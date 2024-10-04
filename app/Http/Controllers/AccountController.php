<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Services\UserService;

class AccountController extends Controller
{
    public function __construct(private readonly UserService $userService) {}

    public function update(UpdateUserRequest $request): UserResource
    {
        $user = $request->user();

        $this->userService->update($user, $request);

        return UserResource::make($user->refresh());
    }
}

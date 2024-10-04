<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function __construct(private readonly UserService $userService) {}

    public function index(Request $request): UserResource
    {
        return UserResource::make($request->user());
    }

    public function update(UpdateUserRequest $request): UserResource
    {
        $user = $request->user();

        $this->userService->update($user, $request);

        return UserResource::make($user->refresh());
    }
}

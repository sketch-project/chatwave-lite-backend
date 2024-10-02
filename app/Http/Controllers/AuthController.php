<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use App\Services\UserService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly UserService $userService,
    ) {}

    /**
     * @throws AuthenticationException
     */
    public function login(LoginRequest $request)
    {
        $username = $request->post('username');
        $password = $request->post('password');
        $result = $this->authService->authenticate($username, $password);

        return [
            'data' => $result['user'],
            'access_token' => $result['access_token'],
            'refresh_token' => $result['refresh_token'],
        ];
    }

    public function register(StoreUserRequest $request): UserResource
    {
        $user = $this->userService->create($request);

        return UserResource::make($user);
    }

    public function me(Request $request): UserResource
    {
        return UserResource::make($request->user());
    }
}

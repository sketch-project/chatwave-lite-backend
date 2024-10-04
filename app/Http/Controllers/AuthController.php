<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Resources\AuthResource;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use App\Services\UserService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
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
    public function login(LoginRequest $request): AuthResource
    {
        $username = $request->post('username');
        $password = $request->post('password');
        $result = $this->authService->authenticate($username, $password);

        return AuthResource::make($result);
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request);

        return response()->json([
            'message' => 'Successfully logged out',
        ]);
    }

    public function register(StoreUserRequest $request): UserResource
    {
        $user = $this->userService->create($request);

        return UserResource::make($user);
    }
}

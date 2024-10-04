<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Sanctum\TransientToken;

readonly class AuthService
{
    public function __construct(private UserRepository $userRepository) {}

    /**
     * @throws AuthenticationException
     */
    public function authenticate(string $username, string $password, bool $remember = false): array
    {
        if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
            $usernameField = 'email';
            $user = $this->userRepository->getByEmail($username);
        } elseif (is_numeric($username)) {
            $usernameField = 'phone_number';
            $user = $this->userRepository->getByPhoneNumber($username);
        } else {
            $usernameField = 'username';
            $user = $this->userRepository->getByUsername($username);
        }

        if (empty($user)) {
            $notFound = new ModelNotFoundException;
            $notFound->setModel(User::class, [$username]);
            throw $notFound;
        }

        $result = Auth::attempt([
            $usernameField => $username,
            'password' => $password,
        ], $remember);

        if (!$result) {
            throw new AuthenticationException(__('Wrong authentication credentials'));
        }

        $token = $user->createToken('app');

        return [
            'user' => $user,
            'access_token' => $token->plainTextToken,
            'refresh_token' => Str::random(48),
        ];
    }

    public function authenticateById($userId): false|Authenticatable
    {
        return Auth::loginUsingId($userId);
    }

    public function logout(Request $request): void
    {
        $currentAccessToken = auth()->user()->currentAccessToken();
        if ($currentAccessToken && !$currentAccessToken instanceof TransientToken) {
            $currentAccessToken->delete();
        }

        if ($request->hasSession()) {
            Auth::logout();

            $request->session()->invalidate();

            $request->session()->regenerateToken();
        }
    }
}

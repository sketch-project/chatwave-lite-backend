<?php

namespace App\Services;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Auth\Events\Registered;

readonly class UserService
{
    public function __construct(
        private UserRepository $userRepository
    ) {}

    public function create(StoreUserRequest $request): User
    {
        $data = $request->except('avatar');
        if ($avatar = $request->file('avatar')) {
            $result = $avatar->store('avatars/' . date('Y/m'));
            $data['avatar'] = $result ?: null;
        }
        $user = $this->userRepository->create($data);

        //event(new Registered($user));

        return $user;
    }

    public function update(User $user, UpdateUserRequest $request): bool
    {
        $data = $request->except('avatar');
        if ($avatar = $request->file('avatar')) {
            $result = $avatar->store('avatars/' . date('Y/m'));
            $data['avatar'] = $result ?: $user->avatar;
        }

        if (empty($data['password'])) {
            unset($data['password']);
        }
        $user->fill($data);

        return $user->save();
    }

    public function delete($user): bool
    {
        return $this->userRepository->delete($user);
    }
}

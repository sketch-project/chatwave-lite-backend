<?php

namespace App\Services;

use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
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
        $data = $request->validated();

        $user = $this->userRepository->create($data);

        event(new Registered($user));

        return $user->refresh();
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

        $this->userRepository->update($user, $data);

        return $user->save();
    }
}

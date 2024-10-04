<?php

namespace App\Repositories;

use App\Models\User;

readonly class UserRepository
{
    public function __construct(private User $model) {}

    public function getByEmail($email): ?User
    {
        return $this->model->query()->where('email', $email)->first();
    }

    public function getByUsername($email): ?User
    {
        return $this->model->query()->where('username', $email)->first();
    }

    public function getByPhoneNumber($phoneNumber): ?User
    {
        return $this->model->query()->where('phone_number', $phoneNumber)->first();
    }

    public function create($data): User
    {
        return $this->model->query()->create($data);
    }

    public function update(User $user, $data): bool
    {
        return $user->fill($data)->save();
    }

    public function delete($user): bool|int|null
    {
        if ($user instanceof User) {
            $result = $user->delete();
        } else {
            $result = $this->model->destroy($user);
        }

        return $result;
    }
}

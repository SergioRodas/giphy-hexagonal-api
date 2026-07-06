<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Eloquent;

use Domain\Shared\ValueObject\Email;
use Domain\User\Entity\User;
use Domain\User\Repository\UserRepository;
use Domain\User\ValueObject\UserId;
use Infrastructure\Persistence\Eloquent\Models\UserModel;

final class EloquentUserRepository implements UserRepository
{
    public function findByEmail(Email $email): ?User
    {
        $model = UserModel::query()->where('email', $email->value())->first();

        return $model === null ? null : $this->toDomain($model);
    }

    public function findById(UserId $id): ?User
    {
        $model = UserModel::query()->find($id->value());

        return $model === null ? null : $this->toDomain($model);
    }

    public function exists(UserId $id): bool
    {
        return UserModel::query()->whereKey($id->value())->exists();
    }

    private function toDomain(UserModel $model): User
    {
        return new User(
            new UserId($model->id),
            $model->name,
            new Email($model->email),
            $model->password,
        );
    }
}

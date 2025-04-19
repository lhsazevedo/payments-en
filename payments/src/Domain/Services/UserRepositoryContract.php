<?php

declare(strict_types=1);

namespace App\Domain\Services;

use App\Domain\User;

interface UserRepositoryContract
{
    public function findByIdForUpdate(int $id): ?User;

    public function findByIdForUpdateOrFail(int $id): User;

    public function save(User $user): User;
}

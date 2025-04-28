<?php

declare(strict_types=1);

namespace App\Domain\Services;

use App\Domain\User;

interface UserRepositoryContract
{
    public function findByIdOrfail(int $id): User;
}

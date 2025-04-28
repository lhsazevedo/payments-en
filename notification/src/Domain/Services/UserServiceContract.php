<?php

declare(strict_types=1);

namespace App\Domain\Services;

use App\Domain\User;

interface UserServiceContract
{
    public function fetchById(int $id): User;
}

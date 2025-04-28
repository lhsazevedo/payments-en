<?php

declare(strict_types=1);

namespace App\Domain;

use App\Domain\Services\UserRepositoryContract;

class GetUserAction
{
    public function __construct(
        private UserRepositoryContract $userRepository,
    ) {}

    public function __invoke(int $userId): User
    {
        return $this->userRepository->findByIdOrfail($userId);
    }
}

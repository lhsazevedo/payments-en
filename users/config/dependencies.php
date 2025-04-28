<?php

declare(strict_types=1);

use App\Domain\Services\UserRepositoryContract;
use App\Respository\DbUserRepository;

return [
    UserRepositoryContract::class => DbUserRepository::class,
];

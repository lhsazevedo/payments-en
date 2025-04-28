<?php

declare(strict_types=1);

namespace App\Domain\Services;

use App\Domain\Account;

interface AccountRepositoryContract
{
    public function findByIdForUpdate(int $id): ?Account;

    public function findByIdForUpdateOrFail(int $id): Account;

    public function save(Account $account): Account;
}

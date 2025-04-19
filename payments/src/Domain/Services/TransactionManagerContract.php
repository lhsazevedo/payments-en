<?php

declare(strict_types=1);

namespace App\Domain\Services;

interface TransactionManagerContract
{
    public function run(callable $cb): void;
}

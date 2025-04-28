<?php

declare(strict_types=1);

namespace App\Domain;

readonly class User
{
    public function __construct(
        public string $name,
        public string $email,
        public string $mobileNumber,
    ) {}
}

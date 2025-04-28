<?php

declare(strict_types=1);

namespace App\Domain;

use App\Domain\Exception\InsufficientFundsException;
use App\Domain\ValueObject\Amount;
use App\Domain\ValueObject\TaxId;

class User
{
    private ?int $id = null; // @phpstan-ignore property.unusedType

    public function __construct(
        public string $name,

        // TODO(Lucas): Convert to value object
        public string $mobileNumber,

        // TODO(Lucas): Convert to value object
        public string $email,
    ) {}

    public function getId(): ?int
    {
        return $this->id;
    }
}

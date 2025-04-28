<?php

declare(strict_types=1);

namespace App\Domain;

use App\Domain\ValueObject\Amount;

class Payment
{
    public \DateTimeImmutable $createdAt;

    private ?int $id = null; // @phpstan-ignore property.unusedType

    public function __construct(
        public Account $payer,
        public Account $payee,
        public Amount $amount,
    ) {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}

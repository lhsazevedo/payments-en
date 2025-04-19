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
        public TaxId $taxId,
        public UserType $type,

        // TODO(Lucas): Use a value object that allows negatve values
        private Amount $balance,
    ) {}

    public function credit(Amount $amount): Amount
    {
        $this->balance = new Amount($this->balance->value + $amount->value);

        return $this->balance;
    }

    public function debit(Amount $amount): Amount
    {
        if (! $this->hasFunds($amount)) {
            throw new InsufficientFundsException();
        }

        $this->balance = new Amount($this->balance->value - $amount->value);

        return $this->balance;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBalance(): Amount
    {
        return $this->balance;
    }

    public function isShopkeeper(): bool
    {
        return $this->type === UserType::Shopkeeper;
    }

    private function hasFunds(Amount $amount): bool
    {
        return $amount->value <= $this->balance->value;
    }
}

<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use App\Domain\Exception\InvalidAmountException;

// TODO(Lucas): Support currencies
readonly class Amount
{
    public function __construct(
        public int $value,
    ) {
        if ($value < 0) {
            throw new InvalidAmountException();
        }
    }

    public function __toString(): string
    {
        return $this->format();
    }

    public function format(): string
    {
        return 'R$ ' . number_format(
            $this->value / 100,
            decimals: 2,
            decimal_separator: ',',
            thousands_separator: '.',
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Domain;

use App\Domain\ValueObject\Amount;

readonly class PaymentEvent
{
    public function __construct(
        public int $payerUserId,
        public int $payeeUserId,
        public Amount $amount,
    ) {}
}

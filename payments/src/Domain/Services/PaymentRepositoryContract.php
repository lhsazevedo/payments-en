<?php

declare(strict_types=1);

namespace App\Domain\Services;

use App\Domain\Payment;

interface PaymentRepositoryContract
{
    public function save(Payment $user): Payment;
}

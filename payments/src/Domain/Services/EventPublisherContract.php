<?php

namespace App\Domain\Services;

use App\Domain\Payment;

interface EventPublisherContract
{
    public function publishTransferCreated(Payment $payment): void;
}

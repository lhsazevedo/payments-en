<?php

declare(strict_types=1);

namespace App\Domain\Services;

use App\Domain\Exception\UnauthorizedPaymentException;
use App\Domain\Payment;

interface PaymentAuthorizerContract
{
    /**
     * @throws UnauthorizedPaymentException
     */
    public function authorize(Payment $payment): void;
}

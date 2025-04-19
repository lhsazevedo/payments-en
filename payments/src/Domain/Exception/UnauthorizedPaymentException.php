<?php

declare(strict_types=1);

namespace App\Domain\Exception;

class UnauthorizedPaymentException extends DomainException
{
    /** @var string */
    protected $message = 'Invalid payer';
}

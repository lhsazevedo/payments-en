<?php

declare(strict_types=1);

namespace App\Domain\Exception;

class InvalidPayerException extends DomainException
{
    /** @var string */
    protected $message = 'Invalid payer';
}

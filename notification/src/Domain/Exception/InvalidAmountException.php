<?php

declare(strict_types=1);

namespace App\Domain\Exception;

class InvalidAmountException extends DomainException
{
    /** @var string */
    protected $message = 'Invalid amount';
}

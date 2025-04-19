<?php

declare(strict_types=1);

namespace App\Domain\Exception;

class InvalidTaxIdException extends DomainException
{
    /** @var string */
    protected $message = 'Invalid tax id';
}

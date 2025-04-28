<?php

declare(strict_types=1);

namespace App\Domain\Exception;

class DomainException extends \Exception
{
    /** @var string */
    protected $message = 'Unknown domain error';
}

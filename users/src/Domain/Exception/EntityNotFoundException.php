<?php

declare(strict_types=1);

namespace App\Domain\Exception;

class EntityNotFoundException extends DomainException
{
    /** @var string */
    protected $message = 'Entity not found';
}

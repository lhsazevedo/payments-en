<?php

declare(strict_types=1);

namespace App\Domain\Notification;

use App\Domain\Notification\NotificationContract;

abstract class AbstractNotification implements NotificationContract
{
    abstract public function getMessage(): string;

    public function asEmail(): string
    {
        return "<h1>{$this->getMessage()}</h1>";
    }

    public function asSms(): string
    {
        return $this->getMessage();
    }
}

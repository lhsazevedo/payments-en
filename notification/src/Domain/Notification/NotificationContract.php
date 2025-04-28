<?php

declare(strict_types=1);

namespace App\Domain\Notification;

interface NotificationContract
{
    public function asEmail(): string;

    public function asSms(): string;
}

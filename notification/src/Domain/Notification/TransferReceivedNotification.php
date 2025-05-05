<?php

declare(strict_types=1);

namespace App\Domain\Notification;

use App\Domain\Notification\NotificationContract;
use App\Domain\User;
use App\Domain\ValueObject\Amount;

class TransferReceivedNotification extends AbstractNotification implements NotificationContract
{
    public function __construct(
        readonly public User $payer,
        readonly public Amount $amount,
    ) {}

    public function getMessage(): string
    {
        return "You received {$this->amount->format()} from {$this->payer->name}.";
    }
}

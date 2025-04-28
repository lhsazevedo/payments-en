<?php

declare(strict_types=1);

namespace App\Domain\Notification;

use App\Domain\Notification\NotificationContract;
use App\Domain\User;
use App\Domain\ValueObject\Amount;

class TransferSentNotification extends AbstractNotification implements NotificationContract
{
    public function __construct(
        readonly public User $payee,
        readonly public Amount $amount,
    ) {}

    public function getMessage(): string
    {
        return "Você enviou {$this->amount->format()} para {$this->payee->name}.";
    }
}

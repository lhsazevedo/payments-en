<?php

declare(strict_types=1);

namespace App\Domain\Notification;

use App\Domain\Services\Notification\NotificationContract;
use App\Domain\User;
use App\Domain\ValueObject\Amount;

class PaymentReceivedNotification extends AbstractNotification implements NotificationContract
{
    public function __construct(
        readonly public User $payer,
        readonly public User $payee,
        readonly public Amount $amount,
    ) {}

    public function getMessage(): string
    {
        return "Olá, {$this->payee->name}! Você recebeu {$this->amount->format()} de {$this->payer->name}.";
    }
}

<?php

declare(strict_types=1);

namespace Tests\Domain\Notification;

use PHPUnit\Framework\TestCase;
use App\Domain\Notification\PaymentReceivedNotification;
use App\Domain\User;
use App\Domain\ValueObject\Amount;

class PaymentReceivedNotificationTest extends TestCase
{
    public function testGetMessage(): void
    {
        /** @var User&\PHPUnit\Framework\MockObject\MockObject */
        $payer = $this->createMock(User::class);
        $payer->name = 'Alice';
        /** @var User&\PHPUnit\Framework\MockObject\MockObject */
        $payee = $this->createMock(User::class);
        $payee->name = 'Bob';
        $amount = new Amount(1234);

        $notification = new PaymentReceivedNotification($payer, $payee, $amount);

        $expected = 'Olá, Bob! Você recebeu R$ 12,34 de Alice.';
        $this->assertSame($expected, $notification->getMessage());
    }

    public function testAsEmailReturnsWrappedMessage(): void
    {
        /** @var User&\PHPUnit\Framework\MockObject\MockObject */
        $payer = $this->createMock(User::class);
        $payer->name = 'Alice';
        /** @var User&\PHPUnit\Framework\MockObject\MockObject */
        $payee = $this->createMock(User::class);
        $payee->name = 'Bob';
        $amount = new Amount(100);

        $notification = new PaymentReceivedNotification($payer, $payee, $amount);

        $message = $notification->getMessage();
        $this->assertSame("<h1>Olá, Bob! Você recebeu R$ 1,00 de Alice.</h1>", $notification->asEmail());
    }

    public function testAsSmsReturnsPlainMessage(): void
    {
        /** @var User&\PHPUnit\Framework\MockObject\MockObject */
        $payer = $this->createMock(User::class);
        $payer->name = 'Alice';
        /** @var User&\PHPUnit\Framework\MockObject\MockObject */
        $payee = $this->createMock(User::class);
        $payee->name = 'Bob';
        $amount = new Amount(5000);

        $notification = new PaymentReceivedNotification($payer, $payee, $amount);

        $this->assertSame("Olá, Bob! Você recebeu R$ 50,00 de Alice.", $notification->asSms());
    }
}

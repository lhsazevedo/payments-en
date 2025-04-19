<?php

declare(strict_types=1);

namespace App\Tests\Services;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Hyperf\Amqp\Message\ProducerMessage;
use Hyperf\Amqp\Producer;
use App\Services\PlatformNotificationService;
use App\Domain\Services\Notification\NotificationContract;
use App\Domain\User;
use App\Domain\ValueObject\TaxId;
use App\Domain\UserType;
use App\Domain\ValueObject\Amount;

class PlatformNotificationServiceTest extends TestCase
{
    public function testNotifySendsSmsAndEmailMessages(): void
    {
        $mobile = '21987654321';
        $email  = 'bob@exemplo.com.br';
        $user   = new User(
            'Bob',
            $mobile,
            $email,
            new TaxId('12345678900'),
            UserType::Regular,
            new Amount(0),
        );

        /** @var NotificationContract&MockObject */
        $notification = $this->createMock(NotificationContract::class);
        $notification->method('asSms')->willReturn('sms text');
        $notification->method('asEmail')->willReturn('<h1>email text</h1>');

        /** @var Producer&MockObject */
        $producer = $this->createMock(Producer::class);

        $callMatcher = $this->exactly(2);
        $producer
            ->expects($callMatcher)
            ->method('produce')
            ->with($this->isInstanceOf(ProducerMessage::class))
            ->willReturnCallback(function (ProducerMessage $message) use ($callMatcher, $mobile, $email) {
                $ref = new \ReflectionClass($message);
                $prop = $ref->getProperty('payload');
                $payload = $prop->getValue($message);

                match ($callMatcher->numberOfInvocations()) {
                    1 => $this->assertSame(
                        ['mobile_number' => $mobile, 'message' => 'sms text'],
                        $payload,
                    ),
                    2 => $this->assertSame(
                        ['email' => $email, 'contents' => '<h1>email text</h1>'],
                        $payload,
                    ),
                };

                return true;
            });

        $service = new PlatformNotificationService($producer);
        $service->notify($user, $notification);
    }
}

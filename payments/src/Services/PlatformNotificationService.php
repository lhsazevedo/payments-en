<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\Services\Notification\NotificationContract;
use Hyperf\Amqp\Message\ProducerMessage;
use Hyperf\Amqp\Message\Type;
use Hyperf\Amqp\Producer;
use App\Domain\Services\Notification\NotificationServiceContract;
use App\Domain\User;

class PlatformNotificationService implements NotificationServiceContract
{
    public function __construct(
        private Producer $producer,
    ) {}

    public function notify(User $user, NotificationContract $notification): void
    {
        // TODO(Lucas): Use correct RabbitMQ exchange and routing keys
        $smsAmqpMessage = new class extends ProducerMessage {
            public string $exchange = 'hyperf';

            public string|Type $type = Type::TOPIC;

            /** @var array<string>|string */
            public array|string $routingKey = 'hyperf';
        };
        $smsAmqpMessage->setPayload([
            'mobile_number' => $user->mobileNumber,
            'message' => $notification->asSms(),
        ]);
        $this->producer->produce($smsAmqpMessage);

        // TODO(Lucas): Use correct RabbitMQ exchange and routing keys
        $emailAmqpMessage = new class extends ProducerMessage {
            public string $exchange = 'hyperf';

            public string|Type $type = Type::TOPIC;

            /** @var array<string>|string */
            public array|string $routingKey = 'hyperf';
        };
        $emailAmqpMessage->setPayload([
            'email' => $user->email,
            'contents' => $notification->asEmail(),
        ]);
        $this->producer->produce($emailAmqpMessage);
    }
}

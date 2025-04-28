<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\Payment;
use App\Domain\Services\EventPublisherContract;
use Hyperf\Amqp\Message\ProducerMessage;
use Hyperf\Amqp\Message\Type;
use Hyperf\Amqp\Producer;

class RabbitMqEventPublisher implements EventPublisherContract
{
    public function __construct(
        private Producer $producer,
    ) {}

    public function publishTransferCreated(Payment $payment): void
    {
        // TODO(Lucas): Use correct RabbitMQ exchange and routing keys
        $smsAmqpMessage = new class extends ProducerMessage {
            public string $exchange = 'hyperf';

            public string|Type $type = Type::TOPIC;

            /** @var array<string>|string */
            public array|string $routingKey = 'hyperf';
        };
        $smsAmqpMessage->setPayload([
            'event' => 'transfer_created',
            'payee_id' => $payment->payee->getId(),
            'payer_id' => $payment->payer->getId(),
            'amount' => $payment->amount->value,
        ]);
        $this->producer->produce($smsAmqpMessage);
    }
}

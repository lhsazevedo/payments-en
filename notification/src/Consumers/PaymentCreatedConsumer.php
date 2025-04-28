<?php

declare(strict_types=1);

namespace App\Consumers;

use App\Domain\NotifyAction;
use App\Domain\PaymentEvent;
use App\Domain\ValueObject\Amount;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\Amqp\Message\ConsumerMessage;
use Hyperf\Amqp\Result;

class PaymentCreatedConsumer extends ConsumerMessage
{
    protected string $exchange = 'hyperf';

    protected ?string $queue = 'hyperf';

    /**
     * @var string|array<string>
     */
    protected string|array $routingKey = 'hyperf';

    public function __construct(
        private ValidatorFactoryInterface $validatorFactory,
        private NotifyAction $notifyAction,
    ) {}

    /**
     * @param array<mixed> $data
     */
    public function consume($data): Result
    {
        try {
            $validated = $this->validate($data);

            ($this->notifyAction)(new PaymentEvent(
                payerUserId: (int) $validated['payer_user_id'],
                payeeUserId: (int) $validated['payee_user_id'],
                amount: new Amount((int) $validated['amount']),
            ));

            return Result::ACK;
        } catch (\Throwable $th) {
            echo $th->getFile() . "\n";
            echo $th->getLine() . "\n";
            echo $th->getMessage() . "\n";
            return Result::NACK;
        }
    }

    /**
     * @param array<mixed> $data
     *
     * @return array{payer_user_id: string, payee_user_id: string, amount: string}
     */
    private function validate(array $data): array
    {
        $validator = $this->validatorFactory->make(
            $data,
            [
                'payer_user_id' => 'required|integer',
                'payee_user_id' => 'required|integer',
                'amount' => 'required|integer|gt:0',
            ],
        );

        $validator->validate();

        /** @var array{payer_user_id: string, payee_user_id: string, amount: string} */
        $validated = $validator->validated();

        return $validated;
    }
}

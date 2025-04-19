<?php

declare(strict_types=1);

namespace App\Http;

use App\Domain\TransferAction;
use App\Domain\ValueObject\Amount;
use Hyperf\Coroutine\Coroutine;
use Hyperf\HttpMessage\Exception\BadRequestHttpException;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

class TransferController
{
    public function __construct(
        private TransferAction $transfer,
        private ValidatorFactoryInterface $validatorFactory,
    ) {}

    public function __invoke(
        RequestInterface $request,
        ResponseInterface $response,
    ): PsrResponseInterface {
        /** @var array{payer_id: string, payee_id: string, amount: string} */
        $validated = $this->validate($request);

        ($this->transfer)(
            (int) $validated['payer_id'],
            (int) $validated['payee_id'],
            new Amount((int) $validated['amount']),
        );

        return $response->json([
            'status' => 'success',
            'data' => [
                'message' => 'Transferred successfuly',
            ],
        ]);
    }

    /**
     * @return array<mixed>
     */
    private function validate(RequestInterface $request): array
    {
        $validator = $this->validatorFactory->make(
            $request->all(),
            [
                'payer_id' => 'required|integer',
                'payee_id' => 'required|integer',
                'amount' => 'required|integer|gt:0',
            ],
        );

        if ($validator->fails()) {
            throw new BadRequestHttpException();
        }

        return $validator->validated();
    }
}

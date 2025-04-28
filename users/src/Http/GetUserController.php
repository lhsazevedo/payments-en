<?php

declare(strict_types=1);

namespace App\Http;

use App\Domain\GetUserAction;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

class GetUserController
{
    public function __construct(
        private GetUserAction $getUserAction,
        private ValidatorFactoryInterface $validatorFactory,
    ) {}

    public function __invoke(
        ResponseInterface $response,
        int $id,
    ): PsrResponseInterface {
        $user = ($this->getUserAction)($id);

        return $response->json([
            'status' => 'success',
            'data' => [
                'id' => $user->getId(),
                'name' => $user->name,
                'email' => $user->email,
                'mobile_number' => $user->mobileNumber,
            ],
        ]);
    }
}

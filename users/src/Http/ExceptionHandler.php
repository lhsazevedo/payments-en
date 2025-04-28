<?php

declare(strict_types=1);

namespace App\Http;

use App\Domain\Exception\DomainException;
use Hyperf\ExceptionHandler\ExceptionHandler as AbstractExceptionHandler;
use Hyperf\HttpMessage\Exception\HttpException;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Throwable;

class ExceptionHandler extends AbstractExceptionHandler
{
    public function handle(Throwable $throwable, PsrResponseInterface $response): PsrResponseInterface
    {
        $code = 500;
        $message = $throwable->getMessage();

        if ($throwable instanceof DomainException) {
            $code = 400;
        } elseif ($throwable instanceof HttpException) {
            $code = $throwable->getStatusCode();
        }

        $body = json_encode([
            'status' => 'fail',
            'data' => [ 'message' => $message ],
        ]);

        assert(is_string($body));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($code)
            ->withBody(new SwooleStream($body));
    }

    /**
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     */
    public function isValid(Throwable $throwable): bool
    {
        return true;
    }
}

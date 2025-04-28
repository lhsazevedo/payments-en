<?php

declare(strict_types=1);

namespace Tests\Services;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Hyperf\Guzzle\ClientFactory;
use App\Services\ExternalPaymentAuthorizer;
use App\Domain\Payment;
use App\Domain\Account;
use App\Domain\ValueObject\TaxId;
use App\Domain\AccountType;
use App\Domain\ValueObject\Amount;
use App\Domain\Exception\UnauthorizedPaymentException;

class ExternalPaymentAuthorizerTest extends TestCase
{
    private ClientFactory&MockObject $clientFactory;
    private Client&MockObject $client;

    protected function setUp(): void
    {
        $this->clientFactory = $this->createMock(ClientFactory::class);
        $this->client = $this->createMock(Client::class);

        $this->clientFactory
            ->method('create')
            ->willReturn($this->client);
    }

    public function testAuthorizeSucceedsWhenAuthorizationTrue(): void
    {
        $payload = ['status' => 'ok', 'data' => ['authorization' => true]];
        $json = json_encode($payload);

        $stream = $this->createMock(StreamInterface::class);
        $stream->method('getContents')->willReturn($json);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getBody')->willReturn($stream);

        $this->client
            ->expects($this->once())
            ->method('get')
            ->willReturn($response);

        $authorizer = $this->createAuthorizer();
        $authorizer->authorize($this->createPayment());

        // no exception means success
        $this->addToAssertionCount(1);
    }

    public function testAuthorizeThrowsWhenAuthorizationFalse(): void
    {
        $payload = ['status' => 'ok', 'data' => ['authorization' => false]];
        $json = json_encode($payload);

        $stream = $this->createMock(StreamInterface::class);
        $stream->method('getContents')->willReturn($json);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getBody')->willReturn($stream);

        $this->client
            ->method('get')
            ->willReturn($response);

        $authorizer = $this->createAuthorizer();
        $this->expectException(UnauthorizedPaymentException::class);

        $authorizer->authorize($this->createPayment());
    }

    public function testAuthorizeThrowsOnHttpErrorStatus(): void
    {
        $stream = $this->createMock(StreamInterface::class);
        $stream->method('getContents')->willReturn('{}');

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(500);
        $response->method('getBody')->willReturn($stream);

        $this->client
            ->expects($this->once())
            ->method('get')
            ->willReturn($response);

        $authorizer = $this->createAuthorizer();
        $this->expectException(UnauthorizedPaymentException::class);

        $authorizer->authorize($this->createPayment());
    }

    public function testAuthorizeThrowsOnGuzzleException(): void
    {
        $exception = new RequestException('Connection error', new Request('GET', 'https://example.com'));

        $this->client
            ->method('get')
            ->willThrowException($exception);

        $authorizer = $this->createAuthorizer();
        $this->expectException(UnauthorizedPaymentException::class);

        $authorizer->authorize($this->createPayment());
    }

    public function testAuthorizeThrowsOnInvalidJson(): void
    {
        $stream = $this->createMock(StreamInterface::class);
        $stream->method('getContents')->willReturn('invalid json');

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getBody')->willReturn($stream);

        $this->client
            ->method('get')
            ->willReturn($response);

        $authorizer = $this->createAuthorizer();
        $this->expectException(UnauthorizedPaymentException::class);

        $authorizer->authorize($this->createPayment());
    }

    private function createAuthorizer(): ExternalPaymentAuthorizer
    {
        return new ExternalPaymentAuthorizer($this->clientFactory);
    }

    private function createPayment(): Payment
    {
        $taxId = new TaxId('12345678900');
        $payer = new Account(10, $taxId, AccountType::Regular, new Amount(100));
        $payee = new Account(20, $taxId, AccountType::Regular, new Amount(0));

        return new Payment($payer, $payee, new Amount(50));
    }
}

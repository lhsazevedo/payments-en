<?php

declare(strict_types=1);

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Hyperf\Guzzle\ClientFactory;
use App\Domain\Exception\UnauthorizedPaymentException;
use App\Domain\Payment;
use App\Domain\Services\PaymentAuthorizerContract;

class ExternalPaymentAuthorizer implements PaymentAuthorizerContract
{
    private Client $client;

    public function __construct(ClientFactory $clientFactory)
    {
        $this->client = $clientFactory->create([
            'timeout' => 5,
            'connect_timeout' => 2,
            'http_errors' => false,
        ]);
    }

    public function authorize(Payment $payment): void
    {
        $body = $this->requestAuthorization($payment);

        if ($body['data']['authorization'] === true) {
            return;
        }

        throw new UnauthorizedPaymentException(
            'Transfer was not authorized by external service',
        );
    }

    /**
     * @return array{status: string, data: array{authorization: bool}}
     */
    private function requestAuthorization(Payment $payment): array
    {
        try {
            $response = $this->client->get(
                'https://util.devi.tools/api/v2/authorize',
                ['json' => $this->buildPayload($payment)],
            );
        } catch (GuzzleException $e) {
            throw new UnauthorizedPaymentException(
                'Error requesting payment authorization',
                previous: $e,
            );
        }

        if (! in_array($response->getStatusCode(), [200, 403], strict: true)) {
            throw new UnauthorizedPaymentException(
                'Error requesting payment authorization',
            );
        }

        return $this->decodeResponse($response->getBody()->getContents());
    }

    /**
     * @return array{payer: string, amount: int}
     */
    private function buildPayload(Payment $payment): array
    {
        return [
            'payer' => $payment->payer->taxId->value,
            'amount' => $payment->amount->value,
        ];
    }

    /**
     * @return array{status: string, data: array{authorization: bool}}
     */
    private function decodeResponse(string $json): array
    {
        $data = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($data)) {
            throw new UnauthorizedPaymentException(
                'Error decoding JSON from authorizer service',
            );
        }

        // Make PHPStan happy
        assert(isset($data['status']));
        assert(is_string($data['status']));
        assert(isset($data['data']));
        assert(is_array($data['data']));
        assert(isset($data['data']['authorization']));
        assert(is_bool($data['data']['authorization']));

        return $data;
    }
}

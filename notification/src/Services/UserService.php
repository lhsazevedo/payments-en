<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\Services\UserServiceContract;
use App\Domain\User;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Hyperf\Guzzle\ClientFactory;

class UserService implements UserServiceContract
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

    public function fetchById(int $id): User
    {
        $data = $this->requestUser($id);

        return new User(
            name: $data['data']['name'],
            email: $data['data']['email'],
            mobileNumber: $data['data']['mobile_number'],
        );
    }

    /**
     * @return array{
     *   status: string,
     *   data: array{
     *     name: string,
     *     email: string,
     *     mobile_number: string,
     *   }
     * }
     */
    private function requestUser(int $id): array
    {
        $response = $this->client->get("http://users:9501/api/v1/users/$id");

        $body = $response->getBody()->getContents();
        /** @var array<mixed> */
        $data = json_decode($body, true);

        // Make PHPStan happy
        assert(isset($data['status']));
        assert(is_string($data['status']));
        assert(isset($data['data']));
        assert(is_array($data['data']));
        assert(isset($data['data']['name']));
        assert(is_string($data['data']['name']));
        assert(isset($data['data']['mobile_number']));
        assert(is_string($data['data']['mobile_number']));
        assert(isset($data['data']['email']));
        assert(is_string($data['data']['email']));

        return $data;
    }
}

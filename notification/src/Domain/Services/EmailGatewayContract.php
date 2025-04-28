<?php

declare(strict_types=1);

namespace App\Domain\Services;

interface EmailGatewayContract
{
    public function send(string $subject, string $body): void;
}

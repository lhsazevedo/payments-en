<?php

declare(strict_types=1);

namespace App\Domain\Services;

interface SmsGatewayContract
{
    public function send(string $number, string $message): void;
}

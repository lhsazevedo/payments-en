<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\Services\SmsGatewayContract;

class StdoutSmsGateway implements SmsGatewayContract
{
    public function send(string $number, string $message): void
    {
        echo "Enviando SMS para $number:\n";
        echo "\"$message\"\n\n";
    }
}

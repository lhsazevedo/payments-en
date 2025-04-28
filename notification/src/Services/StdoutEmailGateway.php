<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\Services\EmailGatewayContract;

class StdoutEmailGateway implements EmailGatewayContract
{
    public function send(string $subject, string $body): void
    {
        echo "Enviando email para $subject:\n";
        echo "\"$body\"\n\n";
    }
}

<?php

declare(strict_types=1);

use App\Domain\Services\EmailGatewayContract;
use App\Domain\Services\SmsGatewayContract;
use App\Domain\Services\UserServiceContract;
use App\Services\StdoutEmailGateway;
use App\Services\StdoutSmsGateway;
use App\Services\UserService;

return [
    UserServiceContract::class => UserService::class,
    SmsGatewayContract::class => StdoutSmsGateway::class,
    EmailGatewayContract::class => StdoutEmailGateway::class,
];

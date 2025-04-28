<?php

declare(strict_types=1);

use App\Domain\Services\EventPublisherContract;
use App\Domain\Services\Notification\NotificationServiceContract;
use App\Domain\Services\PaymentAuthorizerContract;
use App\Domain\Services\PaymentRepositoryContract;
use App\Domain\Services\TransactionManagerContract;
use App\Domain\Services\UserRepositoryContract;
use App\Respository\DbPaymentRepository;
use App\Respository\DbTransactionManager;
use App\Respository\DbUserRepository;
use App\Services\ExternalPaymentAuthorizer;
use App\Services\RabbitMqEventPublisher;

return [
    EventPublisherContract::class => RabbitMqEventPublisher::class,
    PaymentAuthorizerContract::class => ExternalPaymentAuthorizer::class,
    PaymentRepositoryContract::class => DbPaymentRepository::class,
    UserRepositoryContract::class => DbUserRepository::class,
    TransactionManagerContract::class => DbTransactionManager::class,
];

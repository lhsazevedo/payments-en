<?php

use App\Consumers\PaymentCreatedConsumer;
use Hyperf\Nano\Factory\AppFactory;
use Hyperf\Amqp;

require_once __DIR__ . '/vendor/autoload.php';

$app = AppFactory::createBase(
    dependencies: require_once __DIR__ . '/config/dependencies.php',
);
$container = $app->getContainer();

$app->config(require_once __DIR__ . '/config.php');

$app->addProcess(function () {
    $paymentCreatedConsumer = $this->container->get(PaymentCreatedConsumer::class);
    $consumer = $this->container->get(Amqp\Consumer::class);
    $consumer->consume($paymentCreatedConsumer);
});

$app->run();

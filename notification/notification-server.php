<?php

use Hyperf\Nano\Factory\AppFactory;
use Hyperf\Amqp;
use Hyperf\Amqp\Result;
use Hyperf\Nano\Constant;

require_once __DIR__ . '/vendor/autoload.php';

$app = AppFactory::createBase();
$container = $app->getContainer();

$app->config(require_once __DIR__ . '/config.php');

$app->addProcess(function () {
    $message = new class extends Amqp\Message\ConsumerMessage {
        protected string $exchange = 'hyperf';

        protected ?string $queue = 'hyperf';

        protected string|array $routingKey = 'hyperf';

        public function consumeMessage($data, \PhpAmqpLib\Message\AMQPMessage $message): Result
        {
            var_dump($data);
            return Amqp\Result::ACK;
        }
    };
    $consumer = $this->container->get(Amqp\Consumer::class);
    $consumer->consume($message);
});

$app->run();

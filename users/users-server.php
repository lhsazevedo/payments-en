<?php

declare(strict_types=1);

use Hyperf\Nano\Factory\AppFactory;
use App\Http\ExceptionHandler;
use App\Http\GetUserController;
use Hyperf\Nano\Constant;

date_default_timezone_set('UTC');

require_once __DIR__ . '/vendor/autoload.php';

$app = AppFactory::createBase(
    dependencies: require_once __DIR__ . '/config/dependencies.php',
);

// Remove default exception handler
$app->config(['exceptions' => ['handler' => []]], Constant::CONFIG_REPLACE);
$app->config(require_once __DIR__ . '/config/databases.php');

$app->addExceptionHandler(ExceptionHandler::class);

$app->get('/api/v1/users/{id}', GetUserController::class);

$app->run();

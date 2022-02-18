<?php

use app\Controller\HelloController;
use app\Middleware\Middleware;

return [
    ['GET', '/hello/index', [HelloController::class, 'index'], ['middlewares' => [Middleware::class]]],
    ['GET', '/hello/hyperf', [HelloController::class, 'hyperf']],
];
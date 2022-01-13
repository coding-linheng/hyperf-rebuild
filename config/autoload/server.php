<?php

declare(strict_types=1);

/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

use Swoole\Constant;

return [
    'mode'      => SWOOLE_PROCESS,
    'servers'   => [
        [
            'name'      => 'http',
            'type'      => 1,
            'host'      => '0.0.0.0',
            'port'      => 9501,
            'sock_type' => SWOOLE_SOCK_TCP,
            'callbacks' => [
                'request' => [rebuild\HttpServer\Server::class, 'onRequest'],
            ],
        ],
    ],
    'settings'  => [
        'enable_coroutine' => true,
        'worker_num' => 1

    ],
    'callbacks' => [
        'worker_start' => [Hyperf\Framework\Bootstrap\WorkerStartCallback::class, 'onWorkerStart'],
    ],
];

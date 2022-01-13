<?php

namespace rebuild\Server;

use rebuild\Contract\ServerInterface;
use Swoole\Coroutine\Server as SwooleCoServer;
use Swoole\Server as SwooleServer;

class Server implements ServerInterface
{

    protected SwooleServer $server;


    public function __construct()
    {
    }

    public function init(array $config): ServerInterface
    {
        foreach($config['servers'] as $server) {
            $this->server = new \Swoole\Http\Server($server['host'], $server['port'], $server['type'], $server['sock_type']);
            $this->registerSwooleEvents($server['callbacks']);
            break;
        }
        return $this;
    }

    public function start()
    {
        $this->getServer()->start();
    }

    public function getServer(): SwooleServer
    {
        return $this->server;
    }

    protected function registerSwooleEvents(array $callbacks)
    {
        foreach($callbacks as $swooleEvent => $callback) {
            [$class, $method] = $callback;
            $instance = new $class;
            $this->server->on($swooleEvent, [$instance, $method]);
        }
    }
}
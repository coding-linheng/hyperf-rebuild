<?php

namespace rebuild\Server;

use rebuild\Contract\ServerInterface;
use rebuild\HttpServer\Router\DispatcherFactory;
use Swoole\Server as SwooleServer;

class Server implements ServerInterface
{

    protected SwooleServer $server;


    public function __construct()
    {
    }

    /**
     * 根据配置文件初始化服务配置
     * @param array $config
     * @return ServerInterface
     */
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

    /**
     * 注册swoole事件
     * @param array $callbacks
     * @return void
     */
    protected function registerSwooleEvents(array $callbacks)
    {
        foreach($callbacks as $swooleEvent => $callback) {
            [$class, $method] = $callback;
            //注册路由
            if($class === \rebuild\HttpServer\Server::class) {
                $instance = new $class(new DispatcherFactory);
            } else {
                $instance = new $class;
            }
            $this->server->on($swooleEvent, [$instance, $method]);
            //初始化核心中间件
            if(method_exists($instance,'initCoreMiddleware')){
                /** @var \rebuild\HttpServer\Server $instance */
                $instance->initCoreMiddleware();
            }
        }
    }
}
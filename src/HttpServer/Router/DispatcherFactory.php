<?php

namespace rebuild\HttpServer\Router;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use rebuild\HttpServer\MiddlewareManager;
use function FastRoute\simpleDispatcher;

/**
 * 路由调度工厂
 */
class DispatcherFactory
{
    protected array $routeFiles = [BASE_PATH . '/config/routes.php'];

    protected array $dispatchers = [];

    protected array $routes = [];

    public function __construct()
    {
        $this->initConfigRoute();
    }

    /**
     * 获取路由调度
     * @param string $serverName
     * @return Dispatcher
     */
    public function getDispatcher(string $serverName): Dispatcher
    {
        if(!isset($this->dispatchers[$serverName])) {
            $this->dispatchers[$serverName] = simpleDispatcher(function(RouteCollector $r) {
                foreach($this->routes as $route) {
                    //注册路由配置
                    [$httpMethod, $path, $handler] = $route;
                    if(isset($route[3])) {
                        $options = $route[3];
                    }
                    //增加路由中间件
                    $r->addRoute($httpMethod, $path, $handler);
                    if(isset($options['middlewares']) && is_array($options['middlewares'])) {
                        MiddlewareManager::addMiddlewares($path, $httpMethod, $options['middlewares']);
                    }
                }
            });
        }
        return $this->dispatchers[$serverName];
    }

    /**
     * 初始化路由配置
     * @return void
     */
    public function initConfigRoute()
    {
        foreach($this->routeFiles as $file) {
            if(file_exists($file)) {
                $this->routes = array_merge_recursive($this->routes, require_once $file);
            }
        }
    }
}
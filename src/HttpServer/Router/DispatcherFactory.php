<?php

namespace rebuild\HttpServer\Router;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
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

    public function getDispatcher(string $serverName): Dispatcher
    {
        if(!isset($this->dispatchers[$serverName])) {
            $this->dispatchers[$serverName] = simpleDispatcher(function(RouteCollector $r) {
                foreach($this->routes as $route) {
                    [$httpMethod, $path, $handler] = $route;
                    $r->addRoute($httpMethod, $path, $handler);
                }
            });
        }
        return $this->dispatchers[$serverName];
    }

    public function initConfigRoute()
    {
        foreach($this->routeFiles as $file) {
            if(file_exists($file)) {
                $this->routes = array_merge_recursive($this->routes, require_once $file);
            }
        }
    }
}
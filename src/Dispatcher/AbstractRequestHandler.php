<?php

namespace rebuild\Dispatcher;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * 抽象请求处理
 */
abstract class AbstractRequestHandler implements RequestHandlerInterface
{
    protected MiddlewareInterface $coreHandler;

    protected array $middlewares = [];

    protected int $offset = 0;

    /**
     * @param MiddlewareInterface $coreHandler
     * @param array $middlewares
     */
    public function __construct(MiddlewareInterface $coreHandler, array $middlewares)
    {
        $this->coreHandler = $coreHandler;
        $this->middlewares = $middlewares;
    }


    /**
     * 处理请求
     * @param $request
     * @return ResponseInterface
     */
    protected function handlerRequest($request): ResponseInterface
    {
        //执行中间件
        if(!isset($this->middlewares[$this->offset]) && !empty($this->coreHandler)) {
            //没有可执行中间件 且核心中间件不为空
            $handler = $this->coreHandler;
        } else {
            $handler = $this->middlewares[$this->offset];
            is_string($handler) && $handler = new $handler();
        }

        //中间件必须实现psr15
        if(!method_exists($handler, 'process')) {
            throw new \InvalidArgumentException('Invalid middleware, it has to provide a process() method');
        }
        return $handler->process($request, $this->next());
    }

    protected function next(): static
    {
        ++$this->offset;
        return $this;
    }
}
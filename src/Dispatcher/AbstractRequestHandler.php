<?php

namespace rebuild\Dispatcher;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

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


    protected function handlerRequest($request): ResponseInterface
    {
        if(!isset($this->middlewares[$this->offset]) && !empty($this->coreHandler)) {
            $handler = $this->coreHandler;
        } else {
            $handler = $this->middlewares[$this->offset];
            is_string($handler) && $handler = new $handler();
        }

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
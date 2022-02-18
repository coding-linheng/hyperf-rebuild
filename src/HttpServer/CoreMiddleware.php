<?php

namespace rebuild\HttpServer;


use FastRoute\Dispatcher;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Utils\Codec\Json;
use Hyperf\Utils\Context;
use Hyperf\Utils\Contracts\Arrayable;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use rebuild\HttpServer\Contract\CoreMiddlewareInterface;
use rebuild\HttpServer\Router\Dispatched;
use rebuild\HttpServer\Router\DispatcherFactory;

class CoreMiddleware implements CoreMiddlewareInterface
{
    protected Dispatcher $dispatcher;

    public function __construct(DispatcherFactory $dispatcherFactory)
    {
        $this->dispatcher = $dispatcherFactory->getDispatcher('http');
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        //获取转换的路由调度器
        $dispatched = $request->getAttribute(Dispatched::class);
        if(!$dispatched instanceof Dispatched) {
            throw new \InvalidArgumentException('Route not found');
        }
        $response = match ($dispatched->status) {
            Dispatcher::NOT_FOUND => $this->handleNotFound($request),
            Dispatcher::METHOD_NOT_ALLOWED => $this->handleMethodNotAllow($request),
            Dispatcher::FOUND => $this->handleFound($request, $dispatched)
        };

        if(!$response instanceof ResponseInterface) {
            $response = $this->transferToResponse($response);
        }
        return $response;
    }

    public function dispatch(ServerRequestInterface $request): ServerRequestInterface
    {
        $method = $request->getMethod();
        $path   = $request->getUri()->getPath();

        $routeInfo  = $this->dispatcher->dispatch($method, $path);
        $dispatched = new Dispatched($routeInfo);

        return Context::set(ServerRequestInterface::class, $request->withAttribute(Dispatched::class, $dispatched));
    }

    private function handleNotFound(ServerRequestInterface $request)
    {
        /** @var ResponseInterface $response */
        return $response->withStatus(404)->withBody(new SwooleStream('Not Found'));
    }

    private function handleMethodNotAllow(ServerRequestInterface $request)
    {
        /** @var ResponseInterface $response */
        return $response->withStatus(405)->withBody(new SwooleStream('Method not allow'));
    }

    private function handleFound(ServerRequestInterface $request, Dispatched $dispatched)
    {
        [$controller, $action] = $dispatched->handler;
        if(!class_exists($controller)) {
            throw new \InvalidArgumentException('Controller not exists');
        }
        if(!method_exists($controller, $action)) {
            throw new \InvalidArgumentException('Action of Controller not exists');
        }
        $params             = $dispatched->params;
        $controllerInstance = new $controller;
        return $controllerInstance->{$action}(...$params);
    }

    protected function transferToResponse(mixed $response): ResponseInterface
    {
        if(is_string($response)) {
            return $this->response()
                ->withAddedHeader('Content-Type', 'text/plain')
                ->withBody(new SwooleStream($response));
        } elseif(is_array($response) || $response instanceof Arrayable) {
            return $this->response()
                ->withAddedHeader('Content-Type', 'application/json')
                ->withBody(new SwooleStream(Json::encode($response)));
        }
        return $response;
    }

    protected function response(): ResponseInterface
    {
        return Context::get(ResponseInterface::class);
    }


}
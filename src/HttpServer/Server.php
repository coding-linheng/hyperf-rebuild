<?php

namespace rebuild\HttpServer;

use FastRoute\Dispatcher;
use Hyperf\HttpMessage\Server\Request as Psr7Request;
use Hyperf\HttpMessage\Server\Response as Psr7Response;
use Hyperf\Utils\Context;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use rebuild\Config\ConfigFactory;
use rebuild\Dispatcher\HttpRequestHandler;
use rebuild\HttpServer\Contract\CoreMiddlewareInterface;
use rebuild\HttpServer\Router\Dispatched;
use rebuild\HttpServer\Router\DispatcherFactory;
use Swoole\Http\Request;
use Swoole\Http\Response;

class Server
{

    protected Dispatcher $dispatcher;

    protected CoreMiddlewareInterface $coreMiddleware;
    protected array $globalMiddlewares;

    protected DispatcherFactory $dispatcherFactory;

    public function __construct(DispatcherFactory $dispatcherFactory)
    {
        $this->dispatcherFactory = $dispatcherFactory;
        $this->dispatcher        = $this->dispatcherFactory->getDispatcher('http');
    }

    public function initCoreMiddleware()
    {
        $this->coreMiddleware    = new CoreMiddleware($this->dispatcherFactory);
        $this->globalMiddlewares = (new ConfigFactory)()->get('middlewares');
    }

    public function onRequest(Request $request, Response $response)
    {
        /** @var ServerRequestInterface $psr7Request */
        /** @var ResponseInterface $psr7Response */
        [$psr7Request, $psr7Response] = $this->initRequestAndResponse($request, $response);

        $psr7Request = $this->coreMiddleware->dispatch($psr7Request);

        $method = $psr7Request->getMethod();
        $path   = $psr7Request->getUri()->getPath();

        $middlewares = $this->globalMiddlewares;
        $dispatched  = $psr7Request->getAttribute(Dispatched::class);
        if($dispatched instanceof Dispatched && $dispatched->isFound()) {
            $registeredMiddlewares = MiddlewareManager::get($path, $method);
            $middlewares           = array_merge($middlewares, $registeredMiddlewares);
        }
        $requestHandler = new HttpRequestHandler($this->coreMiddleware, $middlewares);
        $psr7Response   = $requestHandler->handle($psr7Request);

        $response->end($psr7Response);
    }

    protected function initRequestAndResponse(Request $request, Response $response): array
    {
        Context::set(ResponseInterface::class, $psr7Response = new Psr7Response);
        Context::set(ServerRequestInterface::class, $psr7Request = Psr7Request::loadFromSwooleRequest($request));
        return [$psr7Request, $psr7Response];
    }
}